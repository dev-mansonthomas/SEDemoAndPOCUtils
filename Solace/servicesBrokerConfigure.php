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
foreach ($myServices as $oneService)
{
    $oneServiceDetails = $service->getServiceDetails($oneService['serviceId']);
    //print_r($oneServiceDetails);

    $AdminURL  = $AdminUser = $AdminPwd = $AdminVPN = "";

    foreach ($oneServiceDetails['managementProtocols'] as $oneManagementProtocol)
    {
        if($oneManagementProtocol['name'] == "SolAdmin" )
        {
            $AdminURL  = $oneManagementProtocol['endPoints'][0]['uris'][0];
            $AdminUser = $oneManagementProtocol['username'];
            $AdminPwd  = $oneManagementProtocol['password'];
            $AdminVPN  = $oneServiceDetails['msgVpnName'];
            break;
        }
    }

    /*
    echo "AdminURL=$AdminURL\n";
    echo "AdminUser=$AdminUser\n";
    echo "AdminPwd=$AdminPwd\n";
    echo "AdminVPN=$AdminVPN\n";
    */


    $config = new Config($AdminURL, $AdminUser, $AdminPwd, $AdminVPN, "/SEMP/v2/config", $servicesConfig->debug);

    for($i=0;$i<10;$i++)
    {
        (new Queue ( $config,"queue_0$i"))
            ->createQueue()
            ->addSubscription("topic/0$i");
    }

}