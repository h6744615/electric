<?php
error_reporting(7);
//ini_set("display_errors","On");
date_default_timezone_set('PRC');

define('ROOT', realpath(__DIR__ . '/..'));
define('APP', ROOT . '/app');
define('APP_ENV',isset($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : 'local');


include ROOT . '/app/config/const.common.php';
include ROOT . '/../vendor/autoload.php';
include ROOT . '/app/bootstrap.php';

use Windward\Core\Application;

$app = new Application($container);

$app->handle();
