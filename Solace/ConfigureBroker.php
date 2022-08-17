#!/usr/local/bin/php
<?php
namespace Solace;
use Solace\Broker\Config;
use Solace\Broker\Queue;

require "Solace/Broker/Config.php";
require "Solace/Broker/Queue.php";


$config = new Config("https://mr-5cni9ouxlxj.messaging.solace.cloud:943", "essilor-admin", "79lgmh4dsvuerdsasn7ooq23gl", "essilor");
//$config = new Config("http://localhost:8080", "admin", "admin", "essilor");


for($i=0;$i<10;$i++)
{
    (new Queue ( $config,"queue_0$i"))
        ->createQueue()
        ->addSubscription("topic/0$i");
}




