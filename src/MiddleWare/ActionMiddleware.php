<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 07/12/2015
     * Time: 18:07
     */
    
    namespace ObjectivePHP\Application\Middleware;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Application\View\Helper\Vars;
    use ObjectivePHP\Primitives\Collection\Collection;

    /**
     * Class ActionMiddleware
     *
     * @package ObjectivePHP\Application\Middleware
     */
    class ActionMiddleware extends EncapsulatedMiddleware
    {

        /**
         * @param ApplicationInterface $application
         *
         * @return mixed
         */
        public function run(ApplicationInterface $app)
        {
            $result = parent::run($app);

            Collection::cast($result)->each(function ($value, $var)
            {
                Vars::set($var, $value);
            });
        }

        /**
         * @return string
         */
        public function getDetails()
        {
            return 'Action Middleware encapsulating ' . parent::getDetails();
        }

    }