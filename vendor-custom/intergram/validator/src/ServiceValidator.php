<?php
/*
 * @author Vojtech Sedlacek <codeasart@gmail.com>
 */

namespace Intergram\Validator;

use Intergram\Logger\AbstractLogger;
use Silex\Application;

use Pimple\Container;

/**
 * Class ServiceValidator
 * @package Intergram\Validator
 */
class ServiceValidator
{
    const DATA_TYPE_ARRAY_EXP = '/^([\x20\x21\x23-\xFF]*)$/';
    const DATA_TYPE_INT_EXP = '/^([0-9]*)$/';

    private $validationDefinitions = [];

    /**
     * ServiceValidator constructor.
     *
     * @param Container $app
     * @param $confPath
     */
    public function __construct(Container &$app, $confPath) {
        $this->app = &$app;
        $this->initValidator($confPath);
    }

    /**
     * Initialize validator, read definitions from ini files
     *
     * @param $confPath
     */
    private function initValidator($confPath) {
        $allowedArr = [
            'DATA_TYPE_ARRAY_EXP',
            'DATA_TYPE_INT_EXP',
        ];

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($confPath));
        foreach ($iterator as $path) {
            if ($path->isFile()) {
                $definitions = parse_ini_file($path, true);
                foreach ($definitions as $index => $conf) {
                    if (in_array($conf['regex'], $allowedArr)) {
                        $definitions[$index]['regex'] = constant('self::'.$conf['regex']);
                    }
                }

                $path_parts = pathinfo($path);
                $this->validationDefinitions[$path_parts['filename']] = $definitions;
            }
        }
    }

    /**
     * Validation function
     *
     * @param $rowType
     * @param $items
     *
     * @throws \Exception
     */
    public function validate($rowType, $items) {
        if (!isset($this->validationDefinitions[$rowType])) {
            throw new \Exception("Missing validation conf file for row type '$rowType'.");
        }
        foreach ($this->validationDefinitions[$rowType] as $index => $validationDefinition) {
            if ((!isset($items[$index]) || strlen($items[$index]) === 0) && !$validationDefinition['required']) {
                continue;
            }

            if (strlen($items[$index]) > $validationDefinition['length']) {
                throw new \Exception('Row item "'
                    . $validationDefinition['attr']
                    . '" with value "'
                    . $items[$index]
                    . '" has '
                    . strlen($items[$index])
                    . ' characters, but only '
                    . $validationDefinition['length']
                    . ' characetrs is allowed!');
            }
            if (!preg_match($validationDefinition['regex'], $items[$index])) {
                throw new \Exception('Row item "' . $validationDefinition['attr'] . '" with value "' . $items[$index]
                    . '" contain not allowed characters! Regex string is "' . $validationDefinition['regex'] . '".');
            }
        }
    }
}
