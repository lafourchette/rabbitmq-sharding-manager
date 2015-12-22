<?php

namespace App\Command;

use RabbitMq\ManagementApi\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractCommand extends Command implements ContainerAwareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    protected function setupClient($uri)
    {
        $parsedUri = parse_url($uri);

        $client = new Client(
            null,
            sprintf('%s://%s:%s', $parsedUri['scheme'], $parsedUri['host'], $parsedUri['port']),
            $parsedUri['user'],
            $parsedUri['pass']
        );

        $this->container->set('rabbitmq.client', $client);
    }
}
