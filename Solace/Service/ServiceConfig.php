<?php
namespace Solace\Service;

class ServiceConfig
{
    public string $name                 ;
    public string $dataCenterId         ;
    public string $type                 ;
    public string $class                ;
    public string $brokerVersion        ;
    public int    $brokerStorageSize    ;
    public bool   $storageIsConfigurable;


    public function __construct(string $name                                                  ,
                                string $dataCenterId                                          ,
                                int    $brokerStorageSize       = 25                          ,
                                bool   $storageIsConfigurable   = false                       ,
                                string $class                   = "enterprise-250-standalone" , // enterprise-250-standalone or enterprise-250-highavailability
                                string $type                    = "enterprise-standalone"     , // enterprise-standalone     or   enterprise
                                string $brokerVersion           = "10.4"                      )
    {
        $this->name                  = $name                ;
        $this->dataCenterId          = $dataCenterId        ;
        $this->brokerStorageSize     = $brokerStorageSize   ;
        $this->storageIsConfigurable = $storageIsConfigurable;
        $this->class                 = $class               ;
        $this->type                  = $type                ;
        $this->brokerVersion         = $brokerVersion       ;
    }
}