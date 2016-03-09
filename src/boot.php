<?php

if (!defined('PUBLIC_PATH')) {
    exit('Invalid boot up.');
}
require __DIR__.'/../vendor/autoload.php';


$app = Laasti\LeanApp\Application::create();

$app->container()->add('Laasti\LeanApp\Controllers\WelcomeController');

$app->route('GET', '/', 'Laasti\LeanApp\Controllers\WelcomeController');

$app->run();