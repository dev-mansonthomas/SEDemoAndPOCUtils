#!/usr/local/bin/php
<?php
namespace Solace;

use Solace\Service\ServiceConfig;
use Solace\Service\ServicesConfig;

require_once "Service/ServiceConfig.php";
require_once "Service/ServicesConfig.php";

$servicesConfig = new ServicesConfig(
    "https://api.solace.cloud",
    rtrim(file_get_contents( $_SERVER['HOME']."/.cred/SolaceCloudToken.txt"), "\n"),
    [
        new ServiceConfig("ACME Rideshare Core"      , "aks-uksouth"          ),
        new ServiceConfig("ACME Rideshare Partner AF", "eks-af-south-1b"      )/*,
        new ServiceConfig("ACME Rideshare Partner US", "gke-gcp-us-central1-a"),
        new ServiceConfig("ACME Rideshare Partner JP", "eks-ap-northeast-1a"  ),
        new ServiceConfig("ACME Rideshare Partner IN", "gke-gcp-asia-south1-a") */
    ],
    'ACME Event Mesh',
    "TM-",
            true);
