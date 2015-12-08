<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 08/12/2015
     * Time: 12:18
     */
    
    namespace ObjectivePHP\Application\Workflow\Filter;

    use ObjectivePHP\Application\ApplicationInterface;

    /**
     * Interface FilterInterface
     *
     * @package ObjectivePHP\Application\Workflow
     */
    interface FilterInterface
    {
        /**
         * @return bool
         */
        public function filter(ApplicationInterface $app) : bool;
    }