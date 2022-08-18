<?php

namespace Solace\Service;

class ServicesConfig
{
    public string $cloudAPIURL;
    public string $cloudAPIToken;
    public array  $services;
    public string $eventMeshName;
    /**
     * in seconds
     */
    public int    $initialWaitForServiceCreation=4*60;

    public function __construct(string $cloudAPIURL  ,
                                string $cloudAPIToken,
                                array  $services     ,
                                string $eventMeshName)
    {
        $this->cloudAPIURL      = $cloudAPIURL     ;
        $this->cloudAPIToken    = $cloudAPIToken;
        $this->services         = $services;
        $this->eventMeshName    = $eventMeshName;
    }
}