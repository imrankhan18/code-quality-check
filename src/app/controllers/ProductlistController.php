<?php

use Phalcon\Mvc\Controller;

class ProductlistController extends Controller
{
    public function indexAction(): void
    {
        $ip = $this->config->ip;
        $url = $ip . "api/productlist";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        echo "<pre>";

        $res = json_decode($response, true);
        // print_r($res);
        // die;
        $this->view->productlist = $res['data'];
    }
    public function addAction(): void
    {
    }
    public function editproductAction(): void
    {
        $id = $this->request->getQuery();
        $this->view->edit = $id;
    }
    public function updateproductAction(): void
    {
        $update = $this->request->getPost('update');
        $id = $this->request->getPost('id');
        $this->mongo->rest_api->products->UpdateOne(
            [
                '_id' => new \MongoDB\BSON\ObjectId($id)
            ],
            [
                '$set' => [
                    'quantity' => $update,
                ]
            ]
        );
        $productup = $this->mongo->rest_api->products->findOne(
            [
                '_id' => new \MongoDB\BSON\ObjectId($id)
            ]
        );
        $this->eventsManager->fire('webhooks:webhooks', $this, $productup);
        $this->response->redirect('/productlist');
    }

    public function addproductAction(): void
    {
        $products = $this->request->getPost();
        $data = $this->mongo->rest_api->products->insertOne([
            'name' => $products['name'],
            'price' => $products['price'],
            'category' => $products['category'],
            'quantity' => $products['quantity'],
        ]);
        $id = json_decode(json_encode($data->getInsertedId()), 1);
        $productfind = $this->mongo->rest_api->products->findOne(['_id' => new \MongoDB\BSON\ObjectId($id['$oid'])]);
        $this->eventsManager->fire('webhooks:addproduct', $this, $productfind);
        $this->response->redirect('/frontend/productlist/list/');
    }
}
