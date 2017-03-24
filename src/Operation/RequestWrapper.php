<?php

    namespace ObjectivePHP\Application\Operation;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Exception;
    use ObjectivePHP\Application\Middleware\AbstractMiddleware;
    use ObjectivePHP\Cli\Request\CliRequest;
    use ObjectivePHP\Message\Request\HttpRequest;
    use Zend\Diactoros\ServerRequestFactory;

    /**
     * Class RequestWrapper
     *
     * @package ObjectivePHP\Application\Operation\Common
     */
    class RequestWrapper extends AbstractMiddleware
    {

        /**
         * @param ApplicationInterface $app
         */
        public function run(ApplicationInterface $app)
        {
            if (isset($_SERVER['REQUEST_URI']))
            {
                $uri = ServerRequestFactory::marshalUriFromServer(
                    $_SERVER,
                    ServerRequestFactory::marshalHeaders($_SERVER)
                );

                $request = new HttpRequest($uri, $_SERVER['REQUEST_METHOD']);
                $request->setGet($_GET);
                $request->setPost($_POST);
                
            }
            else if(class_exists(CliRequest::class))
            {
                $request = new CliRequest($_SERVER['argv'][1] ?? null, 'CLI');
            }
            else {
                throw new Exception("No request matches current environment");
            }
    
            $this->getApplication()->setRequest($request);

        }

    }
