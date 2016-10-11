<?php

if ($argc <= 1) {
    exit("用法：APP_ENV={env} php cli.php [uri]\n");
}

date_default_timezone_set('PRC');

define('ROOT', realpath(__DIR__ . '/..'));
define('APP', ROOT . '/app');

define('APP_DEVELOPMENT', 'local');
define(
    'APP_ENV',
    isset($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : APP_DEVELOPMENT
);

include ROOT . '/conf/const.common.php';
include ROOT . '/conf/code.common.php';

include ROOT . '/conf/' . APP_ENV . '/const.php';
include ROOT . '/conf/' . APP_ENV . '/db.php';
include ROOT . '/conf/' . APP_ENV . '/redis.php';
include ROOT . '/conf/' . APP_ENV . '/code.php';

require ROOT . '/lang/cn/' . '/error.php';

include ROOT . '/../vendor/autoload.php';
include ROOT . '/app/bootstrap.php';

$container->set('router', function () use ($container) {
    return new Windward\Cli\Router($container);
});

use Windward\Core\Application;

$app = new Application($container);
$app->handle($argv[1]);
