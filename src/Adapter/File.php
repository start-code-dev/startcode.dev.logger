<?php

namespace Startcode\Logger\Adapter;

use Startcode\Logger\AdapterAbstract;

class File extends AdapterAbstract
{

    private string $filename;

    public function __construct(string $filename)
    {
        $this->filename = $filename;
    }

    public function save(array $data) : void
    {
        $this->shouldSaveInOneCall()
            ? $this->appendData($data)
            : $this->errorLog("\n" . $this->format($data) . "\n");
    }

    public function saveAppend(array $data) : void
    {
        $this->shouldSaveInOneCall()
            ? $this->appendData($data)->errorLog("\n" . $this->format($this->getData()) . "\n")
            : $this->errorLog($this->format($data) . "\n");
    }

    private function errorLog($formattedData) : void
    {
        error_log($formattedData, 3, $this->filename);
    }

    private function format(array $rawData) : string
    {
        array_walk($rawData, function(&$value, $key) {
            $value = str_pad($key . ": ", 15) . $value;
        });
        return implode("\n", $rawData);
    }

}
