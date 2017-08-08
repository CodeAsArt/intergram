<?php
/*
 * @author Vojtech Sedlacek <codeasart@gmail.com>
 */

namespace Intergram\Logger;

use Pimple\Container;

/**
 * Class BasicLogger
 */
class BasicLogger extends AbstractLogger
{
    const LOG_KEY_ORIGIN = 'origin';
    const LOG_KEY_MESSAGE = 'message';
    const LOG_KEY_SEVERITY = 'severity';

    protected $logs = [];
    protected $inlinePrint;
    protected $elementsOrder;

    /**
     * BasicLogger constructor.
     *
     * @param Container $app
     * @param bool $inlinePrint
     * @param array $elementsOrder
     */
    public function __construct(Container &$app, $inlinePrint = false, array $elementsOrder = [])
    {
        $this->app = &$app;
        $this->inlinePrint = $inlinePrint;
        $this->elementsOrder = $elementsOrder;
    }

    /**
     * @param $origin
     * @param $msg
     * @param array $vars
     * @param int $severity
     * @param array $additionalData
     *
     * @throws \Exception
     */
    public function log($origin, $msg, array $vars = [], $severity = AbstractLogger::SEVERITY_NOTICE)
    {
        $elementsOrder = [];
        if (count($this->elementsOrder)) {
            foreach ($this->elementsOrder as $element) {
                if (array_key_exists ($element, $vars)) {
                    $elementsOrder[] = $element;
                }
            }
        }

        $msg = '<b>'.$msg.'</b><br>';
        if (count($vars)) {
            if (count($elementsOrder)) {
                $vars = array_replace(array_flip($elementsOrder), $vars);
            }
            foreach ($vars as $name => $var) {
                if (is_array($var)) {
                    $msg .= '<b>' . $name . '</b>: <pre>' . print_r($var, true) . '</pre>';
                } else {
                    $msg .= '<b>' . $name . '</b>: ' . $var . '<br>';
                }
            }
        }

        if ($this->inlinePrint) {
            $this->printLog($origin, $msg, $severity);
        }

        $this->logs[] = [
            self::LOG_KEY_ORIGIN => $origin,
            self::LOG_KEY_MESSAGE => $msg,
            self::LOG_KEY_SEVERITY => $severity,
        ];
    }

    /**
     * @param null $origin
     * @param null $severity
     *
     * @return array
     */
    public function getLogs($origin = null, $severity = null)
    {
        $logs = array();
        if (!$origin && !$severity) {
            return $this->logs;
        } else {
            if ($origin) {
                $logs = array_filter($this->logs, function ($data) use ($origin) {
                    return $data[self::LOG_KEY_ORIGIN] == $origin;
                });
            }
            if ($severity) {
                $logs = array_filter($this->logs, function ($data) use ($severity) {
                    return $data[self::LOG_KEY_SEVERITY] == $severity;
                });
            }
        }

        return $logs;
    }

    /**
     * @param null $origin
     * @param null $severity
     */
    public function printLogs($origin = null, $severity = null)
    {
        $logs = $this->getLogs($origin, $severity);

        foreach ($logs as $log) {
            $this->printLog($log[self::LOG_KEY_ORIGIN], $log[self::LOG_KEY_MESSAGE], $log[self::LOG_KEY_SEVERITY]);
        }
    }

    /**
     * @param $origin
     * @param $msg
     * @param $severity
     * @param $additionalData
     */
    protected function printLog($origin, $msg, $severity)
    {
        $severity = array_search($severity, (new \ReflectionObject($this))->getConstants());
        echo '<p style="background-color: #EEE">';
        echo '<b>' . $severity . '</b><br>';
        echo '<b>SOURCE:</b> ' . $origin . '<br>';
        echo $msg . '</p><hr>';
    }
}
