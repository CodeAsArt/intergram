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
    const ENCLOSURE_CHAR = "\x22"; // "

    const STATE_INIT = 'init';
    const STATE_END = 'end';

    const ROW_TYPE_Y_PROT_DATA = 'y';
    const ROW_TYPE_A_PORAD_TV = 'a';
    const ROW_TYPE_B_SNIMEK_TV = 'b';
    const ROW_TYPE_L_PODIL_TV = 'l';
    const ROW_TYPE_D_KSNIMEK_TV = 'd';
    const ROW_TYPE_E_KPODIL = 'e';
    // Note: Currently unsupported row types (due to brief)
    const ROW_TYPE_Q_PRODEJ_TV = 'e';
    const ROW_TYPE_R_PSNIMEK_TV = 'r';
    const ROW_TYPE_S_PPODIL_TV = 's';
    const ROW_TYPE_W_R_SPOT = 'w';

    const SHAREHOLDER_TYPE_INTERPRETER = 'I';
    const SHAREHOLDER_TYPE_AUTHOR = 'A';

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
            foreach ($records as $index => $record) {
                $this->stateMachine($record);
            }
        }
    }

    private function stateMachine($record) {
        $rowType = $record[0];
        switch ($this->state) {
            case self::STATE_INIT:
                $this->checkStateInit($rowType, $record);
                break;

            case self::ROW_TYPE_Y_PROT_DATA:
                $this->checkStateRowTypeProtDataY($rowType, $record);
                break;

            case self::ROW_TYPE_A_PORAD_TV:
                $this->checkStateRowTypePoradTvA($rowType, $record);
                break;

            case self::ROW_TYPE_B_SNIMEK_TV:
            case self::ROW_TYPE_L_PODIL_TV:
                $this->checkStateRowTypeSnimekTvBPodilTvL($rowType, $record);
                break;

            case self::ROW_TYPE_D_KSNIMEK_TV:
            case self::ROW_TYPE_E_KPODIL:
                $this->checkStateRowTypeKsnimekTvDKpodilTvE($rowType, $record);
                break;

            case self::STATE_END:
                break;
        }

        $items = str_getcsv($record, self::DELIMITER_ITEM, self::ENCLOSURE_CHAR);
        $this->app['intergram.validator']->validate($rowType, $items);
    }

    /**
     * Check Init state
     *
     * @param $rowType
     *
     * @throws \Exception
     */
    private function checkStateInit($rowType) {
        switch ($rowType) {
            case self::ROW_TYPE_Y_PROT_DATA:
                $this->state = self::ROW_TYPE_Y_PROT_DATA;
                break;

            default:
                throw new \Exception("Unexpected row type ($rowType).");
                break;
        }
    }

    /**
     * Check PROT_DATA state (type y)
     *
     * @param $rowType
     *
     * @throws \Exception
     */
    private function checkStateRowTypeProtDataY($rowType) {
        switch ($rowType) {
            case self::ROW_TYPE_A_PORAD_TV:
                $this->state = self::ROW_TYPE_A_PORAD_TV;
                break;

            case self::ROW_TYPE_Q_PRODEJ_TV:
            case self::ROW_TYPE_W_R_SPOT:
                throw new \Exception("Currently unsupported row type ($rowType).");
                break;

            default:
                throw new \Exception("Unexpected row type ($rowType).");
                break;
        }
    }

    /**
     * Check PORAD_TV state (type a)
     *
     * @param $rowType
     *
     * @throws \Exception
     */
    private function checkStateRowTypePoradTvA($rowType) {
        switch ($rowType) {
            case self::ROW_TYPE_B_SNIMEK_TV:
                $this->state = self::ROW_TYPE_B_SNIMEK_TV;
                break;

            case self::ROW_TYPE_D_KSNIMEK_TV:
                $this->state = self::ROW_TYPE_D_KSNIMEK_TV;
                break;

            case self::ROW_TYPE_A_PORAD_TV:
                $this->state = self::ROW_TYPE_A_PORAD_TV;
                break;

            default:
                throw new \Exception("Unexpected row type ($rowType).");
                break;
        }
    }

    /**
     * Check SNIMEK_TV and PODIL_TV state (type b and l)
     *
     * @param $rowType
     *
     * @throws \Exception
     */
    private function checkStateRowTypeSnimekTvBPodilTvL($rowType) {
        switch ($rowType) {
            case self::ROW_TYPE_L_PODIL_TV:
                $this->state = self::ROW_TYPE_L_PODIL_TV;
                break;

            case self::ROW_TYPE_B_SNIMEK_TV:
                $this->state = self::ROW_TYPE_B_SNIMEK_TV;
                break;

            case self::ROW_TYPE_D_KSNIMEK_TV:
                $this->state = self::ROW_TYPE_D_KSNIMEK_TV;
                break;

            case self::ROW_TYPE_A_PORAD_TV:
                $this->state = self::ROW_TYPE_A_PORAD_TV;
                break;

            default:
                throw new \Exception("Unexpected row type ($rowType).");
                break;
        }
    }

    /**
     * Check KSNIMEK_TV and KPODIL_TV state (type d and e)
     *
     * @param $rowType
     *
     * @throws \Exception
     */
    private function checkStateRowTypeKsnimekTvDKpodilTvE($rowType) {
        switch ($rowType) {
            case self::ROW_TYPE_E_KPODIL:
                $this->state = self::ROW_TYPE_E_KPODIL;
                break;

            case self::ROW_TYPE_B_SNIMEK_TV:
                $this->state = self::ROW_TYPE_B_SNIMEK_TV;
                break;

            case self::ROW_TYPE_D_KSNIMEK_TV:
                $this->state = self::ROW_TYPE_D_KSNIMEK_TV;
                break;

            case self::ROW_TYPE_A_PORAD_TV:
                $this->state = self::ROW_TYPE_A_PORAD_TV;
                break;

            default:
                throw new \Exception("Unexpected row type ($rowType).");
                break;
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
