<?php

namespace Startcode\Logger;

use Startcode\CleanCore\Utility\Tools;

class Writer
{
    const LOGPATH_FORMAT_SIMPLE  = 1;
    const LOGPATH_FORMAT_COMPLEX = 2;

    private static int $logPathFormat = self::LOGPATH_FORMAT_COMPLEX;

    private static array $_validLogPathFormats = array(
        self::LOGPATH_FORMAT_SIMPLE,
        self::LOGPATH_FORMAT_COMPLEX,
    );

    public static function setLogPathFormat(int $format) : void
    {
        if(!in_array($format, self::$_validLogPathFormats)) {
            throw new \Exception('Selected Log file path format is not valid');
        }

        self::$logPathFormat = $format;
    }

    public static function preVarDump($var, $die = false, $color = '#000000', $parsable = false, $output = true, $indirect = false)
    {
        if(!defined('DEBUG') || DEBUG !== true) {
            return false;
        }

        $formatted = '';

        // if ajax or cli, skip preformating
        $skipFormating =
            (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest')
            || php_sapi_name() == 'cli'
            || (defined('DEBUG_SKIP_FORMATTING') && DEBUG_SKIP_FORMATTING)
            || !$output;

        $skipFormating || $formatted .= "<pre style='padding: 5px; background:#ffffff; font: normal 12px \"Lucida Console\", \"Courier\",
            monospace; position:relative; clear:both; color:{$color}; border:1px solid {$color}; text-align: left !important;'>";

        $formatted .= self::_preFormat($var, $parsable, intval($indirect) + 1, $skipFormating);

        $skipFormating || $formatted .= "</pre>";

        if(!$output) {
            return $formatted;
        }

        echo $formatted . "\n";

        if($die) {
            die("\n\nDebug terminated\n");
        }
    }


    private static function _preFormat($var, $parsable = false, $indirect = false, $skipFormating = false) : string
    {
        $index = intval($indirect);
        $trace = debug_backtrace();
        $line = isset($trace[$index]['line']) ? $trace[$index]['line'] : null;
        $file = isset($trace[$index]['file']) ? $trace[$index]['file'] : null;

        $type = gettype($var);

        if(is_object($var) || is_array($var)) {
            $var = !$parsable
                ? print_r($var, true)
                : var_export($var, true);
        } elseif(is_resource($var)) {
            $var = get_resource_type($var) . ': ' . $var;
        }

        return "\nCalled in '{$file}' LINE {$line}\nvar dump: ({$type}):\n" . ($skipFormating ? $var : htmlspecialchars($var));
    }

    public static function writeLog (string $filename, string $string) : bool
    {
        $logs_path = defined('PATH_LOGS')
            ? PATH_LOGS
            : getcwd();

        $logs_path_real = realpath($logs_path);

        if(!$logs_path_real || !is_writable($logs_path_real)) {
            throw new \Exception("Log path is not accessible or writable");
        }

        $file_name = $logs_path_real . DIRECTORY_SEPARATOR . strtolower(trim($filename));

        $file_folder = dirname($file_name);

        if (!is_dir($file_folder)) {
            if (!mkdir($file_folder, 0766, true)) {
                throw new \Exception("Couldn't create logs subfolder");
            }
        }

        if (!is_file($file_name)) {
            if (@touch($file_name)) {
                @chmod($file_name, 0777);
            }
        }

        return file_put_contents($file_name, $string . PHP_EOL, FILE_APPEND | LOCK_EX);
    }


    /**
     * @param  string|array $extras   - other info you need to insert into file name
     */
    public static function writeLogVerbose(string $filename, string $string, $extras = array()) : bool
    {
        $filename = self::_formatFilePath($filename, self::$logPathFormat, $extras);
        return self::writeLog($filename, $string);
    }

    /**
     * Format file path
     *
     * @param string $filename
     * @param int $format
     * @param array $extras
     * @param bool $addHostname
     * @throws \Exception
     * @return string
     */
    private static function _formatFilePath($filename, $format, $extras = array(), $addHostname = false)
    {
        // we can parse scalars also
        if(is_scalar($extras)) {
            $extras = array($extras);
        }

        if(!is_array($extras)) {
            throw new \Exception('Extras parameter must be array');
        }

        $dot_pos  = strrpos($filename, '.');
        if(!$dot_pos) {
            $filename .= '.log';
            $dot_pos  = strrpos($filename, '.');
        }

        $tmp = strlen($filename) - $dot_pos;

        switch ($format) {
            case self::LOGPATH_FORMAT_COMPLEX:
                // add short date and 24 hour format as last parama
                $extras[] = $addHostname
                    ? substr($filename, 0, $dot_pos) . '_' . gethostname()
                    : substr($filename, 0, $dot_pos);
                $extras = array_merge($extras, explode('-', date("H-d-m-Y", Tools::ts())));
                // reverse order or extras array so that year is first, month etc...
                $extras = array_reverse($extras);
                $glue = DIRECTORY_SEPARATOR;
                break;

            default:
            case self::LOGPATH_FORMAT_SIMPLE:
                // add machine hostname to extra array
                $extras[] = substr($filename, 0, $dot_pos);
                if($addHostname) {
                    $extras[] = gethostname();
                }
                $extras[] = date("Y-m-d", Tools::ts());
                $glue = '_';
                break;
        }

        return implode($glue, $extras) . substr($filename,  "-{$tmp}", $tmp);
    }

    public static function writeLogPre($var, $filename = '__preformated.log', $addRequestData = false, $traceIndex = 1)
    {
        $msg = self::_preFormat($var, false, $traceIndex, true);

        if($addRequestData) {
            $msg = Debug::formatHeaderWithTime() . $msg . Debug::formatRequestData();
        }

        return self::writeLogVerbose($filename, $msg);
    }

}
