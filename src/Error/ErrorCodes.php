<?php

namespace Startcode\Logger\Error;

class ErrorCodes
{

    private static array $map = [
        E_ERROR             => "error",
        E_WARNING           => "warning",
        E_PARSE             => "parse",
        E_NOTICE            => "notice",
        E_CORE_ERROR        => "core_error",
        E_CORE_WARNING      => "core_warning",
        E_COMPILE_ERROR     => "compile_error",
        E_COMPILE_WARNING   => "compile_warning",
        E_USER_ERROR        => "user_error",
        E_USER_WARNING      => "user_warning",
        E_USER_NOTICE       => "user_notice",
        E_STRICT            => "strict",
        E_RECOVERABLE_ERROR => "recoverable_error",
        E_DEPRECATED        => "deprecated",
        E_USER_DEPRECATED   => "user_deprecated",
        E_ALL               => "all",
    ];

    public static function getName(int $errno) : string
    {
        return self::$map[$errno] ?? "__{$errno}__undefined";
    }
}
