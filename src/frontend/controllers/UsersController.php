<?php

use Phalcon\Mvc\Controller;

class UsersController extends Controller
{
    public function indexAction():void
    {
        $user = $this->request->getPost();
        if (count($user) > 0) {
            $this->mongo->Frontend->admin->insertOne(
                [
                    'email' => $user['email'],
                    'password' => $user['password'],
                    'role' => $user['role'],
                ]
            );
            $this->response->redirect('/frontend/login/');
        }
    }
}
