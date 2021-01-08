<?php

namespace Startcode\Logger\Error;

class ErrorData
{

    /**
     * @var int
     */
    private $code;

    /**
     * @var string
     */
    private $file;

    /**
     * @var bool
     */
    private $exceptionFlag;

    /**
     * @var int
     */
    private $line;

    /**
     * @var string
     */
    private $message;

    /**
     * @var array
     */
    private $trace;

    public function getContext() : array
    {
        return [
            'REQUEST' => $_REQUEST,
            'SERVER'  => $_SERVER,
        ];
    }

    public function getDataAsString() : string
    {
        return \join(PHP_EOL, [
            \strtoupper($this->getName()) . ": {$this->getMessage()}",
            "LINE: {$this->getLine()}",
            "FILE: {$this->getFile()}",
        ]) . PHP_EOL;
    }

    public function getFile() : string
    {
        return $this->file;
    }

    public function getLine() : string
    {
        return $this->line;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    public function getName() : string
    {
        return $this->exceptionFlag === true
            ? 'exception'
            : ErrorCodes::getName($this->getCode());
    }

    public function getCode() : int
    {
        return $this->code;
    }

    public function getTrace() : array
    {
        return empty($this->trace)
            ? \debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS)
            : $this->trace;
    }

    public function setCode(int $code) : self
    {
        $this->code = $code;
        return $this;
    }

    public function setFile(string $file) : self
    {
        $this->file = $file;
        return $this;
    }

    public function setLine(int $line) : self
    {
        $this->line = $line;
        return $this;
    }

    public function setMessage(string $message) : self
    {
        $this->message = $message;
        return $this;
    }

    public function setTrace(array $trace) : self
    {
        $this->trace = $trace;
        return $this;
    }

    public function thisIsException() : self
    {
        $this->exceptionFlag = true;
        return $this;
    }
}
