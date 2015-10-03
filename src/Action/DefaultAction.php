<?php

    namespace ObjectivePHP\Application\Action;

    use ObjectivePHP\Primitives\String\String;

    /**
     * Class DefaultAction
     *
     * @package ObjectivePHP\Application\Action
     */
    abstract class DefaultAction extends AbstractAction implements RenderableActionInterface
    {
        /**
         * @var string
         */
        protected $viewTemplate;

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


                if(!is_file($viewTemplate))
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
    }