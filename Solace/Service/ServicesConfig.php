<?php

namespace Solace\Service;

class ServicesConfig
{
    /**
     * @var string $cloudAPIURL The API endpoint
     */
    public string $cloudAPIURL;

    /**
     * @var string $cloudAPIToken The API authentication token. See here for details on how to get one. https://docs.solace.com/Cloud/ght_api_tokens.htm#Create
     */
    public string $cloudAPIToken;

    /**
     * @var ServiceConfig[] $services An array of ServiceConfig object that represent each service configuration
     */
    public array  $services;

    /**
     * @var string $eventMeshName The EventMesh name to be created
     */
    public string $eventMeshName;

    /**
     * @var int $initialWaitForServiceCreation Time in second to wait after the initial API call to create the services
     */
    public int    $initialWaitForServiceCreation=4*60;

    /**
     * @var int $initialWaitForMeshCreation  Time in second to wait after the initial API call to create the Mesh
     */
    public int    $initialWaitForMeshCreation=30;

    /**
     * @var int $initialWaitForMeshDeletion in second to wait after the initial API call to delete the Mesh
     */
    public int    $initialWaitForMeshDeletion=20;

    /**
     * @var bool $debug if set to true, each CURL command, and its output will be printed to the console.
     */
    public bool   $debug=false;

    /**
     * @var string $objectNamePrefix Each object create at Solace Cloud level (Mesh, Services(broker)) will be created with the configured name + the value of this variable as a prefix.
     *
     * Ex: Configured Event Mesh name : "ACME Rideshare Event Mesh"
     * $objectNamePrefix="TM-";
     *
     * The created event mesh will be named  "TM-ACME Rideshare Event Mesh"
     */
    public string $objectNamePrefix;

    public function __construct(string $cloudAPIURL  ,
                                string $cloudAPIToken,
                                array  $services     ,
                                string $eventMeshName,
                                string $objectNamePrefix = ""   ,
                                bool   $debug            = false)
    {
        $this->cloudAPIURL      = $cloudAPIURL     ;
        $this->cloudAPIToken    = $cloudAPIToken;
        $this->services         = $services;
        $this->eventMeshName    = $eventMeshName;
        $this->objectNamePrefix = $objectNamePrefix;
        $this->debug            = $debug;
    }
}