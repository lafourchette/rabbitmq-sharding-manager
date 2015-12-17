<?php

namespace App\Command;

use App\Entity\Vhost;
use Doctrine\DBAL\Platforms\SQLAnywhere11Platform;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class VhostConfigureCommand extends AbstractCommand
{
    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('vhost:configure')
            ->setDescription('Setup a sharding environment on a new vhost')
            ->addArgument('routing-key-template', InputArgument::REQUIRED, 'Define routing template, should look like "routing.1.{modulus}.#"')
            ->addArgument('modulus', InputArgument::REQUIRED, 'How many shards do you need ?')
        ;
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->container->get('rabbitmq.client');
        $username = $this->container->getParameter('rabbitmq.username');
        $vhost = sprintf(Vhost::VHOST_TEMPLATE, uniqid());
        $exchange = 'exchange';
        $queueTemplate = 'queue.{modulus}';
        $routingKeyTemplate = $input->getArgument('routing-key-template');
        $modulus = $input->getArgument('modulus');

        if (strpos($routingKeyTemplate, '{modulus}') === false) {
            $output->writeln('<error>Routing key template should contain a "{modulus}" mask<error>');
            return;
        }

        if ($modulus <= 0) {
            $output->writeln('<error>Modulus should be an unsigned integer<error>');
            return;
        }

        $output->writeln('<info>Configuring vhost...</info>');
        $client->vhosts()->create($vhost);
        $client->permissions()->create($vhost, $username, array("configure" => ".*", "write" => ".*", "read" => ".*"));
        $output->writeln(sprintf('Vhost created [<info>%s</info>]', $vhost));
        $client->exchanges()->create($vhost, $exchange, array('type' => 'topic'));
        $output->writeln('Exchange created');
        $output->write('Creating queues ');
        for ($i = 0; $i < $modulus; $i++) {
            $queue = $this->modulus($queueTemplate, $i);
            $client->queues()->create($vhost, $queue, array('type' => 'topic'));
            $client->bindings()->create($vhost, $exchange, $queue, $this->modulus($routingKeyTemplate, $i));
            $output->write('.');
        }
        $output->writeln('');
        $output->writeln('<info>Vhost configured</info>');
    }

    private function modulus($template, $modulus)
    {
        return strtr($template, array('{modulus}' => $modulus));
    }
}
