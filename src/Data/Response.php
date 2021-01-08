<?php

namespace Startcode\Logger\Data;

use Startcode\Runner\Profiler;

class Response extends RequestResponseAbstarct
{

    private ?Profiler $profiler = null;

    public function getRawData() : array
    {
        $resource   = $this->getApplication()->getResponse()->getResponseObject();
        $appMessage = $this->getApplication()->getResponse()->getResponseMessage();
        $httpCode   = $this->getApplication()->getResponse()->getHttpResponseCode();

        return [
            'id'           => $this->getId(),
            'code'         => $httpCode,
            'message'      => $this->getApplication()->getResponse()->getHttpMessage(),
            'resource'     => empty($resource) ? null : \json_encode($resource),
            'app_code'     => $this->getApplication()->getResponse()->getApplicationResponseCode(),
            'app_message'  => empty($appMessage) ? null : \json_encode($appMessage),
            'elapsed_time' => $this->getElapsedTime(),
            'elapsed_time_ms' => (int) ($this->getElapsedTime() * 1000),
            'profiler'     => \json_encode($this->profiler->getProfilerOutput($httpCode)),
        ];
    }

    public function setProfiler(Profiler $profiler) : self
    {
        $this->profiler = $profiler;
        return $this;
    }
}
