<?php
/*
 * @author Vojtech Sedlacek <codeasart@gmail.com>
 */

namespace Intergram\Parser;

use Silex\Application;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceParserProvider implements ServiceProviderInterface
{
    public function register(Container $app) {
        $app['intergram.parser'] = $app->factory(function ($app) {
            return new \Intergram\Parser\ServiceParser($app);
        });
    }
}
