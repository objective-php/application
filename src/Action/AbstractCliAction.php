<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Application\Action;


use ObjectivePHP\Application\Action\Parameter\Cli\Exception;
use ObjectivePHP\Application\Action\Parameter\Cli\ParameterInterface;
use ObjectivePHP\Application\ApplicationInterface;

abstract class AbstractCliAction implements CliActionInterface
{
    
    protected $expectedParameters = [];
    
    public function expects(ParameterInterface $parameter) 
    {
        if($shortName = $parameter->getShortName())
        {
            if(isset($this->expectedParameters[$shortName]))
            {
                var_dump($this->expectedParameters);
                throw new Exception(sprintf('Parameter "%s" has already been registered', $shortName));
            }
            
            $this->expectedParameters[$shortName] = $parameter;
        }
        
        if ($longName = $parameter->getLongName())
        {
            if (isset($this->expectedParameters[$longName]))
            {
                throw new Exception(sprintf('Parameter "%s" has already been registered', $longName));
            }
        
            $this->expectedParameters[$longName] = $parameter;
        }
        
        return $this;
    }
    
    public function getExpectedParameters()
    {
        return $this->expectedParameters;
    }
    
    
    public function  __invoke(ApplicationInterface $app) {
        return $this->run($app);
    }
    
    public function getUsage(): string
    {
        $output = 'Usage for command "' . $this->getCommand() . '":' . PHP_EOL ;
        $expectedParameters = $this->getExpectedParameters();
        $handledParameters = [];
        
        /** @var \ObjectivePHP\Application\Action\Parameter\Cli\ParameterInterface $parameter */
        foreach ($expectedParameters as $parameter)
        {
            if(in_array($parameter, $handledParameters)) continue;
            $shortName = $parameter->getShortName();
            $longName  = $parameter->getLongName();
            $output .= "\t";
            if ($shortName) $output .= '-' . $shortName;
            if ($shortName && $longName) $output .= " | ";
            if ($longName) $output .= '--' . $longName;
            $output .= "\t\t\t" . $parameter->getDescription();
            $output .= PHP_EOL;
            $handledParameters[] = $parameter;
        }
        
        return $output;
    }
    
    abstract public function run(ApplicationInterface $app);
}
