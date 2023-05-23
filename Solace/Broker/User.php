<?php

namespace Solace\Broker;
use JetBrains\PhpStorm\Pure;
use Solace\Broker\Config;

class User extends Config
{
    public string $clientUsername   ;
    public bool   $enabled          ;
    public string $password         ;
    
    /** @noinspection PhpMissingParentConstructorInspection */
    #[Pure] public function __construct(Config $config,
                                        string $clientUsername  = "default",
                                        bool   $enabled         = true,
                                        string $password        = "DEFAULT@!"
                                        )
    {
        parent::fromConfig($config);
        $this->clientUsername   = $clientUsername   ;
        $this->enabled          = $enabled          ;
        $this->password         = $password         ;
    }


    public function updateUser():User
    {
        $curlCommand =
            $this->generateFirstPart("PUT","clientUsernames/".$this->clientUsername).
            "--data-raw '{
  \"enabled\": ".($this->enabled?"true":"false").",
  \"password\": \"".$this->password."\"
}'
";
        $this->logAsDebug("CURL COMMAND",$curlCommand);
        $shellOutput = shell_exec($curlCommand);
        $this->logAsDebug("CURL OUTPUT",$shellOutput);

        return $this;
    }


}