<?php

    namespace ObjectivePHP\Application\Middleware;


    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\View\Helper\Vars;
    use ObjectivePHP\Message\Response\HttpResponse;
    use ObjectivePHP\Primitives\Collection\Collection;
    use ObjectivePHP\Message\Response\ResponseInterface;
    use Zend\Diactoros\Response;

    /**
     * Class ActionMiddleware
     *
     * @package ObjectivePHP\Application\Middleware
     */
    class ActionMiddleware extends EmbeddedMiddleware
    {

        /**
         * @param ApplicationInterface $app
         * @return mixed
         * @throws \ObjectivePHP\Primitives\Exception
         * @internal param ApplicationInterface $application
         *
         */
        public function run(ApplicationInterface $app)
        {

            $result = parent::run($app);

            if($result instanceof Response)
            {
                $app->setResponse($result);
            }
            else
            {
                // set default content type
                $app->setResponse((new HttpResponse())->withHeader('Content-Type', 'text/html'));

                Collection::cast($result)->each(function ($value, $var)
                {
                    Vars::set($var, $value);
                });
            }
        }

        /**
         * @return string
         */
        public function getDescription() : string
        {
            return 'Action Middleware encapsulating ' . parent::getDescription();
        }

    }