<?php
/*
 * @author Vojtech Sedlacek <codeasart@gmail.com>
 */

namespace Intergram\Parser;

use Intergram\Logger\AbstractLogger;
use Silex\Application;

use Pimple\Container;


class ServiceParser
{
    const DELIMITER_ROW = "\x3B"; // ;
    const DELIMITER_ITEM = "\x2C"; // ,

    const STATE_INIT = 'init';
    const STATE_END = 'end';

    const ROW_TYPE_Y_PROT_DATA = 'y';
    const ROW_TYPE_A_PORAD_TV = 'a';
    const ROW_TYPE_B_SNIMEK_TV = 'b';
    const ROW_TYPE_L_PODIL_TV = 'l';
    const ROW_TYPE_D_KSNIMEK_TV = 'd';
    const ROW_TYPE_E_KPODIL = 'e';

    const SHAREHOLDER_TYPE_INTERPRETER = 'I';
    const SHAREHOLDER_TYPE_AUTHOR = 'A';

    const DATA_TYPE_ARRAY_EXP = '/^([\x20\x21\x23-\xFF]*)$/g';
    const DATA_TYPE_INT_EXP = '/^([0-9]*)$/g';

    private $state = self::STATE_INIT;

    /**
     * ServiceParser constructor.
     *
     * @param Container $app
     */
    public function __construct(Container &$app) {
        $this->app = &$app;
    }

    /**
     * Entry point for parsing
     * (Note: The function is right before refactoring, which will reflect data types)
     *
     * @TODO: Need refactor
     *
     * @param $filename
     *
     * @throws \Exception
     */
    public function parse($filename) {
        $this->log(AbstractLogger::SEVERITY_INFORMATION, 'File to parse', ['filename' => $filename]);

        $fp = fopen($filename, 'r');
        
        while(($row = fgets($fp)) !== false) {
            $this->log(AbstractLogger::SEVERITY_DEBUG, 'Row to parse', ['row' => $row]);
            $row = rtrim($row, " \t\n\r;");
            $row = ltrim($row, " \t\n\r");

            $records = explode(self::DELIMITER_ROW, $row);
            $items = [];
            foreach ($records as $index => $record) {
                $rowType = $record[0];
                switch ($this->state) {
                    case self::STATE_INIT:
                        if ($rowType == self::ROW_TYPE_Y_PROT_DATA) {
                            $this->state = self::ROW_TYPE_Y_PROT_DATA;
                        } else {
                            throw new \Exception("Unexpected row type ($rowType).");
                        }
                        break;

                    case self::ROW_TYPE_Y_PROT_DATA:
                        switch ($rowType) {
                            case self::ROW_TYPE_A_PORAD_TV:
                                $this->state = self::ROW_TYPE_A_PORAD_TV;
                                break;

                            default:
                                throw new \Exception("Unexpected row type ($rowType).");
                                break;
                        }
                        break;

                    case self::ROW_TYPE_A_PORAD_TV:
                        break;

                    case self::ROW_TYPE_B_SNIMEK_TV:
                        break;

                    case self::ROW_TYPE_L_PODIL_TV:
                        break;

                    case self::ROW_TYPE_D_KSNIMEK_TV:
                        break;

                    case self::ROW_TYPE_E_KPODIL:
                        break;

                    case self::STATE_END:
                        break;

                }

                if (strlen($record) > 0) {
                    $items[$index] = explode(self::DELIMITER_ITEM, $record);
                }
            }

            if ($this->app['debug']) {
                $this->log(AbstractLogger::SEVERITY_INFORMATION, 'Die was called you are under debug mode.', ['filename' => $filename]);
                die();
            }
        }
    }

    /**
     * Log event to logger.
     *
     * @param $severity
     * @param $msg
     * @param array $vars
     */
    protected function log($severity, $msg, array $vars = []) {
        if(isset($this->app['intergram.uselogger']) && $this->app['intergram.uselogger']) {
            $additionalVars = [
                'TIMESTAMP' => date('Y.m.d h:i:s'),
                'STATE' => array_search($this->state, (new \ReflectionObject($this))->getConstants()),
            ];

            $vars = array_merge($additionalVars, $vars);

            $this->app['intergram.logger']->log(get_class($this), $msg, $vars, $severity);
        }
    }
}
