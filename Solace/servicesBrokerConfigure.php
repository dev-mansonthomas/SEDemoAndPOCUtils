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

require_once "Broker/Config.php";
require_once "Broker/Queue.php";

require_once "configDemoEnv.php";



/** @var ServicesConfig $servicesConfig */

$service     = new Service($servicesConfig);
//$serviceList = $service->getMyServiceList();
//print_r($serviceList);


$myServices = $service->getMyServiceList();
$numberOfServices = count($myServices);

foreach ($myServices as $oneService)
{
    $serviceAdminDetails = $service->getServiceAdminDetails($oneService['serviceId']);

    $config = new Config(   $serviceAdminDetails['AdminURL' ],
                            $serviceAdminDetails['AdminUser'],
                            $serviceAdminDetails['$AdminPwd'],
                            $serviceAdminDetails['$AdminVPN'],
                            "/SEMP/v2/config"       ,
                            $servicesConfig->debug          );

    for($i=0;$i<$numberOfServices;$i++)
    {
        (new Queue ( $config,"queue_0$i" ) )
            ->createQueue()
            ->addSubscription("topic/0$i");
    }
}