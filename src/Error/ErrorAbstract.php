<?php

namespace Startcode\Logger\Error;

use Startcode\Logger\Logger;
use Startcode\Logger\Data\Error;

abstract class ErrorAbstract
{

    /**
     * @var ErrorData
     */
    private $errorData;

    /**
     * @var bool
     */
    private $debug;

    /**
     * @var Logger
     */
    private $logger;

    /**
     * @var string
     */
    private $pathRoot;


    public function __construct()
    {
    }

    public function getBackTrace() : array
    {
        return \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    }

    public function getErrorData() : ErrorData
    {
        if (!$this->errorData instanceof ErrorData) {
            $this->errorData = new ErrorData();
        }
        return $this->errorData;
    }

    public function setDebug(bool $debug) : self
    {
        $this->debug = $debug;
        return $this;
    }

    public function setLogger(Logger $logger) : self
    {
        $this->logger = $logger;
        return $this;
    }

    public function setPathRoot(string $pathRoot) : self
    {
        $this->pathRoot = $pathRoot;
        return $this;
    }

    public function display() : self
    {
        if ($this->shouldDisplay()) {
            $presenter = new Presenter();
            $presenter
                ->setData($this->errorData)
                ->display();
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function filterFilePath(string $file)
    {
        return $this->pathRoot === null
            ? $file
            : str_replace(realpath($this->pathRoot), '', $file);
    }

    public function log() : self
    {
        if ($this->logger instanceof Logger) {
            $loggerData = new Error();
            $loggerData
                ->setErrorData($this->errorData);
            $this->logger->log($loggerData);
        }
        return $this;
    }

    private function shouldDisplay() : bool
    {
        return $this->debug && error_reporting() && $this->errorData->getCode();
    }
}
