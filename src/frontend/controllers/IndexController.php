<?php

use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

class IndexController extends Controller
{
    public function indexAction(): void
    {
        $ip = $this->config->ip;
        echo $ip."<br>";
        echo "Hello" . "<br>" . "for login and see product list hit http://localhost:8080/frontend/users and fill details and make sure role must be admin ";
    }
}
