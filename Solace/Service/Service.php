<?php
namespace Solace\Service;

use Solace\Broker\Queue;
use Solace\Service\ServiceConfig;
use Solace\Service\ServicesConfig;

class Service
{
    private ServicesConfig $servicesConfig;

    private string $SERVICE_CREATION_COMPLETED="completed";
    private string $SERVICE_CREATION_IN_PROGRESS="inProgress";
    private string $SERVICE_CREATION_FAILED="failed";//TODO Check the value

    public function __construct(ServicesConfig $servicesConfig)
    {
        $this->servicesConfig = $servicesConfig;
    }


    function generateFirstPart($method, $path):string
    {
        return "
curl -s --location --request ".$method." '".$this->servicesConfig->cloudAPIURL.$path."' \\
--header 'Content-Type: application/json'                                            \\
--header 'Accept: application/json'                                                  \\
--header 'Authorization: Bearer ".$this->servicesConfig->cloudAPIToken."'            \\
";
    }

    function logAsDebug($subject, $content):void
    {
        if($this->servicesConfig->debug)
        {


            printf(  "
######################################################################################
# >>>DEBUG<<< START >>>  %-37s <<< START >>>DEBUG<<< #
######################################################################################
", $subject);

            echo $content;
            
            printf(  "
######################################################################################
# >>>DEBUG<<< END   >>>  %-37s <<<   END >>>DEBUG<<< #
######################################################################################
", $subject);

        }

    }

    public function createService(ServiceConfig $serviceConfig):void
    {
      $storage = "";
      if($serviceConfig->storageIsConfigurable)
      {
        $storage = "
\"messagingStorage\": ".$serviceConfig->brokerStorageSize.",
";
      }



      $curlCommand =
        $this->generateFirstPart("POST","/api/v0/services").
        "--data-raw '{
  \"type\": \"service\",
  \"name\": \"".$this->servicesConfig->objectNamePrefix.$serviceConfig->name."\",
  \"serviceTypeId\": \"".$serviceConfig->type."\",
  \"serviceClassId\": \"".$serviceConfig->class."\",
  \"adminState\": \"start\",
  \"redundancyGroupSslEnabled\": true,
  \"datacenterId\": \"".$serviceConfig->dataCenterId."\",
  \"partitionId\": \"default\",
  \"eventBrokerVersion\": \"".$serviceConfig->brokerVersion."\",
  \"msgVpnName\": \"".str_replace(" ", "-", strtolower($serviceConfig->name))."\",
  $storage
  \"serviceConnectionEndpoints\": [
    {
      \"name\": \"Public Endpoint\",
      \"description\": \"\",
      \"accessType\": \"public\",
      \"type\": \"LoadBalancer\",
      \"ports\": {
        \"serviceSmfPlainTextListenPort\": 55555,
        \"serviceSmfCompressedListenPort\": 55003,
        \"serviceSmfTlsListenPort\": 55443,
        \"serviceWebPlainTextListenPort\": 80,
        \"serviceWebTlsListenPort\": 443,
        \"serviceAmqpPlainTextListenPort\": 5672,
        \"serviceAmqpTlsListenPort\": 5671,
        \"serviceMqttPlainTextListenPort\": 1883,
        \"serviceMqttWebSocketListenPort\": 8000,
        \"serviceMqttTlsListenPort\": 8883,
        \"serviceMqttTlsWebSocketListenPort\": 8443,
        \"serviceRestIncomingPlainTextListenPort\": 9000,
        \"serviceRestIncomingTlsListenPort\": 9443,
        \"serviceManagementTlsListenPort\": 943,
        \"managementSshTlsListenPort\": 22
      }
    }
  ]
}'
";
        $this->logAsDebug("CURL COMMAND",$curlCommand);
        $shellOutput = shell_exec($curlCommand);
        $this->logAsDebug("CURL OUTPUT",$shellOutput);

    }


    public function createServices():void
    {
        foreach ($this->servicesConfig->services as $service)
        {
            $service->storageIsConfigurable = $this->servicesConfig->storageIsConfigurable;
            $this->createService($service);
        }
    }


    public function getServiceList():array
    {

        $curlCommand =
            $this->generateFirstPart("GET","/api/v0/services?userOnly=true&page-size=100&page-number=0");

        $this->logAsDebug("CURL COMMAND",$curlCommand);
        $shellOutput = shell_exec($curlCommand);
        $this->logAsDebug("CURL OUTPUT",$shellOutput);



        return json_decode($shellOutput, true)['data'];
    }


    public function getServiceDetails(string $serviceId):array
    {

        $curlCommand =
            $this->generateFirstPart("GET","/api/v0/services/".$serviceId."?connectionDetails=true");

        $this->logAsDebug("CURL COMMAND",$curlCommand);
        $shellOutput = shell_exec($curlCommand);
        $this->logAsDebug("CURL OUTPUT",$shellOutput);



        return json_decode($shellOutput, true)['data'];
    }

    public function getServiceAdminDetails(string $serviceId):array
    {
        $oneServiceDetails = $this->getServiceDetails($serviceId);
        //print_r($oneServiceDetails);

        $AdminURL  = $AdminUser = $AdminPwd = $AdminVPN = "";

        foreach ($oneServiceDetails['managementProtocols'] as $oneManagementProtocol)
        {
            if($oneManagementProtocol['name'] == "SolAdmin" )
            {
                $AdminURL  = $oneManagementProtocol['endPoints' ][0]['uris'][0];
                $AdminUser = $oneManagementProtocol['username'  ];
                $AdminPwd  = $oneManagementProtocol['password'  ];
                $AdminVPN  = $oneServiceDetails    ['msgVpnName'];
                break;
            }
        }

        return ['AdminURL'=>$AdminURL,'AdminUser'=>$AdminUser,'AdminPwd'=>$AdminPwd,'AdminVPN'=>$AdminVPN];
    }



    /**
     * get the service associated to this configuration (so that sevices deletion, mesh is only done to the one listed in this configuration and nothing else)
     */
    public function getMyServiceList():array
    {
        $serviceList = $this->getServiceList();

        $myServiceNames = [];

        foreach ($this->servicesConfig->services as $service)
        {
            $myServiceNames[]=$this->servicesConfig->objectNamePrefix.$service->name;
        }

        $myServices = [];
        foreach($serviceList as $service)
        {
            if(in_array($service['name'], $myServiceNames))
                $myServices[$service['name']]=$service;
        }

        return $myServices;
    }

    public function waitForServicesCreation():void
    {
        echo "
######################################################################################
#                                                                                    #
#         WAITING FOR AN INITIAL ".$this->servicesConfig->initialWaitForServiceCreation." SECONDS FOR SERVICE CREATION TO COMPLETE        #
#                                                                                    #
######################################################################################
";
        sleep($this->servicesConfig->initialWaitForServiceCreation);

        $allServicesInProgress = true;
        do
        {
            $myServices = $this->getMyServiceList();

            $allCompleted = true;
            foreach ($myServices as $myService)
            {
                printf( "Service %-20s is with status %s\n", "'".$myService['name']."'", "'".$myService['adminProgress']."'");

                if($myService['adminProgress']==$this->SERVICE_CREATION_IN_PROGRESS)
                {
                    $allCompleted=false ;
                }
                else if($myService['adminProgress']==$this->SERVICE_CREATION_FAILED)
                {
                    echo "
######################################################################################
#                                                                                    #
#          One of the service creation >>>>FAILED<<<<                                #
#                                                                                    #
######################################################################################
";
                    print_r($myService);
                    exit(1);
                }
            }

            if($allCompleted)
                $allServicesInProgress=false;
            else
            {
                echo "
######################################################################################
#                                                                                    #
#    Some services creation are still in progress, sleeping from 30 more seconds     #
#                                                                                    #
######################################################################################
";
                sleep(30);
            }

        }
        while($allServicesInProgress);


    }



    public function deleteService(string $serviceId):void
    {
        $curlCommand =
            $this->generateFirstPart("DELETE","/api/v0/services/".$serviceId);

        $this->logAsDebug("CURL COMMAND",$curlCommand);
        $shellOutput = shell_exec($curlCommand);
        $this->logAsDebug("CURL OUTPUT",$shellOutput);
    }

    public function deleteMyService():void
    {
        $myServices = $this->getMyServiceList();
        foreach($myServices as $oneService)
        {
            $this->deleteService($oneService['serviceId']);
        }
    }


    public function getDataCenterList():array
    {
        $curlCommand =
            $this->generateFirstPart("GET","/api/v0/datacenters");

        $this->logAsDebug("CURL COMMAND",$curlCommand);
        $shellOutput = shell_exec($curlCommand);
        $this->logAsDebug("CURL OUTPUT",$shellOutput);

        return json_decode($shellOutput, true)['data'];
    }

}