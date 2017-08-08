<?php

use Silex\Application;

define("ROOT_DIR", __DIR__ . '/../');

require_once __DIR__.'/../vendor/autoload.php';

$app = new Application();

require_once __DIR__.'/config/config.php';
require_once __DIR__.'/src/main.php';

return $app;
