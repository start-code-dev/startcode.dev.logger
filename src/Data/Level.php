<?php

namespace Startcode\Logger\Data;

class Level
{

    const OFF           = 0;
    const START         = 1;
    const START_AND_END = 2;
    const END           = 3;

    public static function shouldLogEnd($level) : bool
    {
        return $level == self::END || $level == self::START_AND_END;
    }

    public static function shouldLogStart($level) : bool
    {
        return $level == self::START || $level == self::START_AND_END;
    }
}


