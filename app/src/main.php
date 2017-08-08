<?php

$app->get('/', function () use ($app) {
    /** @var \Intergram\Parser\ServiceParser $parser */
    $parser = $app['intergram.parser'];
    
    $parser->parse('./../data/hlaseni.t');

});
