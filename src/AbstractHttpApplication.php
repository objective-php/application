<?php

namespace ObjectivePHP\Application;

use Composer\Autoload\ClassLoader;
use ObjectivePHP\Application\Config\Param;
use ObjectivePHP\Application\Exception\WorkflowException;
use ObjectivePHP\Application\Filter\FiltersProviderInterface;
use ObjectivePHP\Application\Middleware\MiddlewareRegistry;
use ObjectivePHP\Application\Package\PackageInterface;
use ObjectivePHP\Application\Workflow\PackagesInitListener;
use ObjectivePHP\Application\Workflow\PackagesReadyListener;
use ObjectivePHP\Config\Config;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\Primitives\Collection\Collection;
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
     * AbstractApplication constructor.
     *
     * @param ClassLoader|null $autoloader
     */
    public function __construct(ClassLoader $autoloader = null)
    {
        if ($autoloader) {
            $this->setAutoloader($autoloader);
        }

        $this->getEventsHandler()->trigger(Workflow::BOOTSTRAP_INIT);

        $this->middlewares = new MiddlewareRegistry();
        $this->exceptionHandlers = (new MiddlewareRegistry())->setDefaultInsertionPosition(MiddlewareRegistry::BEFORE_LAST);
        $this->packages = (new Collection())->restrictTo(PackageInterface::class);

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

        $this->getEventsHandler()->trigger(Workflow::BOOTSTRAP_DONE);


    }

    protected function registerPackage(PackageInterface $package, ...$filters)
    {
        $this->packages->append($package);
    }

    /**
     * @return EventsHandler
     */
    public function getEventsHandler(): EventsHandler
    {

        if (is_null($this->eventsHandler)) {
            $this->eventsHandler = new EventsHandler();
        }

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
        if (is_null($this->servicesFactory)) {
            $this->servicesFactory = new ServicesFactory();
        }

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
        foreach($packages as $package)
        {
            if($package instanceof PackagesInitListener)
            {
                $this->getEventsHandler()->bind(Workflow::PACKAGES_INIT, [$package, 'onPackagesInit']);
            }

            if($package instanceof PackagesReadyListener)
            {
                $this->getEventsHandler()->bind(Workflow::PACKAGES_INIT, [$package, 'onPackagesReady']);
            }
        }

        $this->getEventsHandler()->trigger(Workflow::PACKAGES_INIT);



        $this->getEventsHandler()->trigger(Workflow::PACKAGES_READY);

        $emitter = new Response\SapiEmitter();

        try {

            $this->getEventsHandler()->trigger(Workflow::REQUEST_HANDLING_START, $this);
            $response = $this->handle($this->getRequest());
            $this->getEventsHandler()->trigger(Workflow::REQUEST_HANDLING_DONE, $this, ['response' => $response]);
            $emitter->emit($response);
            $this->getEventsHandler()->trigger(Workflow::RESPONSE_SENT);
        } catch (\Throwable $exception) {

            $request = $this->getRequest()->withAttribute('exception', $exception);

            $response = $exceptionHandler->handle($request);

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

        $this->getEventsHandler()->trigger('application.workflow.middleware.before', $middleware);

        $response = $middleware->process($request, $this);

        $this->getEventsHandler()->trigger('application.workflow.middleware.after', $middleware);

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

            if (($middleware instanceof FiltersProviderInterface) && !$middleware->runFilters($this)) {
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
    public function getAutoloader()
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
     * @return Collection
     */
    public function getParams()
    {
        return $this->getConfig()->subset(Param::class);
    }

    /**
     * @return Config
     */
    public function getConfig(): Config
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
    public function setConfig(Config $config): ApplicationInterface
    {
        $this->config = $config;

        return $this;
    }

    /**
     * @param Collection $params
     *
     * @return $this
     */
    public function setParams($params)
    {

        foreach (Collection::cast($params) as $param => $value) {
            $this->getConfig()->import(new Param($param, $value));
        }

        return $this;
    }

    /**
     * @param      $param
     * @param null $default
     *
     * @return mixed|null
     * @throws \ObjectivePHP\Primitives\Exception
     */
    public function getParam($param, $default = null)
    {

        if ($this->getConfig()->subset(Param::class)->has($param)) {
            return $this->getConfig()->subset(Param::class)->get($param);
        }

        return $default;
    }

    /**
     * @param $param
     * @param $value
     *
     * @throws \ObjectivePHP\Primitives\Exception
     */
    public function setParam($param, $value)
    {
        $this->getConfig()->import(new Param($param, $value));

        return $this;
    }

    public function plug(MiddlewareInterface $middleware, ...$filters)
    {
        if ($middleware instanceof FiltersProviderInterface) {
            $middleware->getFilters()->append(...$filters);
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


}
