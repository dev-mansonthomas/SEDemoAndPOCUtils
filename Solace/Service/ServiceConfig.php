<?php
namespace Solace\Service;

class ServiceConfig
{
    public string $name;
    public string $dataCenterId;
    public string $type;
    public string $class;
    public string $brokerVersion;
    public function __construct(string $name                                    ,
                                string $dataCenterId                            ,
                                string $type            = "enterprise"          ,
                                string $class           = "enterprise-250-nano" ,
                                string $brokerVersion   = "10.0"                )
    {
        $this->name             = $name         ;
        $this->dataCenterId     = $dataCenterId ;
        $this->type             = $type         ;
        $this->class            = $class        ;
        $this->brokerVersion    = $brokerVersion;
    }
}