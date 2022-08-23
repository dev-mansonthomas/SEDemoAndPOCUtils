#!/usr/local/bin/php
<?php
namespace Solace;
use Solace\Broker\Config;
use Solace\Broker\Queue;

require_once "Solace/Broker/Config.php";
require_once "Solace/Broker/Queue.php";


$config = new Config("https://mr-5cni9ouxlxj.messaging.solace.cloud:943", "essilor-admin", "xxxxxxxxxxx", "essilor");
//$config = new Config("http://localhost:8080", "admin", "admin", "essilor");


for($i=0;$i<10;$i++)
{
    (new Queue ( $config,"queue_0$i"))
        ->createQueue()
        ->addSubscription("topic/0$i");
}




