<?php

if (!defined('PUBLIC_PATH')) {
    exit('Invalid boot up.');
}
require __DIR__.'/../vendor/autoload.php';


$app = Laasti\LeanApp\Application::create();

$app->getContainer()->add('Laasti\LeanApp\Controllers\WelcomeController');

//Default routing middleware should be the last middleware added
$app->middleware('directions.default::findAndDispatch');

$app->route('GET', '/', 'Laasti\LeanApp\Controllers\WelcomeController');

$app->run();