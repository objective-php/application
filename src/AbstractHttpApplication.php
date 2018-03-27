<?php

namespace ObjectivePHP\Application;

use Composer\Autoload\ClassLoader;
use ObjectivePHP\Application\Config\ApplicationName;
use ObjectivePHP\Application\Exception\WorkflowException;
use ObjectivePHP\Application\Middleware\MiddlewareRegistry;
use ObjectivePHP\Application\Package\PackageInterface;
use ObjectivePHP\Application\Workflow\PackagesInitListener;
use ObjectivePHP\Application\Workflow\PackagesReadyListener;
use ObjectivePHP\Application\Workflow\WorkflowEvent;
use ObjectivePHP\Config\Config;
use ObjectivePHP\Config\ConfigInterface;
use ObjectivePHP\Config\ConfigProviderInterface;
use ObjectivePHP\Config\Loader\FileLoader;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\Filter\FiltersProviderInterface;
use ObjectivePHP\Primitives\Collection\Collection;
use ObjectivePHP\Router\Config\ActionNamespace;
use ObjectivePHP\Router\Config\UrlAlias;
use ObjectivePHP\Router\MetaRouter;
use ObjectivePHP\Router\PathMapperRouter;
use ObjectivePHP\Router\RouterInterface;
use ObjectivePHP\ServicesFactory\ServicesFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Class AbstractApplication
 *
 * @package ObjectivePHP\Application
 */
abstract class AbstractHttpApplication implements ApplicationInterface
{
    /**
     * @var EventsHandler
     */
    protected $eventsHandler;

    /**
     * @var ServicesFactory
     */
    protected $servicesFactory;

    /**
     * @var ClassLoader
     */
    protected $autoloader;

    /**
     * @var string
     */
    protected $env;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var  MiddlewareRegistry
     */
    protected $middlewares;

    /**
     * @var  MiddlewareRegistry
     */
    protected $exceptionHandlers;

    /**
     * @var Collection
     */
    protected $packages;

    /**
     * @var RouterInterface
     */
    protected $router;

    /**
     * AbstractApplication constructor.
     *
     * @param ClassLoader|null $autoloader
     */
    public function __construct(ClassLoader $autoloader = null)
    {
        if ($autoloader) {
            $this->setAutoloader($autoloader);
        }

        $this->eventsHandler = new EventsHandler();

        $this->triggerWorkflowEvent(WorkflowEvent::BOOTSTRAP_INIT);

        $this->servicesFactory = new ServicesFactory();
        $this->middlewares = new MiddlewareRegistry();
        $this->exceptionHandlers = (new MiddlewareRegistry())->setDefaultInsertionPosition(MiddlewareRegistry::BEFORE_LAST);
        $this->packages = (new Collection())->restrictTo(PackageInterface::class);
        $this->router = (new MetaRouter())->registerRouter(new PathMapperRouter());

        // register default configuration directives
        $this->getConfig()->registerDirective(...$this->getConfigDirectives());

        // load default configuration parameters
        $this->getConfig()->hydrate($this->getConfigParams());


        // register application in services factory
        $this->getServicesFactory()->registerService(['id' => 'application', 'instance' => $this]);

        // set default Exception Handler
        //$this->getExceptionHandlers()->register()

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

        $this->triggerWorkflowEvent(WorkflowEvent::BOOTSTRAP_DONE);


    }

    protected function triggerWorkflowEvent($eventName, $origin = null, $context = [])
    {
        $this->getEventsHandler()->trigger($eventName, $origin, $context, new WorkflowEvent($this));
    }

    public function registerPackage(PackageInterface $package, ...$filters)
    {
        if ($package instanceof FiltersProviderInterface && $filters) {
            $package->getFilterEngine()->registerFilter(...$filters);
        }

        $this->packages->append($package);
    }

    /**
     * @return EventsHandler
     */
    public function getEventsHandler(): EventsHandler
    {
        return $this->eventsHandler;
    }

    /**
     * @param EventsHandler $eventsHandler
     *
     * @return $this
     */
    public function setEventsHandler(EventsHandler $eventsHandler): ApplicationInterface
    {
        $this->eventsHandler = $eventsHandler;

        return $this;
    }

    /**
     * @return ServicesFactory
     */
    public function getServicesFactory(): ServicesFactory
    {
        return $this->servicesFactory;
    }

    /**
     * @param ServicesFactory $servicesFactory
     *
     * @return $this
     */
    public function setServicesFactory(ServicesFactory $servicesFactory)
    {
        $this->servicesFactory = $servicesFactory;

        return $this;
    }

    /**
     * @return string
     */
    public function getEnv(): string
    {
        return $this->env;
    }

