<?php

    namespace ObjectivePHP\Application\Common\Renderer;


    class LayoutRenderer extends ViewRenderer
    {


        public function getViewName()
        {
            return $this->getApplication()->getConfig()->get('layouts.layout', $this->getApplication()->getConfig()->get('layouts.default', 'layout'));
        }

        public function getContext()
        {

            // insert view
            $context['content'] = ob_get_clean();

            // inject config
            $context['config'] = $this->getApplication()->getConfig();

            $viewContext = $this->getApplication()->getWorkflow()->getStep('run')->getEarlierEvent('execute')->getResults()[0];

            if(isset($viewContext['layout']))
            {
                foreach($viewContext['layout'] as $var => $value)
                {
                    $context[$var] = $value;
                }
            }

            return $context;
        }


        protected function getViewsLocations()
        {

            $layoutsLocations = array_reverse($this->application->getConfig()->get('layouts.locations', []));

            $locations = [];
            foreach ($layoutsLocations as $paths)
            {
                if (!is_array($paths))
                {
                    // only one action path has been set
                    $paths = [$paths];
                }

                $locations += $paths;

            }

            return $locations;
        }

        public function captureView()
        {
            ob_start();
        }

    }