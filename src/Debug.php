<?php

namespace Startcode\Logger;

class Debug
{

    private static array $cssParameters = [
        'position: relative',
        'background: #ffdfdf',
        'border: 2px solid #ff5050',
        'margin: 5px auto',
        'padding: 10px',
        'min-width: 720px',
        'width: 50%',
        'font: normal 12px "Verdana", monospaced',
        'overflow: auto',
        'z-index: 100',
        'clear: both'
    ];

    private static string $_extenstion = '.log';

    private static bool $_isAjaxOrCli;

    public static function handlerError(int $errno, string $errstr, string $errfile, int $errline) : void
    {
        switch ($errno) {
            case E_ERROR:             $fn = "error";                 break;
            case E_WARNING:           $fn = "warning";               break;
            case E_PARSE:             $fn = "parse";                 break;
            case E_NOTICE:            $fn = "notice";                break;
            case E_CORE_ERROR:        $fn = "core_error";            break;
            case E_CORE_WARNING:      $fn = "core_warning";          break;
            case E_COMPILE_ERROR:     $fn = "compile_error";         break;
            case E_COMPILE_WARNING:   $fn = "compile_warning";       break;
            case E_USER_ERROR:        $fn = "user_error";            break;
            case E_USER_WARNING:      $fn = "user_warning";          break;
            case E_USER_NOTICE:       $fn = "user_notice";           break;
            case E_STRICT:            $fn = "strict";                break;
            case E_RECOVERABLE_ERROR: $fn = "recoverable_error";     break;
            case E_DEPRECATED:        $fn = "deprecated";            break;
            case E_USER_DEPRECATED:   $fn = "user_deprecated";       break;
            case E_ALL:               $fn = "all";                   break;
            default:                  $fn = "__{$errno}__undefined"; break;
        }

        if(defined('PATH_ROOT')) {
            $errfile = str_replace(realpath(PATH_ROOT), '', $errfile);
        }

        $err_msg = strtoupper($fn) . ": {$errstr}\nLINE: {$errline}\nFILE: {$errfile}";

        if(defined('DEBUG') && DEBUG && error_reporting() & $errno) {
            echo self::_skipFormating()
                ? $err_msg . PHP_EOL
                : sprintf("<pre>\n<p style='%s'>\n%s\n</p>\n</pre>\n\n", self::getFormattedCss(), $err_msg);
        }

        if(!empty($fn)) {
            self::_writeLog($fn . self::$_extenstion, $err_msg);
            self::_writeLogJson($fn, $errstr, $errno, $errline, $errfile, debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        }
    }

    public static function handlerException(\Exception $e, bool $print = true) : bool
    {
        $file = 'exception' . self::$_extenstion;

        $msg = self::_parseException($e, self::_skipFormating());

        self::_writeLog($file, $msg);
        self::_writeLogJson(get_class($e), $e->getMessage(), $e->getCode(), $e->getLine(), $e->getFile(), $e->getTrace());

        return $print ? print($msg) : true;
    }

    private static function getFormattedCss() : string
    {
        return implode('; ', self::$cssParameters);
    }

    private static function _writeLogJson($type, $msg, $code, $line, $file, $trace)
    {
        $fullError = array(
            'type'     => $type,
            'message'  => $msg,
            'code'     => $code,
            'line'     => $line,
            'file'     => $file,
            'trace'    => $trace,
            'datetime' => date('Y-m-d H:i:s'),
            'tz'       => date_default_timezone_get(),
            'context'  => self::formatRequestData(false),
        );

        return self::_writeLog('____json__' . $type, json_encode($fullError), false);
    }

    public static function handlerShutdown() : void
    {
        $error = error_get_last();
        if($error !== NULL) {
            self::handlerError($error['type'], '[SHUTDOWN] ' . $error['message'], $error['file'], $error['line']);
        }
    }

    private static function _writeLog(string $filename, string $msg, bool $addTime = true) : void
    {
        if($addTime) {
            $msg = self::formatHeaderWithTime() . $msg;
        }

        Writer::writeLogVerbose($filename, $msg);
    }

    private static function _parseException(\Exception $e, bool $plain_text = false) : string
    {
        $exMsg   = $e->getMessage();
        $exCode  = $e->getCode();
        $exLine  = $e->getLine();
        $exFile  = basename($e->getFile());
        $exTrace = $e->getTrace();

        $trace = '';
        foreach ($exTrace as $key => $row) {
            $trace .= '<span class="traceLine">#' . ($key++) . ' ';

            if (!empty($row['function'])) {
                $trace .= "<b>";
                if (!empty($row['class'])) {
                    $trace .= $row['class'] . $row['type'];
                }

                $trace .= "{$row['function']}</b>()";
            }

            if (!empty($row['file'])) {
                $trace .= " | LINE: <b>{$row['line']}</b> | FILE: <u>" . basename($row['file']) . '</u>';
            }

            $trace .= "</span>\n";
        }

        $msg = "<em style='font-size:larger;'>{$exMsg}</em> (code: {$exCode})<br />\nLINE: <b>{$exLine}</b>\nFILE: <u>{$exFile}</u>";

        $parsed = sprintf("<pre>\n<p style='%s'>\n<strong>EXCEPTION:</strong><br />%s\n%s\n</p>\n</pre>\n\n", self::getFormattedCss(), $msg, $trace);

        return $plain_text ? str_replace(array("\t"), '', strip_tags($parsed)) : $parsed;
    }

    public static function formatRequestData($asString = true) : string
    {
        $fromServer = array(
            'REQUEST_URI',
            'REQUEST_METHOD',
            'HTTP_REFERER',
            'QUERY_STRING',
            'HTTP_USER_AGENT',
            'REMOTE_ADDR',
        );

        if($asString) {
            $s = "\n\n";
            $s .= isset($_REQUEST)? "REQUEST: " . print_r($_REQUEST, true) . PHP_EOL   : '';
            foreach ($fromServer as $item) {
                $s .= isset($_SERVER[$item]) ? "{$item}: {$_SERVER[$item]}\n" : '';
            }
        } else {
            $s = array(
                'REQUEST' => $_REQUEST,
                'SERVER'  => $_SERVER,
            );
        }

        return $s;
    }

    public static function formatHeaderWithTime() : string
    {
        $date = date("Y-m-d H:i:s");
        return PHP_EOL . str_repeat('-', 10) . " {$date} " . str_repeat('-', 100) . PHP_EOL;
    }

    private static function _skipFormating() : bool
    {
        return false;
    }
}
