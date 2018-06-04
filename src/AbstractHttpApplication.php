<?php

namespace ObjectivePHP\Application;

use Composer\Autoload\ClassLoader;
use ObjectivePHP\Application\Config\ApplicationName;
use ObjectivePHP\Application\Exception\WorkflowException;
use ObjectivePHP\Application\Injector\DefaultInjector;
use ObjectivePHP\Application\Middleware\MiddlewareRegistry;
use ObjectivePHP\Application\Package\PackageInterface;
use ObjectivePHP\Application\Workflow\PackagesInitListener;
use ObjectivePHP\Application\Workflow\PackagesReadyListener;
use ObjectivePHP\Application\Workflow\WorkflowEvent;
use ObjectivePHP\Config\ConfigProviderInterface;
use ObjectivePHP\Config\Loader\FileLoader\FileLoader;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\Filter\FiltersProviderInterface;
use ObjectivePHP\Primitives\Collection\Collection;
use ObjectivePHP\Router\Config\ActionNamespace;
use ObjectivePHP\Router\Config\UrlAlias;
use ObjectivePHP\Router\MetaRouter;
use ObjectivePHP\Router\PathMapperRouter;
use ObjectivePHP\Router\RouterInterface;
use ObjectivePHP\Router\RoutingResult;
use ObjectivePHP\ServicesFactory\Config\ServiceDefinition;
use ObjectivePHP\ServicesFactory\ServicesFactory;
use ObjectivePHP\ServicesFactory\Specification\PrefabServiceSpecification;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream;

/**
 * Class AbstractApplication
 *
 * @package ObjectivePHP\Application
 */
abstract class AbstractHttpApplication extends AbstractApplication implements HttpApplicationInterface
{
    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var MiddlewareRegistry
     */
    protected $middlewares;

    /**
     * @var MiddlewareRegistry
     */
    protected $exceptionHandlers;

    /**
     * @var RoutingResult
     */
    protected $routingResult;

    /**
     * AbstractApplication constructor.
     *
     * @param ClassLoader|null $autoloader
     *
     * @throws \ObjectivePHP\Primitives\Exception
     * @throws \ObjectivePHP\ServicesFactory\Exception\ServicesFactoryException
     * @throws \ObjectivePHP\Config\Exception\ConfigException
     */
    public function __construct(ClassLoader $autoloader = null)
    {
        $this->middlewares = new MiddlewareRegistry();

        $this->exceptionHandlers = (new MiddlewareRegistry());

        $this->router = (new MetaRouter())->registerRouter(new PathMapperRouter());

        // init http request
        $this->setRequest(ServerRequestFactory::fromGlobals());

        parent::__construct($autoloader);
    }

    /**
     * @return bool
     */
    public function hasRequest(): bool
    {
        return (bool)$this->request;
    }

    /**
     * {@inheritdoc}
     */
    public function run()
    {
        $emitter = new Response\SapiEmitter();

        try {
            $packages = $this->getPackages();

            /** @var PackageInterface $package */
            foreach ($packages as $package) {
                if ($package instanceof FiltersProviderInterface) {
                    if (!$package->getFilterEngine()->filter($this)) {
                        continue;
                    }
                }

                if ($package instanceof ConfigProviderInterface) {
                    $this->getConfig()->merge($package->getConfig());
                }

                if ($package instanceof PackagesInitListener) {
                    $this->getEventsHandler()->bind(WorkflowEvent::PACKAGES_INIT, [$package, 'onPackagesInit']);
                }

                if ($package instanceof PackagesReadyListener) {
                    $this->getEventsHandler()->bind(WorkflowEvent::PACKAGES_READY, [$package, 'onPackagesReady']);
                }
            }

            // read configuration
            if (is_dir('app/config')) {
                $this->getConfig()->hydrate((new FileLoader())->load('app/config'));
            }

            $this->triggerWorkflowEvent(WorkflowEvent::PACKAGES_INIT);

            // load services
            /** @var ServiceDefinition[] $servicesDefinitions */
            $servicesDefinitions = $this->getConfig()->getRaw(ServiceDefinition::KEY);

            foreach ($servicesDefinitions as $id => $servicesDefinition) {
                $service = array_merge(['id' => $id], $servicesDefinition->getSpecifications());
                $this->getServicesFactory()->registerRawService($service);
            }

            $this->triggerWorkflowEvent(WorkflowEvent::PACKAGES_READY);

            $this->triggerWorkflowEvent(WorkflowEvent::ROUTING_START);

            $this->setRoutingResult($this->getRouter()->route($this->getRequest(), $this));

            if ($this->getRoutingResult()->didMatch()) {
                $this->getMiddlewares()->registerMiddleware($this->getRoutingResult()->getMatchedRoute()->getAction());
            }

            $this->triggerWorkflowEvent(WorkflowEvent::ROUTING_DONE);

            $this->triggerWorkflowEvent(WorkflowEvent::REQUEST_HANDLING_START, $this);

            $response = $this->handle($this->getRequest());

            $this->triggerWorkflowEvent(WorkflowEvent::REQUEST_HANDLING_DONE, $this, ['response' => $response]);

            if ($buffer = $this->cleanBuffer()) {
                $response->getBody()->rewind();
                $content = $buffer . $response->getBody()->getContents();
                $response = $response->withBody(new Stream('php://memory', 'wb+'));
                $response->getBody()->write($content);
            }

            $emitter->emit($response);

            ob_start();

            $this->triggerWorkflowEvent(WorkflowEvent::RESPONSE_SENT);
        } catch (\Throwable $exception) {
            $request = $this->getRequest()
                ->withAttribute('exception', $exception)
                ->withAttribute('buffer', $this->cleanBuffer())
                ->withAttribute('headers', headers_list());

            $response = $this->handleException($request);

            $emitter->emit($response);
        }
    }


    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws WorkflowException
     * @throws \ObjectivePHP\Events\Exception\EventException
     * @throws \ObjectivePHP\Primitives\Exception
     * @throws \ObjectivePHP\ServicesFactory\Exception\ServiceNotFoundException
     * @throws \ObjectivePHP\ServicesFactory\Exception\ServicesFactoryException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = $this->getNextMiddleware();

