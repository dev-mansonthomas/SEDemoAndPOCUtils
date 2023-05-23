#!/usr/local/bin/php
<?php
namespace Solace;

use Solace\Broker\Config;
use Solace\Broker\Queue;
use Solace\Broker\User;

require_once "Broker/Config.php";
require_once "Broker/Queue.php";
require_once "Broker/User.php";

$config = new Config("https://xyze.messaging.solace.cloud:943", "acme-admin", "xxxxxxxxxxx", "acme");

(new User($config, "default", true, "default!!"))->updateUser();


for($i=0;$i<10;$i++)
{
    (new Queue ( $config,"queue_0$i"))
        ->createQueue()
        ->addSubscription("topic/0$i");
}
