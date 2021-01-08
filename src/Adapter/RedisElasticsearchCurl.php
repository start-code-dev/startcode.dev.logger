<?php

namespace Startcode\Logger\Adapter;

use Startcode\ValueObject\Uuid;

class RedisElasticsearchCurl
{
    const TIMEOUT = 1;
    const METHOD_POST =   'POST';
    const BULK = '_bulk';
    const HTTP_CODE_200 = 200;

    /**
     * @var array
     */
    private $hosts;

    /**
     * @var array
     */
    private $versions;

    /**
     * @var string
     */
    private $index;

    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $counts;

    public function __construct(array $hosts, array $versions=[])
    {
        $this->hosts = $this->buildHosts($hosts);
        $this->versions = array_values($versions);
    }

    public function getCountInfo() : string
    {
        $countInfo = '';
        if (!empty($this->counts)) {
            foreach ($this->counts as $host => $count ) {
                $countInfo .= sprintf("[log] |- ES Cluster: %s, count: %s, exec_time: %s ms\n", $host, $count['count'], number_format($count['exec_time']));
            }
        } else {
            foreach ($this->hosts as $host) {
                $countInfo .= sprintf("[log] |- ES Cluster: %s, count: 0\n", $host);
            }
        }

        return $countInfo;
    }

    public function sendAll(array $data) : void
    {
        foreach ($this->hosts as $hostId => $host) {
            $this->send(
                $this->buildBulkData($data, $hostId),
                $this->buildBulkUrl($host),
                self::METHOD_POST
            );
        }
    }

    private function buildBulkUrl($host) : string
    {
        return implode('/', [
            $host,
            self::BULK
        ]);
    }

    private function buildHosts(array $hosts) : array
    {
        $formattedHosts = [];
        foreach ($hosts as $host) {
            if (!empty(array_filter($host))) {
                $formattedHosts[] = $host[array_rand(array_filter($host))];
            }
        }
        return $formattedHosts;
    }

    private function send($data, $url, $method) : void
    {
        $curlPostFieldsData = $data;
        if(is_array($data)){
            $curlPostFieldsData = json_encode($data);
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_POSTFIELDS     => $curlPostFieldsData,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_TIMEOUT        => self::TIMEOUT,
            CURLOPT_URL            => $url,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
        ]);
        $start = microtime(true);
        $response = curl_exec($ch);
        $duration = microtime(true) - $start;

        $data = json_decode($response,true);
        $host = substr($url, 0, strpos($url, '/'));
        $this->counts[$host] = [
            'count' => isset($data['items']) ? count($data['items']) : 0,
            'exec_time' => ceil($duration * 1000),
        ];
        curl_close($ch);
    }

    private function setIndex($index) : self
    {
        $this->index = $index;
        return $this;
    }

    private function setType($type) : self
    {
        $this->type = $type;
        return $this;
    }

    public function isElasticsearchAvailable() : bool
    {
        $host  = $this->hosts[array_rand(array_filter($this->hosts))];
        $ch = curl_init($host);

        curl_setopt_array($ch, [
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_RETURNTRANSFER => true,
        ]);

        curl_exec($ch);

        $httpcode = (int) json_decode(curl_getinfo($ch, CURLINFO_HTTP_CODE));

        curl_close($ch);

        return $httpcode === self::HTTP_CODE_200;
    }

    private function buildBulkData(array $data, $hostId) : string
    {
        // es6 version has some breaking changes
        $esVersion6 = array_key_exists($hostId, $this->versions) && $this->versions[$hostId] === 'es6';

        $bulkData = [];
        foreach ($data as $log) {
            $this->setIndex($log['_index']);
            $this->setType($log['_type']);
            unset($log['_index'], $log['_type']);

            $bulkData[] = json_encode([
                'index' => [
                    '_index' => $this->index,
                    '_type'  => $esVersion6 ? '_doc': $this->type,
                    '_id'    => isset($log['id']) ? $log['id'] : (string) Uuid::generate()
                ]
            ]);

            $bulkData[] = $esVersion6
                ? json_encode(['index_type' => $this->type] + $log)
                : json_encode($log);
        }
        return implode(PHP_EOL, $bulkData) . PHP_EOL;
    }
}
