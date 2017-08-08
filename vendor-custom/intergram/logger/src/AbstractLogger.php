<?php
/*
 * @author Vojtech Sedlacek <codeasart@gmail.com>
 */

namespace Intergram\Logger;

/**
 * Class AbstractLogger
 */
abstract class AbstractLogger
{
    // Used levels have been defined in RFC 3164
    const SEVERITY_EMERGENCY = 0;
    const SEVERITY_ALERT = 1;
    const SEVERITY_CRITICAL = 2;
    const SEVERITY_ERROR = 3;
    const SEVERITY_WARNING = 4;
    const SEVERITY_NOTICE = 5;
    const SEVERITY_INFORMATION = 6;
    const SEVERITY_DEBUG = 7;

    const VARS_KEY_DEBUG = '#debug_vars';

    /**
     * @param $origin
     * @param $msg
     * @param array $vars
     * @param int $severity
     * @param array $additionalData
     *
     * @return mixed
     */
    abstract public function log($origin, $msg, array $vars = [], $severity = self::SEVERITY_NOTICE);
}
