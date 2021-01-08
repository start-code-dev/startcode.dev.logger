<?php

namespace Startcode\Logger\Data;

use Startcode\CleanCore\Utility\Tools;

abstract class LoggerAbstract
{
    const HEADER_CLIENT_IP = 'HTTP_X_ND_CLIENT_IP';
    const HEADER_APP_NAME  = 'HTTP_X_ND_APP_NAME';
    const HEADER_UUID      = 'HTTP_X_ND_UUID';
    const X_ND_PREFIX      = 'X_ND';

    /**
     * @var int
     */
    private $id;

    /**
     * @var float
     */
    private $startTime;


    public function getElapsedTime() : float
    {
        return \microtime(true) - $this->startTime;
    }

    public function setStartTime(float $startTime) : self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function setId(int $id) : self
    {
        $this->id = $id;
        return $this;
    }

    public function getId() : int
    {
        return $this->id;
    }

    public function getJsTimestamp() : int
    {
        return (int) (\microtime(true) * 1000);
    }

    public function getClientIp()
    {
        $tools = new Tools();
        $clientIp = $tools->getRealIP(false, [self::HEADER_CLIENT_IP]);
        return $clientIp ?: null;
    }

    public function getAppName() : ?string
    {
        return $_SERVER[self::HEADER_APP_NAME] ?? null;
    }

    public function getXNDParameters() : array
    {
        return \array_filter($_SERVER, function($key) {
            return \strpos($key, self::X_ND_PREFIX) !== false;
        },ARRAY_FILTER_USE_KEY);
    }

    public function getUuid() : ?string
    {
        return $_SERVER[self::HEADER_UUID] ?? null;
    }
}
