<?php
namespace Solace\Mesh;

use Solace\Broker\Queue;
use Solace\Service\Service;
use Solace\Service\ServiceConfig;
use Solace\Service\ServicesConfig;

class Mesh
{
    private ServicesConfig $servicesConfig;
    private Service        $service;

    private string $MESH_CREATION_COMPLETED="ready";
    private string $MESH_CREATION_IN_PROGRESS="creating";
    private string $MESH_CREATION_FAILED="failed";

    public function __construct(ServicesConfig $servicesConfig, Service $service)
    {
        $this->servicesConfig = $servicesConfig;
        $this->service        = $service;
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


    public function createMesh():void
    {
        $myServices = $this->service->getMyServiceList();

        $serviceCount = count($this->servicesConfig->services);
        $functionName = "create".$serviceCount."ServicesEventMeshPayload";
        $payload = $this->$functionName($myServices);

        $curlCommand =
            $this->generateFirstPart("POST","/api/v0/meshManager/eventMeshes").
            "--data-raw '$payload'
";
        $this->logAsDebug("CURL COMMAND",$curlCommand);
        $shellOutput = shell_exec($curlCommand);
        $this->logAsDebug("CURL OUTPUT",$shellOutput);


    }


    /*
    public function createMesh():void
    {

        $myServices = $this->service->getMyServiceList();
        $serviceList=[];
        $serviceConnectionEndpointIds=[];
        foreach ($myServices as $onService)
        {
            $serviceList[]=['serviceId'=>$onService['serviceId'], 'serviceName'=>$onService['name']];
            $serviceConnectionEndpointIds[$onService['serviceId']] = $onService['serviceConnectionEndpoints'][0]['serviceConnectionEndpointId'];
        }

        $serviceListString = "";
        $serviceLinksString = "";
        foreach ($serviceList as $oneService)
        {
            $currentService = $oneService['serviceId'];
            $serviceListString .= "\n    \"".$currentService."\",";
            /** no need to add objectName prefix here, has serviceName are retrieved from Solace Cloud API* /
            $serviceLinksString.="
    {
      \"serviceId\": \"".$oneService['serviceId']."\",
      \"serviceName\": \"".$oneService['serviceName']."\",
      \"links\": [";
            foreach ($serviceList as $otherService)
            {
                if($currentService != $otherService['serviceId'])
                {
                    $serviceLinksString.="
        {
          \"initiatorServiceId\": \"".$otherService['serviceId']."\",
          \"remoteServiceId\": \"".$currentService."\",
          \"remoteEndpointId\": \"".$serviceConnectionEndpointIds[$currentService]."\"
        },";
                }
            }
            $serviceLinksString = substr( $serviceLinksString, 0, strlen( $serviceLinksString)-1);
            $serviceLinksString.="
      ]
    },";

        }
        //remove last ","
        $serviceLinksString = substr($serviceLinksString, 0, strlen($serviceLinksString)-1);
        $serviceListString  = substr($serviceListString , 0, strlen($serviceListString )-1);




        //--header 'Cookie: Session=RrE4kpRYO9gF-djljbx8-R6ieySYAZDkR9cXHDS_WZs' \
        $curlCommand =
            $this->generateFirstPart("POST","/api/v0/meshManager/eventMeshes").
            "--data-raw '{
  \"eventMeshName\": \"".$this->servicesConfig->objectNamePrefix.$this->servicesConfig->eventMeshName."\",
  \"services\": [$serviceListString
  ],
  \"links\": [$serviceLinksString
  ]
}'
";
        $this->logAsDebug("CURL COMMAND",$curlCommand);
        $shellOutput = shell_exec($curlCommand);
        $this->logAsDebug("CURL OUTPUT",$shellOutput);

    }
*/

    
    public function getEventMeshList():array
    {
        $curlCommand =
            $this->generateFirstPart("GET","/api/v0/meshManager/eventMeshes");

        $this->logAsDebug("CURL COMMAND",$curlCommand);
        $shellOutput = shell_exec($curlCommand);
        $this->logAsDebug("CURL OUTPUT",$shellOutput);

        return json_decode($shellOutput, true)['data'];
    }

    /**
     * get the service associated to this configuration (so that Mesh deletion, mesh is only done to the one listed in this configuration and nothing else)
     */
    public function getMyEventMesh():?array
    {
        $meshList = $this->getEventMeshList();


        foreach($meshList as $mesh)
        {
            if($mesh['name']==$this->servicesConfig->objectNamePrefix.$this->servicesConfig->eventMeshName)
                return $mesh;
        }
        echo "
######################################################################################
#                                                                                    
#        MESH NOT FOUND ('".$this->servicesConfig->objectNamePrefix.$this->servicesConfig->eventMeshName."')                 
#                                                                                    
######################################################################################
";
        return null;
    }



    public function deleteMyEventMesh():void
    {
        $mesh = $this->getMyEventMesh();
        if($mesh !== null)
        {
            $this->deleteEventMesh($mesh['id']);
        }
        else
        {
            echo "The Event Mesh with name '".$this->servicesConfig->objectNamePrefix.$this->servicesConfig->eventMeshName."' has not been found\n\n";
            exit(1);
        }

    }

    public function deleteEventMesh(string $meshId):void
    {
        $curlCommand =
            $this->generateFirstPart("DELETE","/api/v0/meshManager/eventMeshes/".$meshId);

        $this->logAsDebug("CURL COMMAND",$curlCommand);
        $shellOutput = shell_exec($curlCommand);
        $this->logAsDebug("CURL OUTPUT",$shellOutput);
    }



    public function waitForMeshCreation():void
    {
        echo "
######################################################################################
#                                                                                    #
#         WAITING FOR AN INITIAL ".$this->servicesConfig->initialWaitForMeshCreation." SECONDS FOR MESH CREATION TO COMPLETE            #
#                                                                                    #
######################################################################################
";
        sleep($this->servicesConfig->initialWaitForMeshCreation);
        $numberOfRetry=0;
        do
        {
            $myMesh = $this->getMyEventMesh();
            if($myMesh == null)
            {
                return;
            }

            if($myMesh['state']==$this->MESH_CREATION_COMPLETED)
            {
                return;
            }
            else if($myMesh['state']==$this->MESH_CREATION_FAILED)
            {
                echo "
######################################################################################
#                                                                                    #
#          Mesh creation >>>>FAILED<<<<                                              #
#                                                                                    #
######################################################################################
";
                print_r($myMesh);
                exit(1);
            }

            echo "
######################################################################################
#                                                                                    #
#    Mesh creation is still in progress, sleeping from 10 more seconds               #
#                                                                                    #
######################################################################################
";
            $numberOfRetry++;
            sleep(10);

        }
        while($numberOfRetry<12);
    }



    public function waitForMeshDeletion():void
    {
        echo "
######################################################################################
#                                                                                    #
#         WAITING FOR AN INITIAL ".$this->servicesConfig->initialWaitForMeshDeletion." SECONDS FOR MESH DELETION TO COMPLETE            #
#                                                                                    #
######################################################################################
";
        sleep($this->servicesConfig->initialWaitForMeshDeletion);
        $numberOfRetry=0;
        do
        {
            $myMesh = $this->getMyEventMesh();
            if($myMesh == null)
            {

                echo "
######################################################################################
#                                                                                    #
#          MESH DELETION COMPLETED                                                   #
#                                                                                    #
######################################################################################
";
                return;
            }


            echo "
######################################################################################
#                                                                                    #
#    Mesh deletion is still in progress, sleeping from 10 more seconds               #
#                                                                                    #
######################################################################################
";
            $numberOfRetry++;
            sleep(10);

        }
        while($numberOfRetry<12);
    }
    
    /**
     * @param ServiceConfig[] $myServices  List of services, fetched by $service->getMyServices(), in the case of 2 services
     */
    private function create2ServicesEventMeshPayload(array $myServices)
    {
        $MeshName = $this->servicesConfig->objectNamePrefix.$this->servicesConfig->eventMeshName;

        $myServicesNames = array_keys($myServices);
        $countServices   = count($myServices);

        $variableServiceName=["A","B","C","D","E","F"];
        
        for($i=0;$i<$countServices;$i++)
        {
            $serviceIdVariable        = $variableServiceName[$i];
            $serviceNameVariable      = $variableServiceName[$i]."Name";
            $serviceRemoteNameVariable= "Remote".$variableServiceName[$i];

            $$serviceIdVariable         = $myServices[$myServicesNames[$i]]['serviceId'];
            $$serviceNameVariable       = $myServices[$myServicesNames[$i]]['name'     ];
            $$serviceRemoteNameVariable = $myServices[$myServicesNames[$i]]['serviceConnectionEndpoints'][0]['serviceConnectionEndpointId'];
        }

        return "
{
  \"eventMeshName\": \"$MeshName\",
  \"services\": [
    \"$A\",
    \"$B\"
  ],
  \"links\": [
    {
      \"serviceId\": \"$A\",
      \"serviceName\": \"$AName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$B\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        }
      ]
    },
    {
      \"serviceId\": \"$B\",
      \"serviceName\": \"$BName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$B\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        }
      ]
    }
  ]
}
";
        
    }
    /**
     * @param ServiceConfig[] $myServices  List of services, fetched by $service->getMyServices(), in the case of 3 services
     */
    private function create3ServicesEventMeshPayload(array $myServices)
    {
        $MeshName = $this->servicesConfig->objectNamePrefix.$this->servicesConfig->eventMeshName;

        $myServicesNames = array_keys($myServices);
        $countServices   = count($myServices);

        $variableServiceName=["A","B","C","D","E","F"];

        for($i=0;$i<$countServices;$i++)
        {
            $serviceIdVariable        = $variableServiceName[$i];
            $serviceNameVariable      = $variableServiceName[$i]."Name";
            $serviceRemoteNameVariable= "Remote".$variableServiceName[$i];

            $$serviceIdVariable         = $myServices[$myServicesNames[$i]]['serviceId'];
            $$serviceNameVariable       = $myServices[$myServicesNames[$i]]['name'     ];
            $$serviceRemoteNameVariable = $myServices[$myServicesNames[$i]]['serviceConnectionEndpoints'][0]['serviceConnectionEndpointId'];
        }

        return "
{
  \"eventMeshName\": \"$MeshName\",
  \"services\": [
    \"$A\",
    \"$B\",
    \"$C\"
  ],
  \"links\": [
    {
      \"serviceId\": \"$A\",
      \"serviceName\": \"$AName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$B\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        }
      ]
    },
    {
      \"serviceId\": \"$B\",
      \"serviceName\": \"$BName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$B\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        }
      ]
    },
    {
      \"serviceId\": \"$C\",
      \"serviceName\": \"$CName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        }
      ]
    }
  ]
}
";

    }
    /**
     * @param ServiceConfig[] $myServices  List of services, fetched by $service->getMyServices(), in the case of 4 services
     */
    private function create4ServicesEventMeshPayload(array $myServices)
    {
        $MeshName = $this->servicesConfig->objectNamePrefix.$this->servicesConfig->eventMeshName;

        $myServicesNames = array_keys($myServices);
        $countServices   = count($myServices);

        $variableServiceName=["A","B","C","D","E","F"];

        for($i=0;$i<$countServices;$i++)
        {
            $serviceIdVariable        = $variableServiceName[$i];
            $serviceNameVariable      = $variableServiceName[$i]."Name";
            $serviceRemoteNameVariable= "Remote".$variableServiceName[$i];

            $$serviceIdVariable         = $myServices[$myServicesNames[$i]]['serviceId'];
            $$serviceNameVariable       = $myServices[$myServicesNames[$i]]['name'     ];
            $$serviceRemoteNameVariable = $myServices[$myServicesNames[$i]]['serviceConnectionEndpoints'][0]['serviceConnectionEndpointId'];
        }

        return "
{
  \"eventMeshName\": \"$MeshName\",
  \"services\": [
    \"$A\",
    \"$B\",
    \"$C\",
    \"$D\"
  ],
  \"links\": [
    {
      \"serviceId\": \"$A\",
      \"serviceName\": \"$AName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$B\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"RemoteA\"
        }
      ]
    },
    {
      \"serviceId\": \"$B\",
      \"serviceName\": \"$BName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$B\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"RemoteB\"
        }
      ]
    },
    {
      \"serviceId\": \"$C\",
      \"serviceName\": \"$CName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$C\",
          \"remoteEndpointId\": \"RemoteC\"
        }
      ]
    },
    {
      \"serviceId\": \"$D\",
      \"serviceName\": \"$DName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$C\",
          \"remoteEndpointId\": \"RemoteC\"
        }
      ]
    }
  ]
}
";

    }
    /**
     * @param ServiceConfig[] $myServices  List of services, fetched by $service->getMyServices(), in the case of 5 services
     */
    private function create5ServicesEventMeshPayload(array $myServices)
    {
        $MeshName = $this->servicesConfig->objectNamePrefix.$this->servicesConfig->eventMeshName;

        $myServicesNames = array_keys($myServices);
        $countServices   = count($myServices);

        $variableServiceName=["A","B","C","D","E","F"];

        for($i=0;$i<$countServices;$i++)
        {
            $serviceIdVariable        = $variableServiceName[$i];
            $serviceNameVariable      = $variableServiceName[$i]."Name";
            $serviceRemoteNameVariable= "Remote".$variableServiceName[$i];

            $$serviceIdVariable         = $myServices[$myServicesNames[$i]]['serviceId'];
            $$serviceNameVariable       = $myServices[$myServicesNames[$i]]['name'     ];
            $$serviceRemoteNameVariable = $myServices[$myServicesNames[$i]]['serviceConnectionEndpoints'][0]['serviceConnectionEndpointId'];
        }

        return "
{
  \"eventMeshName\": \"$MeshName\",
  \"services\": [
    \"$A\",
    \"$B\",
    \"$C\",
    \"$D\",
    \"$E\"
  ],
  \"links\": [
    {
      \"serviceId\": \"$A\",
      \"serviceName\": \"$AName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$B\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        }
      ]
    },
    {
      \"serviceId\": \"$B\",
      \"serviceName\": \"$BName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$B\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        }
      ]
    },
    {
      \"serviceId\": \"$C\",
      \"serviceName\": \"$CName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$C\",
          \"remoteEndpointId\": \"$RemoteC\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$C\",
          \"remoteEndpointId\": \"$RemoteC\"
        }
      ]
    },
    {
      \"serviceId\": \"$D\",
      \"serviceName\": \"$DName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$C\",
          \"remoteEndpointId\": \"$RemoteC\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$D\",
          \"remoteEndpointId\": \"$RemoteD\"
        }
      ]
    },
    {
      \"serviceId\": \"$E\",
      \"serviceName\": \"$EName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$C\",
          \"remoteEndpointId\": \"$RemoteC\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$D\",
          \"remoteEndpointId\": \"$RemoteD\"
        }
      ]
    }
  ]
}
";

    }
    /**
     * @param ServiceConfig[] $myServices  List of services, fetched by $service->getMyServices(), in the case of 6 services
     */
    private function create6ServicesEventMeshPayload(array $myServices)
    {
        $MeshName = $this->servicesConfig->objectNamePrefix.$this->servicesConfig->eventMeshName;

        $myServicesNames = array_keys($myServices);
        $countServices   = count($myServices);

        $variableServiceName=["A","B","C","D","E","F"];

        for($i=0;$i<$countServices;$i++)
        {
            $serviceIdVariable        = $variableServiceName[$i];
            $serviceNameVariable      = $variableServiceName[$i]."Name";
            $serviceRemoteNameVariable= "Remote".$variableServiceName[$i];

            $$serviceIdVariable         = $myServices[$myServicesNames[$i]]['serviceId'];
            $$serviceNameVariable       = $myServices[$myServicesNames[$i]]['name'     ];
            $$serviceRemoteNameVariable = $myServices[$myServicesNames[$i]]['serviceConnectionEndpoints'][0]['serviceConnectionEndpointId'];
        }

        return "
{
  \"eventMeshName\": \"$MeshName\",
  \"services\": [
    \"$A\",
    \"$B\",
    \"$C\",
    \"$D\",
    \"$E\",
    \"$F\"
  ],
  \"links\": [
    {
      \"serviceId\": \"$A\",
      \"serviceName\": \"$AName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$B\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$F\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        }
      ]
    },
    {
      \"serviceId\": \"$B\",
      \"serviceName\": \"$BName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$B\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$F\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        }
      ]
    },
    {
      \"serviceId\": \"$C\",
      \"serviceName\": \"$CName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$C\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$C\",
          \"remoteEndpointId\": \"$RemoteC\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$C\",
          \"remoteEndpointId\": \"$RemoteC\"
        },
        {
          \"initiatorServiceId\": \"$F\",
          \"remoteServiceId\": \"$C\",
          \"remoteEndpointId\": \"$RemoteC\"
        }
      ]
    },
    {
      \"serviceId\": \"$D\",
      \"serviceName\": \"$DName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$D\",
          \"remoteServiceId\": \"$C\",
          \"remoteEndpointId\": \"$RemoteC\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$D\",
          \"remoteEndpointId\": \"$RemoteD\"
        },
        {
          \"initiatorServiceId\": \"$F\",
          \"remoteServiceId\": \"$D\",
          \"remoteEndpointId\": \"$RemoteD\"
        }
      ]
    },
    {
      \"serviceId\": \"$E\",
      \"serviceName\": \"$EName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$C\",
          \"remoteEndpointId\": \"$RemoteC\"
        },
        {
          \"initiatorServiceId\": \"$E\",
          \"remoteServiceId\": \"$D\",
          \"remoteEndpointId\": \"$RemoteD\"
        },
        {
          \"initiatorServiceId\": \"$F\",
          \"remoteServiceId\": \"$E\",
          \"remoteEndpointId\": \"$RemoteE\"
        }
      ]
    },
    {
      \"serviceId\": \"$F\",
      \"serviceName\": \"$FName\",
      \"links\": [
        {
          \"initiatorServiceId\": \"$F\",
          \"remoteServiceId\": \"$A\",
          \"remoteEndpointId\": \"$RemoteA\"
        },
        {
          \"initiatorServiceId\": \"$F\",
          \"remoteServiceId\": \"$B\",
          \"remoteEndpointId\": \"$RemoteB\"
        },
        {
          \"initiatorServiceId\": \"$F\",
          \"remoteServiceId\": \"$C\",
          \"remoteEndpointId\": \"$RemoteC\"
        },
        {
          \"initiatorServiceId\": \"$F\",
          \"remoteServiceId\": \"$D\",
          \"remoteEndpointId\": \"$RemoteD\"
        },
        {
          \"initiatorServiceId\": \"$F\",
          \"remoteServiceId\": \"$E\",
          \"remoteEndpointId\": \"$RemoteE\"
        }
      ]
    }
  ]
}
";

    }
}