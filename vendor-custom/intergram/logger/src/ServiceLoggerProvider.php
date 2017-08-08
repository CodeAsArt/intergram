<?php
/*
 * @author Vojtech Sedlacek <codeasart@gmail.com>
 */

namespace Intergram\Logger;

use Silex\Application;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceLoggerProvider implements ServiceProviderInterface
{
    const LOGGER_BASIC = 0;

    private $logger = null;
    
    public function register(Container $app) {
        $app['intergram.logger'] = $app->factory(function ($app) {
            if ($this->logger) {
                return $this->logger;
            }

            if (!isset($app['intergram.logger.type'])) {
                $app['intergram.logger.type'] = self::LOGGER_BASIC;
            }

            switch ($app['intergram.logger.type']) {
                case self::LOGGER_BASIC:
                    $inlinePrint = false;
                    $elementsOrder = [];
                    if (isset($app['intergram.logger.conf'])) {
                        $conf = $app['intergram.logger.conf'];
                        if (isset($conf['inlinePrint'])) {
                            $inlinePrint = $conf['inlinePrint'];
                        }
                        if (isset($conf['elementsOrder'])) {
                            $elementsOrder = $conf['elementsOrder'];
                        }
                    }
                    $this->logger = new \Intergram\Logger\BasicLogger($app, $inlinePrint, $elementsOrder);
                    return $this->logger;

                    break;

                default:
                    throw new \Exception('Unknown logger!');
                    break;
            }
        });
    }
}
