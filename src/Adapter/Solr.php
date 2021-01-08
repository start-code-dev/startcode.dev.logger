<?php

namespace Startcode\Logger\Adapter;

use Startcode\Logger\AdapterAbstract;

class Solr extends AdapterAbstract
{

    const TIMEOUT = 1;

    const IDENTIFIER_KEY = 'id';

    private $collection;

    private $host;

    private $url;

    public function __construct($host, $collection)
    {
        $this->host       = $host;
        $this->collection = $collection;
    }

    public function save(array $data) : void
    {
        $this->shouldSaveInOneCall()
            ? $this->appendData($data)
            : $this->send([$data]);
    }

    public function saveAppend(array $data) : void
    {
        if ($this->shouldSaveInOneCall()) {
            $this->appendData($data)->send([$this->getData()]);
        } else {
            array_walk($data, function(&$value, $key){
                if ($key != self::IDENTIFIER_KEY) {
                    $value = ['add' => $value];
                }
            });
            $this->send([$data]);
        }
    }

    private function buildUrl() : string
    {
        if ($this->url === null) {
            $this->url = join('', [
                $this->host,
                '/solr/',
                $this->collection,
                '/update/',
            ]);
        }
        return $this->url;
    }

    private function send(array $data) : void
    {
        $ch = curl_init($this->buildUrl());
        curl_setopt_array($ch, [
            CURLOPT_POST           => 1,
            CURLOPT_POSTFIELDS     => json_encode($data),
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_URL            => $this->buildUrl(),
        ]);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);
    }
}
