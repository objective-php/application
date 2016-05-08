<?php

    namespace ObjectivePHP\Application\Action;

    use ObjectivePHP\Application\Middleware\AbstractMiddleware;
    use ObjectivePHP\Application\Middleware\MiddlewareInterface;

    /**
     * Class DefaultAction
     *
     * @package ObjectivePHP\Application\Action
     */
    abstract class RenderableAction extends HttpAction implements RenderableActionInterface
    {

        /**
         * @var string
         */
        protected $viewTemplate;


        static protected $viewExtensions = ['phtml'];


        /**
         * @return string
         */
        public function getViewTemplate()
        {
            // set default view name
            if (is_null($this->viewTemplate))
            {

                $reflected = new \ReflectionObject($this);

                $viewTemplate = substr($reflected->getFileName(), 0, -4);

                $templateFound = false;

                foreach(self::$viewExtensions as $extension)
                {
                    if(file_exists($viewTemplate . '.' . ltrim($extension, '.')))
                    {
                        $templateFound = true;
                        break;
                    }
                }

                if(!$templateFound)
                {
                    // try to get parent template if any
                    $parentReflectedClass = $reflected->getParentClass();
                    if($parentReflectedClass->isInstantiable() && $parentReflectedClass->implementsInterface(RenderableActionInterface::class))
                    {
                        $parentViewTemplate = $parentReflectedClass->newInstance()->getViewTemplate();

                        $viewTemplate = $parentViewTemplate;
                    }

                }

                $this->viewTemplate = $viewTemplate;

            }

            return $this->viewTemplate;
        }

        /**
         * @param string $viewTemplate
         *
         * @return $this
         */
        public function setViewTemplate($viewTemplate)
        {
            $this->viewTemplate = $viewTemplate;

            return $this;
        }

        /**
         * @param $extension
         */
        static public function registerTemplateExtension($extension)
        {
            self::$viewExtensions[] = $extension;
            self::$viewExtensions   = array_unique(self::$viewExtensions);
        }
    }