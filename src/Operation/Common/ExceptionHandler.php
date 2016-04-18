<?php

    namespace ObjectivePHP\Application\Operation\Common;
    
    
    use ObjectivePHP\Application\ApplicationInterface;
    use ObjectivePHP\Html\Tag\Tag;
    use ObjectivePHP\Primitives\String\Str;
    use Zend\Diactoros\Response\HtmlResponse;
    use Zend\Diactoros\Response\SapiEmitter;
    use ObjectivePHP\Application\Workflow\Hook;

    /**
     * Class ExceptionHandler
     *
     * @package ObjectivePHP\Application\Task\Common
     */
    class ExceptionHandler
    {


        /**
         * @param ApplicationInterface $app
         */
        public function __invoke(ApplicationInterface $app)
        {
            $exception = $app->getException();

            $output = Tag::h1('An error occurred');

            $output .= Tag::h2('Workflow');

            foreach ($app->getExecutionTrace() as $step => $middlewares)
            {
                $output .= Tag::h3('Step: ' . $step);

                /**
                 * @var Hook $hook
                 */
                foreach($middlewares as $middleware)
                {
                    $output .= Tag::dt([$middleware->getReference() . ': ', $middleware->getDescription()]);
                }
            }

            do
            {
                $output .= $this->renderException($exception);
            } while($exception = $exception->getPrevious());

            // display config
            $output .= Tag::h2('Configuration');
            ob_start();
            var_dump($app->getConfig()->getInternalValue());
            $output .= ob_get_clean();

            // display services
            $output .= Tag::h2('Services');
            foreach($app->getServicesFactory()->getServices() as $spec) {
                $output .= Tag::h3($spec->getId());
                ob_start();
                var_dump($spec);
                $output .= ob_get_clean();
            }

            // manually emit response
            (new SapiEmitter())->emit((new HtmlResponse($output))->withStatus(500));

        }

        protected function renderException(\Throwable $exception)
        {
            $div = Tag::div(Tag::h2('Exception trace'), 'errors');



            $div->append(Tag::h2('Message'), Tag::pre($exception->getMessage()));
            $div->append(Tag::h2('File'), Tag::pre($exception->getFile())->append(':', $exception->getLine())
                                             ->setSeparator(''));

            // shorten Trace
            $trace = Str::cast($exception->getTraceAsString())->replace(getcwd() . '/', '');


            $div->append(Tag::h2('Trace'), Tag::pre($trace));

            return $div;
        }
    }