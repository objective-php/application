<?php

    namespace ObjectivePHP\Application\Operation\Common;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Message\Request\HttpRequest;

    /**
     * Class RequestWrapper
     *
     * @package ObjectivePHP\Application\Operation\Common
     */
    class RequestWrapper
    {
        /**
         * @param ApplicationInterface $app
         */
        public function __invoke(ApplicationInterface $app)
        {
            if (isset($_SERVER['REQUEST_URI']))
            {
                $request = new HttpRequest($_SERVER['REQUEST_URI']);

                $app->setRequest($request);
            }
            else
            {
                // TODO handle cli requests
            }

        }
    }