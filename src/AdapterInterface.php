<?php

namespace Startcode\Logger;

interface AdapterInterface
{

    public function save(array $data);

    public function saveAppend(array $data);

    public function saveInOneCall();
}
