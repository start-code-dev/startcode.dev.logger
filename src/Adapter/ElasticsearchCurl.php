<?php

namespace Startcode\Logger\Adapter;

use Startcode\Logger\AdapterAbstract;

class ElasticsearchCurl extends AdapterAbstract
{

    const TIMEOUT = 1;
    const METHOD_DELETE = 'DELETE';
    const METHOD_POST =   'POST';

    private string $host;
    private string $index;
    private string $type;

    /**
     * Elasticsearch constructor.
     * @param array $hosts
     * @param string $index
     * @param string $type
     */
    public function __construct(array $hosts, string $index, string $type)
    {
        $this->index    = $index;
        $this->type     = $type;
        $this->host     = $hosts[array_rand(array_filter($hosts))];
    }

    public function deleteIndex() : void
    {
        $this->send([],  join('/', [$this->host, $this->index]), self::METHOD_DELETE);
    }

    public function deleteByQuery(array $data) : void
    {
        $this->send($data,  $this->buildUrl('_query'), self::METHOD_DELETE);
    }

    public function save(array $data) : void
    {
        $this->shouldSaveInOneCall()
            ? $this->appendData($data)
            : $this->send($data, $this->buildUrl($data['id']), self::METHOD_POST);
    }

    public function saveAppend(array $data) : void
    {
        $this->shouldSaveInOneCall()
            ? $this->appendData($data)->send($this->getData(), $this->buildUrl($data['id']), self::METHOD_POST)
            : $this->send(['doc' => $data], $this->buildUrl($data['id'], '_update'), self::METHOD_POST);
    }

    private function buildUrl($id, $update = null) : string
    {
        return implode('/', [
            $this->host,
            $this->index,
            $this->type,
            $id,
            $update
        ]);
    }

    private function send(array $data, $url, $method) : void
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}
