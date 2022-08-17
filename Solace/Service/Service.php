<?php
namespace Solace\Service;

use Solace\Broker\Queue;
use Solace\Service\ServiceConfig;
use Solace\Service\ServicesConfig;

class Service
{
    private ServicesConfig $servicesConfig;

    public function __construct(ServicesConfig $servicesConfig)
    {
        $this->servicesConfig = $servicesConfig;
    }


    function generateFirstPart($method, $path):string
    {
        return "
curl --location --request ".$method." '".$this->servicesConfig->cloudAPIURL.$path."' \\
--header 'Content-Type: application/json'                                            \\
--header 'Accept: application/json'                                                  \\
--header 'Authorization: Bearer ".$this->servicesConfig->cloudAPIToken."'            \\
";
    }

    public function createService(ServiceConfig $serviceConfig):void
    {
        //--header 'Cookie: Session=RrE4kpRYO9gF-djljbx8-R6ieySYAZDkR9cXHDS_WZs' \
        $curlCommand =
            $this->generateFirstPart("POST","/api/v0/services").
            "--data-raw '{
  \"type\": \"service\",
  \"name\": \"".$serviceConfig->name."\",
  \"serviceTypeId\": \"".$serviceConfig->type."\",
  \"serviceClassId\": \"".$serviceConfig->class."\",
  \"adminState\": \"start\",
  \"redundancyGroupSslEnabled\": true,
  \"datacenterId\": \"".$serviceConfig->dataCenterId."\",
  \"partitionId\": \"default\",
  \"eventBrokerVersion\": \"".$serviceConfig->brokerVersion."\",
  \"msgVpnName\": \"".str_replace(" ", "-", strtolower($serviceConfig->name))."\",
  \"messagingStorage\": 25,
  \"serviceConnectionEndpoints\": [
    {
      \"name\": \"Public Endpoint\",
      \"description\": \"\",
      \"accessType\": \"public\",
      \"type\": \"LoadBalancer\",
      \"ports\": {
        \"serviceSmfPlainTextListenPort\": 0,
        \"serviceSmfCompressedListenPort\": 0,
        \"serviceSmfTlsListenPort\": 55443,
        \"serviceWebPlainTextListenPort\": 0,
        \"serviceWebTlsListenPort\": 443,
        \"serviceAmqpPlainTextListenPort\": 0,
        \"serviceAmqpTlsListenPort\": 5671,
        \"serviceMqttPlainTextListenPort\": 0,
        \"serviceMqttWebSocketListenPort\": 0,
        \"serviceMqttTlsListenPort\": 8883,
        \"serviceMqttTlsWebSocketListenPort\": 8443,
        \"serviceRestIncomingPlainTextListenPort\": 0,
        \"serviceRestIncomingTlsListenPort\": 9443,
        \"serviceManagementTlsListenPort\": 943,
        \"managementSshTlsListenPort\": 0
      }
    }
  ]
}'
";
        echo "$curlCommand";
        echo "";
        $shellOutput = shell_exec($curlCommand);
        echo "\n\n\n".$shellOutput."\n\n\n";

    }


    public function createServices():void
    {
        foreach ($this->servicesConfig->services as $service)
        {
            $this->createService($service);
        }
    }


    public function getServiceList():array
    {

        $curlCommand =
            $this->generateFirstPart("GET","/api/v0/services?userOnly=true&page-size=100&page-number=0");

        //echo "$curlCommand";
        //echo "";
        $shellOutput = shell_exec($curlCommand);
        //echo "\n\n\n".$shellOutput."\n\n\n";

        return json_decode($shellOutput, true);
    }

    /**
     * get the service associated to this configuration (so that sevices deletion, mesh is only done to the one listed in this configuration and nothing else)
     */
    public function getMyServices():array
    {
        $serviceList = $this->getServiceList();

        $myServiceNames = [];

        foreach ($this->servicesConfig->services as $service)
        {
            $myServiceNames[]=$service->name;
        }

        $myServices = [];
        foreach($serviceList['data'] as $service)
        {
            if(in_array($service['name'], $myServiceNames))
                $myServices[$service['name']]=$service;
        }

        return $myServices;
    }



    public function deleteService(string $serviceId):void
    {
        $curlCommand =
            $this->generateFirstPart("DELETE","/api/v0/services/".$serviceId);

        echo "$curlCommand";
        echo "";
        $shellOutput = shell_exec($curlCommand);
        echo "\n\n\n".$shellOutput."\n\n\n";
    }

}