<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Application\Action\Parameter\Cli;


use ObjectivePHP\Primitives\String\Str;

abstract class AbstractParameter implements ParameterInterface
{
    protected $options = 0;
    
    protected $shortName = '';
    
    protected $longName = '';
    
    protected $value;
    
    protected $description;
    
    protected $tokens = [' ' => '_*_*SPACE*_*_'];
    
    public function __construct($name, $description = '', $options = 0)
    {
        $this->setName($name);
        $this->setDescription($description);
        $this->setOptions($options);
    }
    
    
    /**
     * @return int
     */
    public function getOptions(): int
    {
        return $this->options;
    }
    
    /**
     * @param int $options
     *
     * @return $this
     */
    public function setOptions(int $options)
    {
        $this->options = $options;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getShortName() : string
    {
        return $this->shortName;
    }
    
    /**
     * @param string|array $name
     *
     * @return $this
     */
    public function setName($name)
    {
        if(is_array($name))
        {
            reset($name);
            $shortName = key($name);
            $longName  = current($name);
    
            if (strlen($shortName) !== 1)
            {
                throw new Exception('Short parameters name has to be exactly one character long');
            }
            
            $this->shortName = $shortName;
            $this->longName = $longName;
        }
        else if (strlen($name) == 1)
        {
            $this->shortName = $name;
        }
        else {
            $this->longName = $name;
        }
        
        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getLongName() : string
    {
        return $this->longName;
    }
    
    
    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * @param mixed $value
     *
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        
        return $this;
    }
    
    /**
     * @return string
     */
    public function getDescription() : string
    {
        return $this->description;
    }
    
    /**
     * @param mixed $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        
        return $this;
    }
    
    protected function tokenize($string) : string
    {
        $string = new Str($string);
        foreach($this->tokens as $characterPattern => $token)
        {
            $pattern = '/((["\']).*?(' . $characterPattern . ').*?(?:\\2))/';
            if(preg_match_all($pattern, $string, $matches))
            {
                foreach($matches[0] as $i => $match)
                {
                    $string->replace($match, preg_replace('/' . $characterPattern . '/', $token, $match));
                }
            }
        }
        
        return (string) $string;
    }
    
    protected function untokenize($string) : string
    {
        $string = new Str($string);
        foreach ($this->tokens as $characterPattern => $token)
        {
            $string->replace($token, $characterPattern);
        }
        
        return (string) $string;
    }
}
