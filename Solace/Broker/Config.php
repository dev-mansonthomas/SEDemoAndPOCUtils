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
    public bool   $debug;

    public function __construct(string $adminURL                    ,
                                string $adminUsername               ,
                                string $adminPassword               ,
                                string $msgVpnName                  ,
                                string $basePath = "/SEMP/v2/config",
                                bool   $debug    = false            )
    {
        $this->adminURL      = $adminURL     ;
        $this->adminUsername = $adminUsername;
        $this->adminPassword = $adminPassword;
        $this->msgVpnName    = $msgVpnName   ;
        $this->basePath      = $basePath     ;
        $this->debug         = $debug        ;
    }

    public function fromConfig(Config $config):void
    {
        $this->adminURL      = $config->adminURL     ;
        $this->adminUsername = $config->adminUsername;
        $this->adminPassword = $config->adminPassword;
        $this->msgVpnName    = $config->msgVpnName   ;
        $this->basePath      = $config->basePath     ;
        $this->debug         = $config->debug        ;
    }

    function generateBasicAuth():string
    {
      return base64_encode($this->adminUsername.":".$this->adminPassword);
    }

    #[Pure] function generateFirstPart($method, $path):string
    {
        return "
curl -s --location --request ".$method." '".$this->adminURL.$this->basePath."/msgVpns/".$this->msgVpnName."/".$path."' \\
--header 'Content-Type: application/json'                       \\
--header 'Accept: application/json'                             \\
--header 'Authorization: Basic ".$this->generateBasicAuth()."'  \\
";
    }

    function logAsDebug($subject, $content):void
    {
        if($this->debug)
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
}