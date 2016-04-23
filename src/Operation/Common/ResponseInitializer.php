<?php
    /**
     * Created by PhpStorm.
     * User: gauthier
     * Date: 07/09/15
     * Time: 16:41
     */
    
    namespace ObjectivePHP\Application\Operation\Common;

    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Message\Response\HttpResponse;

    /**
     * Class ResponseInitializer
     *
     * @package ObjectivePHP\Application\Task\Common
     */
    class ResponseInitializer
    {
        /**
         * Instantiate Response
         *
         * @param WorkflowEvent $event
         */
        public function __invoke(ApplicationInterface $app)
        {
            // TODO handle CLI repsonse
            $app->setResponse(new HttpResponse());
        }
    }