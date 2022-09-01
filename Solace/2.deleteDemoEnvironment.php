#!/usr/local/bin/php
<?php
namespace Solace;

use Solace\Mesh\Mesh;
use Solace\Service\Service;
use Solace\Service\ServiceConfig;
use Solace\Service\ServicesConfig;


require_once "Mesh/Mesh.php";
require_once "Service/Service.php";
require_once "Service/ServiceConfig.php";
require_once "Service/ServicesConfig.php";

require_once "configDemoEnv.php";

/** @var ServicesConfig $servicesConfig */

$service = new Service($servicesConfig);

$mesh = new Mesh($servicesConfig, $service);
$mesh->deleteMyEventMesh();
$mesh->waitForMeshDeletion();

$service->deleteMyService();

echo "end of Delete Demo Env Script";
