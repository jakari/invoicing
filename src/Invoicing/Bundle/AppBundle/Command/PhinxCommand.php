<?php

namespace Invoicing\Bundle\AppBundle\Command;

use Phinx\Console\Command\Create;
use Phinx\Console\PhinxApplication;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

class PhinxCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('phinx:migrations')
            ->setDescription('Run phinx migration tool')
            ->addArgument(
                'args',
                InputArgument::IS_ARRAY,
                'Phinx command line args'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $c = $this->getContainer();

        $config = $c->getParameter('kernel.root_dir')
            . '/config/phinx.php';

        $args = $input->getArgument('args');
        $args[] = '-c ' . $config;
        $args[] = '-e ' . $c->getParameter('kernel.environment');

        $phinx = new PhinxApplication();
        return $phinx->run(new StringInput(implode(' ', $args)), $output);
    }
}
