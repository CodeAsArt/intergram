<?php
/*
 * @author Vojtech Sedlacek <codeasart@gmail.com>
 */

namespace Intergram\Validator;

use Silex\Application;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class ServiceValidatorProvider implements ServiceProviderInterface
{
    private $validator = null;

    public function register(Container $app) {
        $app['intergram.validator'] = $app->factory(function ($app) {
            if ($this->validator) {
                return $this->validator;
            }

            if (!isset($app['intergram.validator.conf_path'])) {
                $app['intergram.validator.conf_path'] = './validator/';
                if (!is_dir($app['intergram.validator.conf_path'])) {
                    throw new \Exception('Path "'.$app['intergram.validator.conf_path'].'" with configuration files is not exist!');
                }
            }

            $this->validator = new \Intergram\Validator\ServiceValidator($app, $app['intergram.validator.conf_path']);
            return $this->validator;
        });
    }
}
