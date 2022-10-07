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
foreach($serviceList as $oneService)
{
    //print_r($oneService);
    print_r($service->getServiceDetails($oneService['serviceId']));
}