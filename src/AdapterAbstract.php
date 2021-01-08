<?php

namespace Startcode\Logger;

abstract class AdapterAbstract implements AdapterInterface
{

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var bool
     */
    private $shouldBeLazy = false;

    /**
     * @var bool
     */
    private $shouldSaveInOneCall = false;


    public function appendData(array $data) : self
    {
        $this->data += $data;
        return $this;
    }

    public function beLazy() : void
    {
        $this->shouldBeLazy = true;
    }

    public function getData() : array
    {
        return $this->data;
    }

    public function saveInOneCall() : void
    {
        $this->shouldSaveInOneCall = true;
    }

    public function shouldBeLazy() : bool
    {
        return $this->shouldBeLazy;
    }

    public function shouldSaveInOneCall() : bool
    {
        return $this->shouldSaveInOneCall;
    }
}
