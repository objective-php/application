<?php

namespace ObjectivePHP\Application\Operation;


use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Cli\Request\CliRequest;
use Zend\Diactoros\Response\SapiEmitter;

/**
 * Class ResponseSender
 *
 * @package ObjectivePHP\Application\Task\Common
 */
class ResponseSender
{
    /**
     * @param ApplicationInterface $app
     */
    public function __invoke(ApplicationInterface $app)
    {
        if(!$app->getRequest() instanceof CliRequest)
        {
            $response = $app->getResponse();
            $emitter  = new SapiEmitter();
            $emitter->emit($response);
        }
    }

}
