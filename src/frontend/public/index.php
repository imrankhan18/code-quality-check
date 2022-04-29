<?php

$_SERVER['REQUEST_URI'] = str_replace("/frontend/", "/", $_SERVER['REQUEST_URI']);

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\View;
use Phalcon\Mvc\Application;
use Phalcon\Config;
use Phalcon\Loader;
use Phalcon\Url;
use Phalcon\Debug;
use Fabfuel\Prophiler\Profiler;
use Fabfuel\Prophiler\Toolbar;

require_once "../../vendor/autoload.php";

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

$debug = new Debug();
$debug->listen(false, true);
$config = new Config([]);
$loader = new Loader();
$loader->registerDirs(
    [
        BASE_PATH . "/controllers/",
        BASE_PATH . "/models/",
    ]
);
$loader->register();
$container = new FactoryDefault();
$application = new Application($container);
$profiler = new Profiler();
$toolbar = new Toolbar($profiler);
$toolbar->addDataCollector(new \Fabfuel\Prophiler\DataCollector\Request());

$container->set(
    'mongo',
    function () {
        $mongo = new \MongoDB\Client("mongodb://mongo", array("username" => 'root', "password" => "password123"));

        return $mongo;
    },
    true
);
$application = new Application($container);

$container->set(
    'view',
    function () {
        $view = new View();
        $view->setViewsDir(BASE_PATH . '/views');
        return $view;
    }
);

$container->set(
    'url',
    function () {
        $url = new Url();
        $url->setBaseUri('/');
        return $url;
    }
);
$config = new config(['ip' => 'http://192.168.2.55:8080/']);
$container->set('config', $config);
$container->setShared('profiler', $profiler);
$container->setShared('toolbar', $toolbar);

try {
    //Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
