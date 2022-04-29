<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Application;
use Phalcon\Config;
use Phalcon\Loader;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Phalcon\Mvc\View\Simple;
use Phalcon\Debug;
use Fabfuel\Prophiler\Profiler;
use Fabfuel\Prophiler\Toolbar;

require_once "../../vendor/autoload.php";

$debug = new Debug();
$debug->listen(1, true);

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/api');

$config = new Config([]);
$loader = new Loader();
$loader->registerNamespaces(
    [
        'Api\Models' => '../models/',
    ]
);
$loader->register();

$container = new FactoryDefault();
$application = new Application($container);
$profiler = new Profiler();
$toolbar = new Toolbar($profiler);
$toolbar->addDataCollector(new \Fabfuel\Prophiler\DataCollector\Request());
// echo $toolbar->render();

$container->set(
    'mongo',
    function () {
        $mongo = new \MongoDB\Client("mongodb://mongo", array("username" => 'root', "password" => "password123"));

        return $mongo;
    },
    true
);
$prod = new Api\Models\Robots();
$app = new Micro($container);

$container->set('view', function () {
    $view = new Simple();
    $view->setViewsDir(BASE_PATH . '/views');
    return $view;
}, true);
//---------------------------------------------------by handle-------------------------------------

$app->post(
    '/api/token',
    [
        $prod,
        'token'
    ]
);

$app->post(
    '/api/order',
    [
        $prod,
        'order'
    ]
);
$app->post(
    '/api/addorder',
    [
        $prod,
        'addorder'
    ]
);

$app->put(
    '/api/order/update',
    [
        $prod,
        'updateorder'
    ]
);

$app->get(
    '/api/orderlist',
    [
        $prod,
        'list'
    ]
);

$app->get(
    '/api/productlist',
    [
        $prod,
        'productlist'
    ]
);
$app->post(
    '/api/products/update',
    [
        $prod,
        'productupdate'
    ]
);

$app->get(
    '/api',
    [
        $prod,
        'welcome'

    ]
);

$app->get(
    '/api/search/{name}',
    [
        $prod,
        'search'
    ]
);
$app->post(
    '/api/products/add',
    [
        $prod,
        'add'
    ]
);

$app->get(
    '/api/gettoken/{role}',
    [
        $prod,
        'gettoken'
    ]
);


$app->get(
    '/api/search/{per_page}/{page}',
    [
        $prod,
        'getlimit'
    ]
);


//---------------------------------------------------------------------------------------------------------------------------------
$app->handle(
    $_SERVER["REQUEST_URI"]
);
$container->setShared('profiler', $profiler);
$container->setShared('toolbar', $toolbar);
