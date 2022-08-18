<?php
namespace Solace\Service;

use Solace\Broker\Queue;
use Solace\Service\ServiceConfig;
use Solace\Service\ServicesConfig;

class Mesh
{
    private ServicesConfig $servicesConfig;
    private Service        $service;

    private string $MESH_CREATION_COMPLETED="completed";
    private string $MESH_CREATION_IN_PROGRESS="inProgress";
    private string $MESH_CREATION_FAILED="failed";//TODO Check the value

    public function __construct(ServicesConfig $servicesConfig, Service $service)
    {
        $this->servicesConfig = $servicesConfig;
        $this->service        = $service;
    }


    function generateFirstPart($method, $path):string
    {

        $myServices = $this->service->getMyServiceList();




        return "
curl --location --request ".$method." '".$this->servicesConfig->cloudAPIURL.$path."' \\
--header 'Content-Type: application/json'                                            \\
--header 'Accept: application/json'                                                  \\
--header 'Authorization: Bearer ".$this->servicesConfig->cloudAPIToken."'            \\
";
    }

    public function createMesh(ServiceConfig $serviceConfig):void
    {
        //--header 'Cookie: Session=RrE4kpRYO9gF-djljbx8-R6ieySYAZDkR9cXHDS_WZs' \
        $curlCommand =
            $this->generateFirstPart("POST","/api/v0/meshManager/eventMeshes").
            "--data-raw '{
  \"eventMeshName\": \"ACME Event Mesh\",
  \"services\": [
    \"f4me4ykpyp2\",
    \"qz6t09437qp\",
    \"cutxwyn8jwn\",
    \"47zy5s1of30\"
  ],
  \"links\": [
    {
      \"serviceId\": \"f4me4ykpyp2\",
      \"serviceName\": \"ACME Rideshare Partner AF\",
      \"links\": [
        {
          \"initiatorServiceId\": \"qz6t09437qp\",
          \"remoteServiceId\": \"f4me4ykpyp2\",
          \"remoteEndpointId\": \"b01h34rzgkp\"
        },
        {
          \"initiatorServiceId\": \"cutxwyn8jwn\",
          \"remoteServiceId\": \"f4me4ykpyp2\",
          \"remoteEndpointId\": \"b01h34rzgkp\"
        },
        {
          \"initiatorServiceId\": \"47zy5s1of30\",
          \"remoteServiceId\": \"f4me4ykpyp2\",
          \"remoteEndpointId\": \"b01h34rzgkp\"
        }
      ]
    },
    {
      \"serviceId\": \"qz6t09437qp\",
      \"serviceName\": \"ACME Rideshare Partner US\",
      \"links\": [
        {
          \"initiatorServiceId\": \"qz6t09437qp\",
          \"remoteServiceId\": \"f4me4ykpyp2\",
          \"remoteEndpointId\": \"b01h34rzgkp\"
        },
        {
          \"initiatorServiceId\": \"cutxwyn8jwn\",
          \"remoteServiceId\": \"qz6t09437qp\",
          \"remoteEndpointId\": \"463n06mptdv\"
        },
        {
          \"initiatorServiceId\": \"47zy5s1of30\",
          \"remoteServiceId\": \"qz6t09437qp\",
          \"remoteEndpointId\": \"463n06mptdv\"
        }
      ]
    },
    {
      \"serviceId\": \"cutxwyn8jwn\",
      \"serviceName\": \"ACME Rideshare Core\",
      \"links\": [
        {
          \"initiatorServiceId\": \"cutxwyn8jwn\",
          \"remoteServiceId\": \"f4me4ykpyp2\",
          \"remoteEndpointId\": \"b01h34rzgkp\"
        },
        {
          \"initiatorServiceId\": \"cutxwyn8jwn\",
          \"remoteServiceId\": \"qz6t09437qp\",
          \"remoteEndpointId\": \"463n06mptdv\"
        },
        {
          \"initiatorServiceId\": \"47zy5s1of30\",
          \"remoteServiceId\": \"cutxwyn8jwn\",
          \"remoteEndpointId\": \"0t66psr45ca\"
        }
      ]
    },
    {
      \"serviceId\": \"47zy5s1of30\",
      \"serviceName\": \"ACME Rideshare Partner JP\",
      \"links\": [
        {
          \"initiatorServiceId\": \"47zy5s1of30\",
          \"remoteServiceId\": \"f4me4ykpyp2\",
          \"remoteEndpointId\": \"b01h34rzgkp\"
        },
        {
          \"initiatorServiceId\": \"47zy5s1of30\",
          \"remoteServiceId\": \"qz6t09437qp\",
          \"remoteEndpointId\": \"463n06mptdv\"
        },
        {
          \"initiatorServiceId\": \"47zy5s1of30\",
          \"remoteServiceId\": \"cutxwyn8jwn\",
          \"remoteEndpointId\": \"0t66psr45ca\"
        }
      ]
    }
  ]
}'
";
        echo "$curlCommand";
        echo "";
        $shellOutput = shell_exec($curlCommand);
        echo "\n\n\n".$shellOutput."\n\n\n";

    }



    //TODO 
    public function getEventMeshList():array
    {

        $curlCommand =
            $this->generateFirstPart("GET","/api/v0/services?userOnly=true&page-size=100&page-number=0");

        //echo "$curlCommand";
        //echo "";
        $shellOutput = shell_exec($curlCommand);
        //echo "\n\n\n".$shellOutput."\n\n\n";

        return json_decode($shellOutput, true)['data'];
    }

    /**
     * get the service associated to this configuration (so that sevices deletion, mesh is only done to the one listed in this configuration and nothing else)
     */
    //TODO
    public function getMyEventMeshList():array
    {
        $serviceList = $this->getEventMeshList();

        $myServiceNames = [];

        foreach ($this->servicesConfig->services as $service)
        {
            $myServiceNames[]=$service->name;
        }

        $myServices = [];
        foreach($serviceList as $service)
        {
            if(in_array($service['name'], $myServiceNames))
                $myServices[$service['name']]=$service;
        }

        return $myServices;
    }




//TODO
    public function deleteEventMesh(string $serviceId):void
    {
        $curlCommand =
            $this->generateFirstPart("DELETE","/api/v0/services/".$serviceId);

        echo "$curlCommand";
        echo "";
        $shellOutput = shell_exec($curlCommand);
        echo "\n\n\n".$shellOutput."\n\n\n";
    }

}