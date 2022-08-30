#!/usr/local/bin/php
<?php
namespace Solace;

use Solace\Mesh\Mesh;
use Solace\Service\Service;
use Solace\Service\ServiceConfig;
use Solace\Service\ServicesConfig;

use Solace\Broker\Config;
use Solace\Broker\Queue;

require_once "Mesh/Mesh.php";
require_once "Service/Service.php";
require_once "Service/ServiceConfig.php";
require_once "Service/ServicesConfig.php";

require_once "Solace/Broker/Config.php";
require_once "Solace/Broker/Queue.php";


require_once "configDemoEnv.php";

/** @var ServicesConfig $servicesConfig */

$service = new Service($servicesConfig);
$service->createServices();
$service->waitForServicesCreation();

$mesh = new Mesh($servicesConfig, $service);
$mesh->createMesh();
$mesh->waitForMeshCreation();




//TODO :  broker configuration
//TODO : Load generation for DataDog

echo "end of create Demo Env Script";
