<?php
/**
 * Created by PhpStorm.
 * User: gauthier
 * Date: 13/12/2015
 * Time: 10:21
 */

namespace ObjectivePHP\Application\Exception\Filter;


use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Application\Exception\ApplicationException;
use ObjectivePHP\Primitives\Collection\Collection;

class ContentTypeFilter extends AbstractFilter
{
    public function run(ApplicationInterface $app)
    {
        $response = $app->getResponse();

        if(!$response)
        {
            throw new ApplicationException(sprintf('Cannot filter response ContentType against "%s" because no response has been set', $this->getFilter()->join(', ')));
        }

        $contentTypes = $response->getHeader('Content-Type');

        $result = false;

        $this->getFilter()->each(function ($filter) use ($contentTypes, &$result)
        {
            foreach($contentTypes as $contentType) {
                if(strpos($contentType, $filter) === 0)
                {
                    $result = true;
                    break;
                }
            }
        });

        return $result;

    }

    public function getDescription() : string
    {
        return sprintf('Filter based on Response ContentType header (validates "%s" content type)', $this->getFilter()->join(', '));
    }

    public function getFilter()
    {
        return Collection::cast($this->filter);
    }


}