        if (!$middleware) {
            if ($this->getRoutingResult()->didMatch()) {
                throw new WorkflowException('No suitable middleware was found to handle the request.', 404);
            }

            throw new WorkflowException('No route matched requested URL', 404);
        }

        $this->getServicesFactory()->injectDependencies($middleware);

        $this->triggerWorkflowEvent('application.workflow.middleware.before', $middleware);

        $response = $middleware->process($request, $this);

        $this->triggerWorkflowEvent('application.workflow.middleware.after', $middleware);

        return $response;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * @throws WorkflowException
     * @throws \ObjectivePHP\ServicesFactory\Exception\ServicesFactoryException
     */
    public function handleException(ServerRequestInterface $request): ResponseInterface
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = $this->getExceptionHandlers()->getNextMiddleware();
        if (!$middleware) {
            throw new WorkflowException(
                'No suitable middleware was found to handle the uncaught exception.',
                null,
                $request->getAttribute('exception')
            );
        }

        $this->getServicesFactory()->injectDependencies($middleware);

        $response = $middleware->process($request, $this);

        return $response;
    }

    /**
     * @return MiddlewareInterface|null
     */
    protected function getNextMiddleware()
    {
        while ($middleware = $this->getMiddlewares()->current()) {
            $this->getMiddlewares()->next();
            // filter step

            if (($middleware instanceof FiltersProviderInterface) && !$middleware->getFilterEngine()->run($this)) {
                continue;
            }

            return $middleware;
        }
    }

    /**
     * @return ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return $this
     */
    public function setRequest(ServerRequestInterface $request): ApplicationInterface
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return MiddlewareRegistry
     */
    public function getMiddlewares(): MiddlewareRegistry
    {
        return $this->middlewares;
    }


    /**
     * @param MiddlewareInterface $middleware
     * @param array ...$filters
     */
    public function plug(MiddlewareInterface $middleware, ...$filters)
    {
        if ($middleware instanceof FiltersProviderInterface) {
            $middleware->getFilterEngine()->registerFilter(...$filters);
        }

        $this->middlewares->append($middleware);
    }


    /**
     * @return MiddlewareRegistry
     */
    public function getExceptionHandlers(): MiddlewareRegistry
    {
        return $this->exceptionHandlers;
    }

    /**
     * @param MiddlewareRegistry $exceptionHandlers
     */
    public function setExceptionHandlers(MiddlewareRegistry $exceptionHandlers)
    {
        $this->exceptionHandlers = $exceptionHandlers;
    }

    /**
     * Defines default application config directives
     */
    protected function getConfigDirectives(): array
    {
        return [
            // application config
            new ApplicationName(),
            // meta router config
            new UrlAlias(),
            new ActionNamespace(),
            new ServiceDefinition()
        ];
    }

    /**
     * @return array
     */
    protected function getConfigParams(): array
    {
        return [
            'application.name' => 'ObjectivePHP Starter Kit',
            'router.url-alias' => ['/' => 'Home'],
            'router.action-namespace' => ['default' => $this->getProjectNamespace() . '\\Action']
        ];
    }


    /**
     * @return RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        return $this->router;
    }

    /**
     * @param RouterInterface $router
     */
    public function setRouter(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * Get RoutingResult
     *
     * @return RoutingResult|null
     */
    public function getRoutingResult()
    {
        return $this->routingResult;
    }

    /**
     * Set RoutingResult
     *
     * @param RoutingResult $routingResult
     *
     * @return $this
     */
    public function setRoutingResult(RoutingResult $routingResult)
    {
        $this->routingResult = $routingResult;

        return $this;
    }
}
