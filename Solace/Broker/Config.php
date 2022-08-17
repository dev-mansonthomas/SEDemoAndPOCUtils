<?php

namespace Solace\Broker;

use JetBrains\PhpStorm\Pure;

class Config
{
    public string $adminURL;
    public string $adminUsername;
    public string $adminPassword;
    public string $msgVpnName;
    public string $basePath;

    public function __construct(string $adminURL,
                                string $adminUsername,
                                string $adminPassword,
                                string $msgVpnName,
                                string $basePath="/SEMP/v2/config")
    {
        $this->adminURL     =$adminURL     ;
        $this->adminUsername=$adminUsername;
        $this->adminPassword=$adminPassword;
        $this->msgVpnName   =$msgVpnName   ;
        $this->basePath     =$basePath     ;
    }

    public function fromConfig(Config $config):void
    {
        $this->adminURL     =$config->adminURL     ;
        $this->adminUsername=$config->adminUsername;
        $this->adminPassword=$config->adminPassword;
        $this->msgVpnName   =$config->msgVpnName   ;
        $this->basePath     =$config->basePath     ;
    }

    function generateBasicAuth():string
    {
      return base64_encode($this->adminUsername.":".$this->adminPassword);
    }

    #[Pure] function generateFirstPart($method, $path):string
    {
        return "
curl --location --request ".$method." '".$this->adminURL.$this->basePath."/msgVpns/".$this->msgVpnName."/".$path."' \\
--header 'Content-Type: application/json'                       \\
--header 'Accept: application/json'                             \\
--header 'Authorization: Basic ".$this->generateBasicAuth()."'  \\
";
    }
}