<?php

namespace Api\Models;

use Phalcon\Mvc\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use GuzzleHttp\Psr7\Response as Psr7Response;
use Phalcon\Http\Response;

class Robots extends Controller
{
    public function welcome()
    {
        echo "<h1>" . "Welcome to Rest Api" . "<br>" . "Explore Api!!!" . "</h1>";
        echo "<h3>" . "for search products use /api/search/{name}?Token=''" . "<br>" . "signup and get token for different role  hit=>/users" . "</h3>";
    }
    public function add()
    {
        $order = $this->request->getPost();
        // print_r($order);
        $data = $this->mongo->rest_api->products->insertOne([
            'name' => $order['name'],
            'price' => $order['price'],
            'category' => $order['category'],
            'quantity' => $order['quantity'],
        ]);
        // echo "<pre>";
        // print_r();
        $response = new Response();
        $response->setStatusCode(200, 'OK')
            ->setJsonContent(
                [
                    'status' => 200,
                    'data' => $$data,
                ],
                JSON_PRETTY_PRINT
            );
        return $response;
    }
    public function search($name = "")
    {
        $name = urldecode($name);
        $value = explode(" ", $name);
        $key = "Admin_Key";
        $bearer = $this->request->get('Token');
        $jwt = JWT::decode($bearer, new Key($key, 'HS256'));
        if ($jwt->role == 'admin' || $jwt->role == 'user') {
            foreach ($value as $key => $val) {
                $data = $this->mongo->rest_api->products->findOne(['name' => ['$regex' => $val]]);
                // print_r($data);
                // die;
                foreach ($data as $k => $v) {
                    $getdata = json_encode($v);
                    // echo $getdata . "<br>";
                    $response = new Response();
                    $response->setStatusCode(200, 'OK')
                        ->setJsonContent(
                            [
                                'status' => 200,
                                'data' => $getdata,
                            ],
                            JSON_PRETTY_PRINT
                        );
                    return $response;
                }
            }
        } else {
            echo "Access denied";
        }
    }
    public function getlimit($per_page, $page)
    {
        $per_page = (int)$per_page;
        $page = (int)$page;
        $results = ($page - 1) * $per_page;
        $search = $this->mongo->rest_api->products->find([], ['limit' => $per_page, 'skip' => $results])->toArray();
        foreach ($search as $value) {
            // print_r(json_encode($value) . "<br>");
            $response = new Response();
            $response->setStatusCode(200, 'OK')
                ->setJsonContent(
                    [
                        'status' => 200,
                        'data' => $value,
                    ],
                    JSON_PRETTY_PRINT
                );
            return $response;
        }
    }
    public function token()
    {
        $register = $this->request->getPost();
        if ($register['name'] != null && $register['email'] != null && $register['password'] != null && $register['role'] != null) {
            $this->mongo->rest_api->users->insertOne(
                [
                    'name' => $register['name'],
                    'email' => $register['email'],
                    'password' => $register['password'],
                    'role' => $register['role'],


                ]
            );
            $finduser = $this->mongo->rest_api->users->find(["name" => $register['name'], "email" => $register["email"]]);
            foreach ($finduser as $k => $value) {
                $valueid = json_decode(json_encode($value['_id']), true);
            }
            $key = "Admin_Key";
            $payload = array(
                "iss" => "http://example.org",
                "aud" => "http://example.com",
                "iat" => 1356999524,
                "nbf" => 1357000000,
                'name' => $register['name'],
                'email' => $register['email'],
                'id' => $valueid['$oid'],
                'role' => $register['role'],


            );
            $token = JWT::encode($payload, $key, 'HS256');
            return $token;
            $this->response->redirect('/api/login');
        } else {
            echo "fill Details!!!";
        }
    }
    public function updateorder()
    {
        parse_str(file_get_contents("php://input"), $value);
        print_r($value);
        $this->mongo->rest_api->orders->updateOne(
            [
                '_id' => new \MongoDB\BSON\ObjectId($value['orderid'])
            ],
            [
                '$set' => [
                    'status' => $value['status'],
                    'payment' => $value['payment'],
                ]
            ]
        );
        $response = new Response();
        $response->setStatusCode(200, 'OK')
            ->setJsonContent(
                [
                    'status' => 200,
                    'data' => 'updated successfully',
                ],
                JSON_PRETTY_PRINT
            );
        return $response;
    }
    public function list()
    {
        $orderlist = $this->mongo->rest_api->orders->find();
        foreach ($orderlist as $key => $value) {
            $val[] = $value;
        }
        echo json_encode($val);
        $response = new Response();
        $response->setStatusCode(200, 'OK')
            ->setJsonContent(
                [
                    'status' => 200,
                    'data' => $val,
                ],
                JSON_PRETTY_PRINT
            );
        return $response;
    }
    public function productlist()
    {
        $productlist = $this->mongo->rest_api->products->find();
        foreach ($productlist as $key => $value) {
            $val[] = $value;
        }
       
        $response = new Response();
        $response->setStatusCode(200, 'OK')
            ->setJsonContent(
                [
                    'status' => 200,
                    'data' => $val,
                ],
                JSON_PRETTY_PRINT
            );
        return $response;
    }
    public function addorder()
    {
        $data = $this->request->getPost();
        $key = "Admin_Key";
        $token = $this->request->getHeaders();
        $bearer = $token['Token'];
        $jwt = JWT::decode($bearer, new Key($key, 'HS256'));
        if (isset($jwt)) {
            $data = $this->mongo->rest_api->orders->InsertOne([
                'customer_email' => $jwt->email,
                'product_id' => $data['id'],
                'quantity' => $data['qty'],
            ]);
            $response = new Response();
            $response->setStatusCode(200, 'OK')
                ->setJsonContent(
                    [
                        'status' => 200,
                        'data' => $data,
                    ],
                    JSON_PRETTY_PRINT
                );
            return $response;
        }
    }
}