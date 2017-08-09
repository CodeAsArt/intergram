<?php

$app->get('/', function () use ($app) {
    /** @var \Intergram\Parser\ServiceParser $parser */
    $parser = $app['intergram.parser'];

    $parser->parse('./../data/hlaseni.t');

//    $app['intergram.logger']->printLogs();
    return 'Input file have been successfully parsed and validated.'
        .' To see logs uncomment previous line (but will be prepared that it will eat your memory :)).'
        .' However loging can be easily changed to log into file, database, wherever you would like.';
});
