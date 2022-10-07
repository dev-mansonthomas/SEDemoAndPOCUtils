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
    public int    $initialWaitForMeshDeletion = 20;

    /**
     * @var bool   $storageIsConfigurable Setting this option to true, will allow you to specify a storage size for each brokers. However, your Solace Cloud Account (not your token) must be allowed to set the broker storage size.
     *                                    To check if you can set the broker storage size, go to your solace cloud account, create a service and see if you have a field that allows you to specify the storage size.
     *                                    If you set this to true and you don't have the appropriate rights, you'll get the following error : "Organization seall is not permitted to configure message storage size"
     */
    public bool   $storageIsConfigurable      = false;

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

    public function __construct(string $cloudAPIURL                  ,
                                string $cloudAPIToken                ,
                                array  $services                     ,
                                string $eventMeshName                ,
                                string $objectNamePrefix      = ""   ,
                                bool   $storageIsConfigurable = false,
                                bool   $debug                 = false)
    {
        $this->cloudAPIURL           = $cloudAPIURL             ;
        $this->cloudAPIToken         = $cloudAPIToken           ;
        $this->services              = $services                ;
        $this->eventMeshName         = $eventMeshName           ;
        $this->objectNamePrefix      = $objectNamePrefix        ;
        $this->storageIsConfigurable = $storageIsConfigurable   ;
        $this->debug                 = $debug                   ;
    }
}