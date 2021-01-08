<?php

namespace Startcode\Logger\Error;

class Shutdown extends ErrorAbstract
{

    private array $error;

    public function handle() : void
    {
        $this->error = \error_get_last();
        if ($this->hasError()) {
            $this->getErrorData()
                ->setCode($this->error['type'])
                ->setMessage('[SHUTDOWN] ' . $this->error['message'])
                ->setFile($this->filterFilePath($this->error['file']))
                ->setLine($this->error['line']);
            $this
                ->display()
                ->log();
        }
    }

    private function hasError() : bool
    {
        return $this->error !== null;
    }
}
