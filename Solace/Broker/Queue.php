<?php

namespace Solace\Broker;
use JetBrains\PhpStorm\Pure;
use Solace\Broker\Config;

class Queue extends Config
{
    public string $queueName        ;
    public string $accessType       ;
    public int    $maxBindCount     ;
    public int    $maxMsgSpoolUsage ;
    public bool   $egressEnabled    ;
    public bool   $ingressEnabled   ;
    public string $permission       ;

    /** @noinspection PhpMissingParentConstructorInspection */
    #[Pure] public function __construct(Config $config,
                                        string $queueName        = "queue",
                                        string $accessType       = "exclusive",
                                        int    $maxBindCount     = 1000,
                                        int    $maxMsgSpoolUsage = 5000,
                                        bool   $egressEnabled    = true,
                                        bool   $ingressEnabled   = true,
                                        string $permission       = "consume")
    {
        parent::fromConfig($config);
        $this->queueName        = $queueName       ;
        $this->accessType       = $accessType      ;
        $this->maxBindCount     = $maxBindCount    ;
        $this->maxMsgSpoolUsage = $maxMsgSpoolUsage;
        $this->egressEnabled    = $egressEnabled   ;
        $this->ingressEnabled   = $ingressEnabled  ;
        $this->permission       = $permission      ;
    }


    public function createQueue():Queue
    {
        //--header 'Cookie: Session=RrE4kpRYO9gF-djljbx8-R6ieySYAZDkR9cXHDS_WZs' \
        $curlCommand =
            $this->generateFirstPart("POST","queues").
            "--data-raw '{
  \"accessType\": \"".$this->accessType."\",
  \"maxBindCount\": ".$this->maxBindCount.",
  \"maxMsgSpoolUsage\": ".$this->maxMsgSpoolUsage.",
  \"egressEnabled\": ".($this->egressEnabled?"true":"false").",
  \"ingressEnabled\": ".($this->ingressEnabled?"true":"false").",
  \"permission\": \"".$this->permission."\",
  \"queueName\": \"".$this->queueName."\"
}'
";
        $this->logAsDebug("CURL COMMAND",$curlCommand);
        $shellOutput = shell_exec($curlCommand);
        $this->logAsDebug("CURL OUTPUT",$shellOutput);

        return $this;
    }


    public function addSubscription(string|array $subscription)
    {
        //--header 'Cookie: Session=RrE4kpRYO9gF-djljbx8-R6ieySYAZDkR9cXHDS_WZs' \

        if(gettype($subscription) == "string")
        {
            $subscription = [$subscription];
        }

        foreach($subscription as $oneSubscription)
        {
            $curlCommand =
                $this->generateFirstPart("POST","queues/".$this->queueName."/subscriptions").
                "--data-raw '{
  \"subscriptionTopic\": \"".$oneSubscription."\"
}'
";
            $this->logAsDebug("CURL COMMAND",$curlCommand);
            $shellOutput = shell_exec($curlCommand);
            $this->logAsDebug("CURL OUTPUT",$shellOutput);
        }
        return $this;
    }
}