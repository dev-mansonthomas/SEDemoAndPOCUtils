#!/usr/local/bin/php
<?php
namespace Solace;

use Solace\Broker\Config;
use Solace\Broker\Queue;
use Solace\Service\Service;
use Solace\Service\ServiceConfig;
use Solace\Service\ServicesConfig;


require_once "Service/Service.php";
require_once "Service/ServiceConfig.php";
require_once "Service/ServicesConfig.php";

require_once "Solace/Broker/Config.php";
require_once "Solace/Broker/Queue.php";

require_once "configDemoEnv.php";



/** @var ServicesConfig $servicesConfig */

$service     = new Service($servicesConfig);
//$serviceList = $service->getMyServiceList();
//print_r($serviceList);


$myServices = $service->getMyServiceList();
foreach ($myServices as $oneService)
{
    $oneServiceDetails = etails($oneService['serviceId']);
    print_r($oneServiceDetails);

    /*
    $config = new Config("https://mr-5cni9ouxlxj.messaging.solace.cloud:943", "essilor-admin", "xxxxxxxxxxx", "essilor");
//$config = new Config("http://localhost:8080", "admin", "admin", "essilor");


    for($i=0;$i<10;$i++)
    {
        (new Queue ( $config,"queue_0$i"))
            ->createQueue()
            ->addSubscription("topic/0$i");
    }
    */
}