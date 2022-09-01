#!/usr/local/bin/php
<?php
namespace Solace;
use Solace\Broker\Config;
use Solace\Broker\Queue;

require_once "Broker/Config.php";
require_once "Broker/Queue.php";

$config = new Config("https://xyze.messaging.solace.cloud:943", "acme-admin", "xxxxxxxxxxx", "acme");

for($i=0;$i<10;$i++)
{
    (new Queue ( $config,"queue_0$i"))
        ->createQueue()
        ->addSubscription("topic/0$i");
}




