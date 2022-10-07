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

require_once "Broker/Config.php";
require_once "Broker/Queue.php";


require_once "configDemoEnv.php";

/** @var ServicesConfig $servicesConfig */

$service = new Service($servicesConfig);
$service->createServices();
$service->waitForServicesCreation();

$mesh = new Mesh($servicesConfig, $service);
$mesh->createMesh();
$mesh->waitForMeshCreation();


$myServices = $service->getMyServiceList();
$numberOfServices = count($myServices);
$j=1;
foreach ($myServices as $oneService)
{
    $serviceAdminDetails = $service->getServiceAdminDetails($oneService['serviceId']);

    $config = new Config(   $serviceAdminDetails['AdminURL' ],
                            $serviceAdminDetails['AdminUser'],
                            $serviceAdminDetails['AdminPwd' ],
                            $serviceAdminDetails['AdminVPN' ],
                            $servicesConfig->debug          );

    for($i=0;$i<$numberOfServices;$i++)
    {
        if($i == 0 || $i == $j || $i%$j == 0)
            (new Queue ( $config,"queue_0$i" ) )
                ->createQueue()
                ->addSubscription("topic/0$i");
    }
    $j++;
}


//TODO :  broker configuration
//TODO : Load generation for DataDog

echo "end of create Demo Env Script";
