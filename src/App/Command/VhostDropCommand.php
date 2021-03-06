<?php

namespace App\Command;

use App\Entity\Vhost;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class VhostDropCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('vhost:drop')
            ->setDescription('Drop a vhost')
            ->addArgument('vhost', InputArgument::REQUIRED, 'Vhost name')
            ->addOption('rabbitmq-connection-uri', 'u', InputOption::VALUE_OPTIONAL, 'Specify the rabbitmq connection uri (amqp://{username}:{password}@{host}:{port}/{vhost})')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $uri = $input->getOption('rabbitmq-connection-uri');
        if (null !== $uri) {
            $this->setupClient($uri);
        }

        $client = $this->container->get('rabbitmq.client');
        $vhost = $input->getArgument('vhost');

        if (strpos($vhost, Vhost::VHOST_BASE_NAME) === false) {
            $output->writeln('<error>We\'re not able to drop a non-temporary vhost<error>');
            return;
        }

        $output->writeln('<info>Dropping vhost...</info>');
        $client->vhosts()->delete($vhost);
        $output->writeln('<info>Vhost dropped</info>');
    }
}
