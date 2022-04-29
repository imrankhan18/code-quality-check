<?php

use Phalcon\Mvc\Application;
use Phalcon\Config;
use Phalcon\Debug;
use Fabfuel\Prophiler\Profiler;
use Fabfuel\Prophiler\Toolbar;
use Phalcon\Loader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Events\Manager as EventsManager;
use App\Listener\Webhooks;
use Phalcon\Url;
use Phalcon\Mvc\View;


require_once "../../vendor/autoload.php";

$debug = new Debug();
$debug->listen(1, true);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH);
$config = new Config([]);
$loader = new Loader();
$loader->registerDirs(
    [
        BASE_PATH . "/controllers/",
        BASE_PATH . "/models/",
        BASE_PATH . "/webhooks/",
    ]
);
$loader->registerNamespaces(
    [

        'App\Listener' => APP_PATH . "/listener/",
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
        return $mongo = new \MongoDB\Client("mongodb://mongo", array("username" => 'root', "password" => "password123"));

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
$config = new Config(['ip' => 'http://192.168.2.55:8080/']);
$container->set('config', $config);
$container->setShared('profiler', $profiler);
$container->setShared('toolbar', $toolbar);

$eventsManager = new EventsManager();
$container->set(
    'eventsManager',
    function () use ($eventsManager) {
        $eventsManager->attach(
            'webhooks',
            new Webhooks()
        );
        return $eventsManager;
    }
);

try {
    //Handle the request
    $response = $application->handle(
        $_SERVER["REQUEST_URI"]
    );

    $response->send();
} catch (\Exception $e) {
    echo 'Exception: ', $e->getMessage();
}
