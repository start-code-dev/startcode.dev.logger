<?php

namespace Startcode\Logger\Data;

use Startcode\Logger\Error\ErrorData;

class Error extends LoggerAbstract
{

    private ?ErrorData $errorData = null;

    public function getRawData() : array
    {
        return [
            'id'        => \md5(\uniqid(microtime(), true)),
            'timestamp' => $this->getJsTimestamp(),
            'datetime'  => \date('Y-m-d H:i:s'),

            'code'      => $this->errorData->getCode(),
            'type'      => $this->errorData->getName(),
            'message'   => $this->errorData->getMessage(),
            'file'      => $this->errorData->getFile(),
            'line'      => $this->errorData->getLine(),
            'trace'     => \json_encode($this->errorData->getTrace()),
            'context'   => \json_encode($this->errorData->getContext()),

            'hostname'  => \gethostname(),
            'pid'       => \getmypid(),
            'ip'        => $_SERVER['REMOTE_ADDR'] ?? 'cli',
            'client_ip' => $this->getClientIp(),
            'app_name'  => $this->getAppName(),
            'headers'   => \json_encode($this->getXNDParameters()),
            'uuid'      => $this->getUuid(),
            'php_version' => str_replace(PHP_EXTRA_VERSION, '', PHP_VERSION),
        ];
    }

    public function setErrorData(ErrorData $errorData) : self
    {
        $this->errorData = $errorData;
        return $this;
    }
}