    /**
     * @param string $env
     *
     * @return $this
     */
    public function setEnv($env): ApplicationInterface
    {
        $this->env = $env;

        return $this;
    }

    /**
     * @return bool
     */
    public function hasRequest(): bool
    {
        return (bool)$this->request;
    }

    public function run()
    {

        $packages = $this->getPackages();
        /** @var PackageInterface $package */
        foreach ($packages as $package) {

            if ($package instanceof FiltersProviderInterface) {
                if (!$package->getFilterEngine()->filter($this)) continue;
            }

            if ($package instanceof ConfigProviderInterface) {
                $this->getConfig()->merge($package->getConfig());
            }

            if ($package instanceof PackagesInitListener) {
                $this->getEventsHandler()->bind(WorkflowEvent::PACKAGES_INIT, [$package, 'onPackagesInit']);
            }

            if ($package instanceof PackagesReadyListener) {
                $this->getEventsHandler()->bind(WorkflowEvent::PACKAGES_INIT, [$package, 'onPackagesReady']);
            }
        }

        // read configuration
        if (is_dir('app/config')) {
            $this->getConfig()->hydrate((new FileLoader())->load('app/config'));
        }

        $this->triggerWorkflowEvent(WorkflowEvent::PACKAGES_INIT);

        $this->triggerWorkflowEvent(WorkflowEvent::PACKAGES_READY);

        $this->triggerWorkflowEvent(WorkflowEvent::ROUTING_START);

        $routingResult = $this->router->route($this);
        $action = $routingResult->getMatchedRoute()->getAction();

        $this->getMiddlewares()->register($action);

        $this->triggerWorkflowEvent(WorkflowEvent::ROUTING_DONE);


        $emitter = new Response\SapiEmitter();

        try {
            $this->triggerWorkflowEvent(WorkflowEvent::REQUEST_HANDLING_START, $this);
            $response = $this->handle($this->getRequest());
            $this->triggerWorkflowEvent(WorkflowEvent::REQUEST_HANDLING_DONE, $this, ['response' => $response]);
            $emitter->emit($response);
            $this->triggerWorkflowEvent(WorkflowEvent::RESPONSE_SENT);
        } catch (\Throwable $exception) {

            $request = $this->getRequest()->withAttribute('exception', $exception);

            $response = $this->handleException($request);

            $emitter->emit($response);

        }
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {

        /** @var MiddlewareInterface $middleware */
        $middleware = $this->getNextMiddleware();

        if (!$middleware) {
            throw new WorkflowException('No suitable middleware was found to handle the request.');
        }

        $this->triggerWorkflowEvent('application.workflow.middleware.before', $middleware);

        $response = $middleware->process($request, $this);

        $this->triggerWorkflowEvent('application.workflow.middleware.after', $middleware);

        return $response;
    }

    public function handleException(ServerRequestInterface $request): ResponseInterface
    {
        /** @var MiddlewareInterface $middleware */
        $middleware = $this->getExceptionHandlers()->getNextMiddleware();

        if (!$middleware) {
            throw new WorkflowException('No suitable middleware was found to handle the uncaught exception.', null, $request->getAttribute('exception'));
        }

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
     * @return ClassLoader
     */
    public function getAutoloader(): ClassLoader
    {
        return $this->autoloader;
    }

    /**
     * @param ClassLoader $autoloader
     *
     * @return $this
     */
    public function setAutoloader(ClassLoader $autoloader)
    {
        $this->autoloader = $autoloader;

        return $this;
    }

    /**
     * @return Config
     */
    public function getConfig(): ConfigInterface
    {
        // init Config
        if (is_null($this->config)) {
            $this->config = new Config();
        }

        return $this->config;
    }

    /**
     * @param Config $config
     *
     * @return $this
     */
    public function setConfig(ConfigInterface $config): ApplicationInterface
    {
        $this->config = $config;

        return $this;
    }


    public function plug(MiddlewareInterface $middleware, ...$filters)
    {
        if ($middleware instanceof FiltersProviderInterface) {
            $middleware->getFilterEngine()->registerFilter(...$filters);
        }

        $this->middlewares->append($middleware);
    }

    /**
     * @return Collection
     */
    public function getPackages(): Collection
    {
        return $this->packages;
    }

    /**
     * @param Collection $packages
     */
    public function setPackages(Collection $packages)
    {
        $this->packages = $packages;
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
            new ActionNamespace()

        ];
    }

    protected function getConfigParams()
    {
        return [
            'application.name' => 'ObjectivePHP Starter Kit',
            'router.url-alias' => ['/' => 'Home'],
            'router.action-namespace' => ['default' => (new \ReflectionObject($this))->getNamespaceName() . '\\Action']
        ];
    }

}
