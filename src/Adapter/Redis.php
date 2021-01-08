<?php

namespace Startcode\Logger\Adapter;

use Startcode\Logger\AdapterAbstract;
use Startcode\ValueObject\{IntegerNumber, StringLiteral};

class Redis extends AdapterAbstract
{


    private \Redis $client;

    private StringLiteral $key;

    public function __construct(\Redis $client, StringLiteral $key)
    {
        $this->client   = $client;
        $this->key      = $key;
    }

    public function fetchAndClear(IntegerNumber $batchsize) : array
    {
        $data = $this->client->lRange((string) $this->key, 0, $batchsize->getValue());
        $this->client->lTrim((string) $this->key, $batchsize->getValue() + 1, -1);

        return $data;
    }

    public function getKey() : StringLiteral
    {
        return $this->key;
    }

    public function save(array $data) : void
    {
        try {
            $this->shouldSaveInOneCall()
                ? $this->appendData($data)
                : $this->appendData($data)->doRPush(array_merge($this->getData(), $data));
        } catch (\Exception $exception) {
            error_log ($exception->getMessage(), 0);
        }

    }


    public function saveAppend(array $data) : void
    {
        try {
            $this->shouldSaveInOneCall()
                ? $this->appendData($data)->doRPush()
                : $this->appendData($data)->doRPush(array_merge($this->getData(), $data));
        } catch (\Exception $exception) {
            error_log ($exception->getMessage(), 0);
        }
    }

    private function doRPush($data = null) : void
    {
        $logData = !empty($data) ? $data : $this->getData();
        $this->client->rPush((string) $this->key, \json_encode($logData));
    }

    public function doRPushBatch(array $data) : void
    {
        $arguments = array_map(function($logData) {
            return json_encode($logData);
        }, $data);

        $this->client->rPush((string) $this->key, ...$arguments);
    }

}
