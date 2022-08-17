<?php

namespace Solace\Service;

class ServicesConfig
{
    public string $cloudAPIURL;
    public string $cloudAPIToken;
    public array  $services;

    public function __construct(string $cloudAPIURL,
                                string $cloudAPIToken,
                                array  $services)
    {
        $this->cloudAPIURL      = $cloudAPIURL     ;
        $this->cloudAPIToken    = $cloudAPIToken;
        $this->services         = $services;
    }
}