#!/usr/local/bin/php
<?php
namespace Solace;

use Solace\Service\Service;
use Solace\Service\ServiceConfig;
use Solace\Service\ServicesConfig;


require_once "Service/Service.php";
require_once "Service/ServiceConfig.php";
require_once "Service/ServicesConfig.php";

require_once "configDemoEnv.php";
/** @var ServicesConfig $servicesConfig */
$service     = new Service($servicesConfig);
$serviceList = $service->getMyServiceList();
//print_r($serviceList);
echo "===========================================================";
foreach ($serviceList as $myService)
{
    //print_r($myService);
    echo "deleting service name='".$myService['name']."' id='".$myService['serviceId']."' ";
    $service->deleteService($myService['serviceId']);
}


