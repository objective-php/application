<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 08/12/2015
     * Time: 12:22
     */
    
    namespace ObjectivePHP\Application\Workflow\Filter;
    
    
    use ObjectivePHP\Application\ApplicationInterface;

    /**
     * Class EncapsulatedFilter
     *
     * @package ObjectivePHP\Application\Workflow
     */
    class EncapsulatedFilter extends AbstractFilter
    {
        /**
         * @return bool
         */
        public function filter(ApplicationInterface $app) : bool
        {
            return $app->exec($this->getFilter());
        }

    }