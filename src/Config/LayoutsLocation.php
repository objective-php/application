<?php
    /**
     * This file is part of the Objective PHP project
     *
     * More info about Objective PHP on www.objective-php.org
     *
     * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
     */
    
    namespace ObjectivePHP\Application\Config;
    
    
    use ObjectivePHP\Config\StackDirective;
    use ObjectivePHP\Config\StackedValuesDirective;

    class LayoutsLocation extends StackedValuesDirective
    {
        public function __construct($value)
        {
            // TODO check path existence

            parent::__construct($value);
        }

    }
