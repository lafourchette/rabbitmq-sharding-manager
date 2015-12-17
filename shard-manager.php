<?php

use RabbitMq\ManagementApi\Client;

require_once __DIR__ . '/vendor/autoload.php';

$baseUrl = 'http://rabbitmq.integ8.lafourchette.io:15672';
$username = 'lafourchette';
$password = 'lafourchette';

$vhost = '/tmp_vhost';
$exchange = 'tmp_exchange';
$queueTemplate = 'queue.{modulus}';
$routingTemplate = 'message.1.{modulus}';
$nbShard = 10;

$client = new Client(null, $baseUrl, $username, $password);

$client->vhosts()->create($vhost);
$client->permissions()->create($vhost, $username, array("configure" => ".*", "write" => ".*", "read" => ".*"));
$client->exchanges()->create($vhost, $exchange, array('type' => 'topic'));
for ($i = 0; $i < $nbShard; $i++) {
    $queue = modulus($queueTemplate, $i);
    $client->queues()->create($vhost, $queue, array('type' => 'topic'));
    $client->bindings()->create($vhost, $exchange, $queue, modulus($routingTemplate, $i));
}

function modulus($template, $modulus)
{
    return strtr($template, array('{modulus}' => $modulus));
}
