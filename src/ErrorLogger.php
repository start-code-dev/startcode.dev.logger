<?php

namespace Startcode\Logger;

use Startcode\Logger\Data\Error;
use Startcode\Logger\Error\ErrorData;

class ErrorLogger
{

    private Logger $logger;

    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function log(\Exception $exception) : void
    {
        $errorData = new ErrorData();
        $errorData
            ->setTrace($exception->getTrace())
            ->setCode($exception->getCode())
            ->setMessage($exception->getMessage())
            ->setFile($exception->getFile())
            ->setLine($exception->getLine())
            ->thisIsException();

        $error = new Error();
        $error->setErrorData($errorData);
        $this->logger->log($error);
    }

}
