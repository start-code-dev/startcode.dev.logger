<?php

namespace Startcode\Logger\Adapter;

use Startcode\Logger\AdapterAbstract;
use Elasticsearch\ClientBuilder;
use Elasticsearch\Client;

class Elasticsearch extends AdapterAbstract
{

    /**
     * Number of retries if first request fails
     */
    const RETRIES = 0;

    /**
     * Number of seconds for client-side, curl timeouts
     */
    const TIMEOUT = 1;

    private Client $client;

    private string $index;

    private string $type;

    public function __construct(array $hosts, string $index, string $type)
    {
        $this->index = $index;
        $this->type  = $type;

        $this->client = ClientBuilder::create()
            ->setHosts($hosts)
            ->setRetries(self::RETRIES)
            ->build();

    }

    public function deleteIndex()
    {
        return $this->client->indices()->delete(['index' => $this->index]);
    }

    public function save(array $data) : void
    {
        try {
            $this->shouldSaveInOneCall()
                ? $this->appendData($data)
                : $this->doIndexing($data);
        } catch (\Exception $exception) {
            error_log ($exception->getMessage(), 0);
        }
    }

    public function saveAppend(array $data) : void
    {
        try {
            $this->shouldSaveInOneCall()
                ? $this->appendData($data)->doIndexing($this->getData())
                : $this->doUpdate($data);
        } catch (\Exception $exception) {
            error_log ($exception->getMessage(), 0);
        }
    }

    private function doIndexing(array $data) : void
    {
        $this->client->index([
            'index' => $this->index,
            'type'  => $this->type,
            'id'    => $data['id'],
            'body'  => $data,
            'client' => $this->getClientOptions(),
        ]);
    }

    private function doUpdate(array $data) : void
    {
        $this->client->update([
            'index' => $this->index,
            'type'  => $this->type,
            'id'    => $data['id'],
            'body'  => [
                'doc' => $data,
            ],
            'client' => $this->getClientOptions(),
        ]);
    }

    private function getClientOptions() : array
    {
        $clientOptions = [
            'timeout'         => self::TIMEOUT,
            'connect_timeout' => self::TIMEOUT,
        ];
        if ($this->shouldBeLazy() && $this->shouldSaveInOneCall()) {
            $clientOptions['future'] = 'lazy';
        }
        return $clientOptions;
    }
}
