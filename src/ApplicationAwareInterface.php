<?php

    namespace ObjectivePHP\Application;

    /**
     * Interface ApplicationAwareInterface
     *
     * @package ObjectivePHP\Application
     */
    interface ApplicationAwareInterface
    {
        /**
         * @param ApplicationInterface $application
         */
        public function setApplication(ApplicationInterface $application);
    }