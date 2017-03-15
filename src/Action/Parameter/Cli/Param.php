<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Application\Action\Parameter\Cli;


class Param extends AbstractParameter
{
    
    
    public function hydrate(string $cli): string
    {
        $multiple = $this->getOptions() & self::MULTIPLE;
        $value = $multiple ? [] : '';
        
        // look for long name occurrences
        if ($long = $this->getLongName())
        {
            $pattern = '/(?:^|\s)(\-\-' . $long . '\s+(["\\\']{0,1})(.*?)(\2)(?:\s+|$))/';
            if (preg_match_all($pattern, $cli, $matches))
            {
                foreach($matches[1] as $i => $match) {
                    if ($multiple)
                    {
                        $value[] = $matches[3][$i];
                    }
                    else $value = $matches[3][$i];
                    $cli = str_replace($match, '', $cli);
                }
            }
        
        }
        // look for short name occurrences
        if ($short = $this->getShortName())
        {
            $pattern = '/(?:^|\s)(\-' . $short . '\s+(["\\\']{0,1})(.*?)(\2)(?:\s+|$))/';
            while (preg_match_all($pattern, $cli, $matches))
            {
                foreach ($matches[1] as $i => $match)
                {
                    if($multiple) $value[] = $matches[3][$i];
                    else $value = $matches[3][$i];
                    $cli = str_replace($match, '', $cli);
                }
            }
            
        }
        
        
        $this->setValue($value);
        
        return trim($cli);
    }
}
