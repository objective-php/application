<?php

namespace ObjectivePHP\Application;

use Composer\Autoload\ClassLoader;
use ObjectivePHP\Application\Config\ApplicationName;
use ObjectivePHP\Application\Injector\DefaultInjector;
use ObjectivePHP\Application\Middleware\MiddlewareRegistry;
use ObjectivePHP\Application\Workflow\WorkflowEvent;
use ObjectivePHP\Config\DirectivesProviderInterface;
use ObjectivePHP\Filter\FiltersProviderInterface;
use ObjectivePHP\Router\Router\MetaRouter;
use ObjectivePHP\Router\Router\RouterInterface;
use ObjectivePHP\Router\RoutingResult;
use ObjectivePHP\ServicesFactory\Specification\PrefabServiceSpecification;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Stream;
use ObjectivePHP\Application\Exception\WorkflowException;

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
     * @throws \ObjectivePHP\Events\Exception\EventException
     * @throws \ObjectivePHP\Primitives\Exception
     * @throws \ObjectivePHP\ServicesFactory\Exception\ServiceNotFoundException
     * @throws \ObjectivePHP\ServicesFactory\Exception\ServicesFactoryException
     */
    public function __construct(AbstractEngine $engine)
    {
        $buffer = $this->cleanBuffer();

        ob_start();
        if ($buffer) {
            echo $buffer;
        }

        $this->setEngine($engine);

        $this->getServicesFactory()
            ->registerService(new PrefabServiceSpecification('application', $this));

        $this->middlewares = new MiddlewareRegistry();

        $this->exceptionHandlers = (new MiddlewareRegistry());

        $this->router = (new MetaRouter());

        // register default injector
        $this->getServicesFactory()->registerInjector(new DefaultInjector());

        // init http request
        $request = ServerRequestFactory::fromGlobals(
            $_SERVER,
            $_GET,
            $_POST,
            $_COOKIE,
            $_FILES
        );

        $this->setRequest($request);

        // let ServicesFactory and EventsHandler know each other
        $this->getEventsHandler()->setServicesFactory($this->getServicesFactory());

        // initialize application by plugging middlewares
        $this->init();

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
            $this->getEngine()->triggerWorkflowEvent(WorkflowEvent::ROUTING_START, $this);

            $this->setRoutingResult($this->getRouter()->route($this->getRequest(), $this));

            if ($this->getRoutingResult()->didMatch()) {
                $this->getMiddlewares()->registerMiddleware($this->getRoutingResult()->getMatchedRoute()->getAction());
            }

            $this->getEngine()->triggerWorkflowEvent(WorkflowEvent::ROUTING_DONE, $this);

            $this->getEngine()->triggerWorkflowEvent(WorkflowEvent::REQUEST_HANDLING_START, $this);

            $response = $this->handle($this->getRequest());

            $this->getEngine()->triggerWorkflowEvent(WorkflowEvent::REQUEST_HANDLING_DONE, $this, ['response' => $response]);
            $this->getEngine()->triggerWorkflowEvent(WorkflowEvent::RESPONSE_READY, $this, ['response' => $response]);

            if ($buffer = $this->cleanBuffer()) {
                $response->getBody()->rewind();
                $content = $buffer . $response->getBody()->getContents();
                $response = $response->withBody(new Stream('php://memory', 'wb+'));
                $response->getBody()->write($content);
            }

            $emitter->emit($response);

            ob_start();
            $this->getEngine()->triggerWorkflowEvent(WorkflowEvent::RESPONSE_SENT);

        } catch (\Throwable $exception) {
            $request = $this->getRequest()
                ->withAttribute('exception', $exception)
                ->withAttribute('buffer', $this->cleanBuffer())
                ->withAttribute('headers', headers_list());

            $response = $this->handleException($request);

            $this->getEngine()->triggerWorkflowEvent(WorkflowEvent::RESPONSE_READY, $this, ['response' => $response]);

            if ($response->getStatusCode() == 200) {
                $response = $response->withStatus(500);
            }

            $emitter->emit($response);

            $this->getEngine()->triggerWorkflowEvent(WorkflowEvent::RESPONSE_SENT);
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

        $this->getEngine()->triggerWorkflowEvent(WorkflowEvent::MIDDLEWARE_START, $middleware);

        $response = $this->getServicesFactory()->autorun([$middleware, 'process'], [$request, $this]);

        $this->getEngine()->triggerWorkflowEvent(WorkflowEvent::MIDDLEWARE_DONE, $middleware);

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

        $response = $this->getServicesFactory()->autorun([$middleware, 'process'], [$request, $this]);

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
    public function getDirectives(): array
    {
        return [
            // application config
            new ApplicationName()
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
