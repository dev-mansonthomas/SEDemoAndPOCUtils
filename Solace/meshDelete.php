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
$mesh = new Mesh($servicesConfig, new Service($servicesConfig));

$mesh->deleteMyEventMesh();

echo "end of Mesh Script";
