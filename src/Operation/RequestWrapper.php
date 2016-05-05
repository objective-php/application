<?php

    namespace ObjectivePHP\Application\Operation;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\Middleware\AbstractMiddleware;
    use ObjectivePHP\Message\Request\HttpRequest;

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
                $request = new HttpRequest($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
                $request->setGet($_GET);
                $request->setPost($_POST);
                $this->getApplication()->setRequest($request);
            }
            else
            {
                // TODO handle cli requests
            }

        }

    }
