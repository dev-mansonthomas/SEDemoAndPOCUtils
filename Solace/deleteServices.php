#!/usr/local/bin/php
<?php
namespace Solace;

use Solace\Service\Service;
use Solace\Service\ServiceConfig;
use Solace\Service\ServicesConfig;

require "Service/Service.php";
require "Service/ServiceConfig.php";
require "Service/ServicesConfig.php";

$servicesConfig = new ServicesConfig(
    "https://api.solace.cloud",
    "eyJhbGciOiJSUzI1NiIsImtpZCI6Im1hYXNfcHJvZF8yMDIwMDMyNiIsInR5cCI6IkpXVCJ9.eyJvcmciOiJzb2xhY2VldmFuZ2VsaXN0cyIsIm9yZ1R5cGUiOiJFTlRFUlBSSVNFIiwic3ViIjoiZGhyY3B1cjk5aGsiLCJwZXJtaXNzaW9ucyI6IkFBQUFBSUFFQUFBQWZ6Z0FJQUFBQUFBQUFBQUFBQUFBQUFDQVNJY2hJQWpnTC83L2c1YmZCWmdEV0VBQUNBPT0iLCJhcGlUb2tlbklkIjoia25jeHZqMHdiY2oiLCJpc3MiOiJTb2xhY2UgQ29ycG9yYXRpb24iLCJpYXQiOjE2NTk2ODgzNzZ9.mofc6C3QAnl7VeDBwoT08XvcmwO0ANR2eeZgyogh6loOuuPPsEVhKKuO02Ih-G2TzqEFB2FgW9d8eeSq8J0PXngf9oKvdtEay4yTnSwbS93FzDdW2Kp5a75bGB0TPPa4trT-FQQmZ-asnguE1B76kuVoYkfPJeaoVhe8mRl3SeD6j3brUmHdyVI1a0-afE5ly_q2PCeCViuaPRMW-MRgFfwaPVm7rAb5tt90YZb5gy0GIuy9PbQ0s7tYyawaJdbNT--FyTjKPBs6_oLUP2RpkTFi43HuqEoFwa4XRrra6jvrzlOrgrZAVUQZ3b6ejTPmPRocSqWEfVbTp5Yne6MbeA",
[
    new ServiceConfig("ACME Rideshare Core"      , "aks-uksouth"          ),
    new ServiceConfig("ACME Rideshare Partner AF", "eks-af-south-1b"      ),
    new ServiceConfig("ACME Rideshare Partner US", "gke-gcp-us-central1-a"),
    new ServiceConfig("ACME Rideshare Partner JP", "eks-ap-northeast-1a"  )
],
    'ACME Event Mesh');


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


