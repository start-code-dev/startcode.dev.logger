<?php

namespace Startcode\Logger;

use Startcode\Logger\Data\{LoggerAbstract, RuntimeLog};

class Logger
{

    const DEFAULT_LINE = 2;

    private AdapterInterface $adapter;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    public function log(LoggerAbstract $data) : void
    {
        $this->adapter->save($data->getRawData());
    }

    public function logAppend(LoggerAbstract $data) : void
    {
        $this->adapter->saveAppend($data->getRawData());
    }

    public function runtimeLog($var, $tag = false, $index = self::DEFAULT_LINE) : void
    {
        $this->log(new RuntimeLog($var, $tag, $index));
    }
}
