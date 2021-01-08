<?php

namespace Startcode\Logger\Error;

class Exception extends ErrorAbstract
{

    /**
     * @param \Exception|\Throwable $exception
     */
    public function handle($exception)
    {
        $this->getErrorData()
            ->setCode($exception->getCode())
            ->setMessage($exception->getMessage())
            ->setFile($this->filterFilePath($exception->getFile()))
            ->setLine($exception->getLine())
            ->setTrace($this->getTrace($exception))
            ->thisIsException();
        $this
            ->display()
            ->log();
    }

    /**
     * @param \Exception|\Throwable $exception
     */
    private function getTrace($exception) : array
    {
        $e = $exception;
        while ($e->getPrevious() !== null) {
            $e = $e->getPrevious();
        }
        return $e->getTrace();
    }
}
