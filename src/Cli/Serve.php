<?php


namespace ObjectivePHP\Application\Cli;


use League\CLImate\CLImate;
use ObjectivePHP\Application\ApplicationInterface;
use ObjectivePHP\Cli\Action\AbstractCliAction;
use ObjectivePHP\Cli\Action\Parameter\Param;
use ObjectivePHP\Cli\Application\CliApplicationInterface;

class Serve extends AbstractCliAction
{
    /**
     * Serve constructor.
     */
    public function __construct()
    {
        $this->setCommand('serve');
        $this->setDescription('PHP internal server launcher');
        $this->expects(new Param(['p'=>'port'], 'Server port (default to 8080)'));
        $this->expects(new Param(['e'=>'env'], 'Application environment (default to "dev")'));
        $this->expects(new Param(['r'=>'router'], 'Router file'));
        $this->expects(new Param(['root'], 'Document root (default to "public")'));
    }


    /**
     * @param CliApplicationInterface $app
     * @return mixed|void
     */
    public function run(ApplicationInterface $app)
    {
        $c = new CLImate();
        $cmdLine = 'APPLICATION_ENV=' . $this->getParam('env', 'dev') . ' php -S 0.0.0.0:' . $this->getParam('port', 8080);
        $cmdLine .= ' -t ' . $this->getParam('root', 'public') . ' ' . $this->getParam('router', is_file('router.php') ? 'router.php' : '');
        $c->out('Launching web server using command line: ' . $cmdLine);
        passthru($cmdLine);
    }

}
