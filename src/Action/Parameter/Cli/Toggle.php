<?php
/**
 * This file is part of the Objective PHP project
 *
 * More info about Objective PHP on www.objective-php.org
 *
 * @license http://opensource.org/licenses/GPL-3.0 GNU GPL License 3.0
 */

namespace ObjectivePHP\Application\Action\Parameter\Cli;


class Toggle extends AbstractParameter
{
    
    
    public function hydrate(string $cli): string
    {
        $value = 0;
        $cli = $this->tokenize($cli);
        // look for long name occurrences
        if ($long = $this->getLongName())
        {
            $pattern = '/\-\-(' . $long . ')/';
            if (preg_match_all($pattern, $cli, $matches))
            {
                $value += count($matches[1]);
                $cli = str_replace('--' . $long, '', $cli);
            }
        
        }
        // look for short name occurrences
        if ($short = $this->getShortName())
        {
            while (preg_match_all('/(?:^|\s)(\-[a-zA-Z]*?([' . $short . ']+)[a-zA-Z]*?)(?:\s+|$)/', $cli, $matches))
            {
                foreach ($matches[1] as $i => $match)
                {
                    $value += preg_match_all('/' . $short . '/', $match);
                    $replacement = str_replace($short, '', $match);
                    if ($replacement == '-') $replacement = '';
                    $cli = str_replace($match, $replacement, $cli);
                }
            }
            
        }
        
        
        $this->setValue($value);
        
        $cli = implode(' ', preg_split('/\s+/', $cli));
        
        return trim($this->untokenize($cli));
    }
}
