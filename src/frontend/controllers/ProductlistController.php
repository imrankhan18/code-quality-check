<?php

use Phalcon\Mvc\Controller;
use Phalcon\Http\Response;

class ProductlistController extends Controller
{
    public function indexAction():void
    {
        $ip = $this->config->ip;
        $url = $ip . "api/productlist";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        $res = json_decode($response, true);
        foreach ($res as $key => $value) {
            print_r($value);

            $this->mongo->Frontend->products->insertOne(
                [
                    '_id' => new \MongoDB\BSON\ObjectId($value['_id']['$oid']),
                    'name' => $value['name'],
                    'price' => $value['price'],
                    'category' => $value['category'],
                    'quantity' => $value['quantity'],
                ]
            );
        }
    }
    public function listAction():void
    {
        $login = $this->request->getPost();
        $logindb = $this->mongo->Frontend->admin->find();
        foreach ($logindb as $key => $value) {
        }

        if (count($login) > 0) {
            if ($login['role'] == $value['role'] && $login['email'] == $value['email'] && $login['password'] == $value['password']) {
                $list = $this->mongo->Frontend->products->find();
                foreach ($list as $k => $v) {
                    $val[] = json_decode(json_encode($v), true);
                }
                $this->view->productlist = $val;
            }
        } else {
            echo "fill correct details";
        }
    }
    public function updatestockAction()
    {
        $data = $this->request->getPost();


        $this->mongo->Frontend->products->updateOne(
            [
                '_id' => new \MongoDB\BSON\ObjectId($data['_id']['$oid'])
            ],
            [
                '$set' => [
                    'quantity' => $data['quantity'],
                ]
            ]

        );
        $response = new Response();
        $response->setStatusCode(200, 'OK')
            ->setJsonContent(
                [
                    'status' => 200,
                    'message' => 'Stock updated sucessfully',
                ],
                JSON_PRETTY_PRINT
            );
        return $response;
    }
    public function addproductAction()
    {
        $productdata = $this->request->getPost();
        $this->mongo->Frontend->products->insertOne(
            [
                '_id' => new \MongoDB\BSON\ObjectId($productdata['_id']['$oid']),
                'name' => $productdata['name'],
                'price' => $productdata['price'],
                'category' => $productdata['category'],
                'quantity' => $productdata['quantity'],

            ]
        );
        $response = new Response();
        $response->setStatusCode(200, 'OK')
            ->setJsonContent(
                [
                    'status' => 200,
                    'message' => 'Product added sucessfully',
                ],
                JSON_PRETTY_PRINT
            );
        return $response;
    }
    public function recieveResponseAction():void
    {
        $response = $this->request->getPost();
        $response = $response[0];


        $response = array_merge($response, ['_id' => (new \MongoDB\BSON\ObjectId($response['_id']['$oid']))]);
        $this->mongo->Frontend->products->insertOne($response);
    }
}
