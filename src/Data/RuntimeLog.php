<?php

namespace Startcode\Logger\Data;

class RuntimeLog extends LoggerAbstract
{
    /**
     * @var mixed
     */
    private $loggedData;

    private string $tag;

    private int $index;

    /**
     * @param mixed $var
     */
    public function __construct($var, string $tag, int $index = 2)
    {
        $this->loggedData = $var;
        $this->tag        = $tag;
        $this->index      = $index;
    }

    public function getRawData() : array
    {
        $trace  = \debug_backtrace();
        $line   = $trace[$this->index]['line'] ?? null;
        $file   = $trace[$this->index]['file'] ?? null;

        return [
            'id'        => $this->getId(),
            'timestamp' => $this->getJsTimestamp(),
            'datetime'  => \date('Y-m-d H:i:s'),
            'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'cli',
            'file'      => $file,
            'line'      => $line,
            'data'      => \var_export($this->loggedData, true),
            'tag'       => $this->tag ? $this->tag : '',
            'client_ip' => $this->getClientIp(),
            'app_name'  => $this->getAppName(),
            'headers'   => \json_encode($this->getXNDParameters()),
            'uuid'      => $this->getUuid(),
            'php_version' => str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION),
        ];
    }
}
