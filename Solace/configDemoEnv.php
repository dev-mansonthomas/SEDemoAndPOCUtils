#!/usr/local/bin/php
<?php
namespace Solace;

use Solace\Service\ServiceConfig;
use Solace\Service\ServicesConfig;

require_once "Service/ServiceConfig.php";
require_once "Service/ServicesConfig.php";
$servicesConfig = new ServicesConfig(
  "https://api.solace.cloud",
  rtrim(file_get_contents( $_SERVER['HOME']."/.cred/SolaceEvangelistAllPrivilegesToken.txt"), "\n"),
  [
      new ServiceConfig("PMU Core", "aks-uksouth"                ),
      new ServiceConfig("PMU AF"  , "eks-af-south-1b"            ),
      new ServiceConfig("PMU US"  , "gke-gcp-us-central1-a"      ),
      new ServiceConfig("PMU JP"  , "eks-ap-northeast-1a"        )/*,
      new ServiceConfig("ACME Rideshare Partner IN", "gke-gcp-asia-south1-a"      ),
      new ServiceConfig("ACME Rideshare Partner SG", "gke-gcp-asia-southeast1-a"  )*/
  ],
  'PMU Event Mesh',
  "TM-",
  false,
  true);



$servicesConfig = new ServicesConfig(
  "https://api.solace.cloud",
  rtrim(file_get_contents( $_SERVER['HOME']."/.cred/SolaceEvangelistAllPrivilegesToken.txt"), "\n"),
  [
      new ServiceConfig("ACME Rideshare Core"      , "aks-uksouth"                ),
      new ServiceConfig("ACME Rideshare Partner AF", "eks-af-south-1b"            ),
      new ServiceConfig("ACME Rideshare Partner US", "gke-gcp-us-central1-a"      ),
      new ServiceConfig("ACME Rideshare Partner JP", "eks-ap-northeast-1a"        ),
      new ServiceConfig("ACME Rideshare Partner IN", "gke-gcp-asia-south1-a"      ),
      new ServiceConfig("ACME Rideshare Partner SG", "gke-gcp-asia-southeast1-a"  )
],
'ACME Event Mesh',
  "TM-",
  false,
  true);

/*
 * /

$servicesConfig = new ServicesConfig(
"https://api.solace.cloud",
rtrim(file_get_contents( $_SERVER['HOME']."/.cred/SolaceSEs-AllPrivilegesToken.txt"), "\n"),
[
new ServiceConfig("Service-BE", "gke-gcp-europe-west1-b"),
new ServiceConfig("Service-UK", "gke-gcp-europe-west2-a"),
new ServiceConfig("Service-GE", "gke-gcp-europe-west3-a")
],
'Mesh',
"VT-",
false,
true);  /*      */

