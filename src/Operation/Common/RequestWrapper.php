<?php

    namespace ObjectivePHP\Application\Operation\Common;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Invokable\InvokableInterface;
    use ObjectivePHP\Message\Request\HttpRequest;

    /**
     * Class RequestWrapper
     *
     * @package ObjectivePHP\Application\Operation\Common
     */
    class RequestWrapper implements InvokableInterface
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

        /**
         * @return string
         */
        public function getDescription() : string
        {
            return 'Request initializer';
        }


    }