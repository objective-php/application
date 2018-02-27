<?php

namespace ObjectivePHP\Application;

use Composer\Autoload\ClassLoader;
use ObjectivePHP\Application\Config\Param;
use ObjectivePHP\Application\Exception\Workflow;
use ObjectivePHP\Application\Exception\WorkflowException;
use ObjectivePHP\Application\Operation\ExceptionHandler;
use ObjectivePHP\Application\Package\PackageInterface;
use ObjectivePHP\Application\Workflow\Filter\FiltersProviderInterface;
use ObjectivePHP\Config\Config;
use ObjectivePHP\Config\Loader\DirectoryLoader;
use ObjectivePHP\Events\EventsHandler;
use ObjectivePHP\Invokable\Invokable;
use ObjectivePHP\Invokable\InvokableInterface;
use ObjectivePHP\Matcher\Matcher;
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
abstract class AbstractApplication implements ApplicationInterface
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
     * @var InvokableInterface
     */
    protected $exceptionHandler;
    
    /**
     * @var \Throwable
     */
    protected $exception;
    
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
     * @var ResponseInterface
     */
    protected $response;
    
    
    /**
     * @var Collection
     */
    protected $steps;
    
    /**
     * @var array
     */
    protected $executionTrace = [];
    
    /**
     * @var
     */
    protected $currentExecutionStack;
    
    /**
     * @var Matcher
     */
    protected $routeMatcher;
    
    /**
     * @var  Collection
     */
    protected $middlewares;
    
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
        
        $this->middlewares  = (new Collection())->restrictTo(MiddlewareInterface::class);
        $this->packages  = (new Collection())->restrictTo(PackageInterface::class);
        $this->routeMatcher = (new Matcher())->setSeparator('/');
        
        // set default Exception Handler
        $this->setExceptionHandler(new ExceptionHandler());
        
        // init http request
        if (!$this->isCli()) {
            $request = ServerRequestFactory::fromGlobals(
                $_SERVER,
                $_GET,
                $_POST,
                $_COOKIE,
                $_FILES
            );
            
            $this->setRequest($request);
        }
        // let ServicesFactory and EventsHandler know each other
        $this->getEventsHandler()->setServicesFactory($this->getServicesFactory());
        
        // initialize application by plugging middlewares
        $this->init();
        
        $this->getEventsHandler()->trigger(Workflow::BOOTSTRAP_DONE);
    }
    
    public function isCli()
    {
        return php_sapi_name() === 'cli';
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
     *
     */
    public function loadConfig($path)
    {
        $configLoader = new DirectoryLoader();
        
        $this->config = $configLoader->load($path);
        
        return $this;
    }
    
    /**
     * @return bool
     */
    public function hasRequest(): bool
    {
        return (bool)$this->request;
    }
    
    /**
     * @return ResponseInterface
     */
    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
    
    /**
     * @param Response $response
     *
     * @return $this
     */
    public function setResponse(ResponseInterface $response): ApplicationInterface
    {
        $this->response = $response;
        
        return $this;
    }
    
    public function run()
    {
        try {
            
            $this->getEventsHandler()->trigger();
            
            $response = $this->handle($this->getRequest());
            $emitter  = new Response\SapiEmitter();
            $emitter->emit($response);
        } catch (\Throwable $e) {
            $this->setException($e);
            $exceptionHandler = $this->getExceptionHandler();
            $exceptionHandler($this);
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
     * @return InvokableInterface
     */
    public function getExceptionHandler(): InvokableInterface
    {
        return $this->exceptionHandler;
    }
    
    /**
     * @param  $exceptionHandler
     *
     * @return $this
     */
    public function setExceptionHandler($exceptionHandler): ApplicationInterface
    {
        $this->exceptionHandler = Invokable::cast($exceptionHandler);
        
        return $this;
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
     * @return Collection
     */
    public function getMiddlewares(): Collection
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
    
    /**
     * @return Matcher
     */
    public function getRouteMatcher()
    {
        return $this->routeMatcher;
    }
    
    /**
     * @param Matcher $routeMatcher
     *
     * @return $this
     */
    public function setRouteMatcher($routeMatcher)
    {
        $this->routeMatcher = $routeMatcher;
        
        return $this;
    }
    
    /**
     * @return \Throwable
     */
    public function getException(): \Throwable
    {
        return $this->exception;
    }
    
    /**
     * @param \Throwable $exception
     *
     * @return $this
     */
    public function setException(\Throwable $exception): ApplicationInterface
    {
        $this->exception = $exception;
        
        return $this;
    }
    
    /**
     * @return array
     */
    public function getExecutionTrace(): array
    {
        return $this->executionTrace;
    }
    
    public function plug(MiddlewareInterface $middleware, ...$filters)
    {
        if ($middleware instanceof FiltersProviderInterface) {
            $middleware->getFilters()->append(...$filters);
        }
        
        $this->middlewares->append($middleware);
    }
    
    
}
