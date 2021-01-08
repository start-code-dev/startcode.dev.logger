<?php

namespace Startcode\Logger\Error;

class Error extends ErrorAbstract
{

    public function handle(int $errno, string $errstr, string $errfile, int $errline) : void
    {
        $this->getErrorData()
            ->setCode($errno)
            ->setMessage($errstr)
            ->setFile($this->filterFilePath($errfile))
            ->setLine($errline);
        $this
            ->display()
            ->log();
    }
}
