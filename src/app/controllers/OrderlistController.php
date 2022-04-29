<?php

use Phalcon\Mvc\Controller;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Phalcon\Http\Response;

class OrderlistController extends Controller
{
    public function indexAction(): void
    {
        $ip = $this->config->ip;
        $user = $this->mongo->rest_api->users->find();
        foreach ($user as $key => $value) {
            $val = json_decode(json_encode($value), true);
        }

        $login = $this->request->getPost();
        $url = $ip . "api/orderlist";
        // $bearer = "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOi8vZXhhbXBsZS5vcmciLCJhdWQiOiJodHRwOi8vZXhhbXBsZS5jb20iLCJpYXQiOjEzNTY5OTk1MjQsIm5iZiI6MTM1NzAwMDAwMCwibmFtZSI6IkltcmFuIEtoYW4iLCJlbWFpbCI6ImlraWtAZ21haWwuY29tIiwiaWQiOiI2MjYyNmRhZDE1MzEyMjRmYTEwNjAxZDIiLCJyb2xlIjoiYWRtaW4ifQ.62zVeZBEom2FVW5bnAZV2mn50T8Up0gwQv5HlmwQdVg";
        // $key = "Admin_Key";
        // $jwt = JWT::decode($bearer, new Key($key, 'HS256'));
        if ($login['role'] !== null && $login['email'] !== null) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            $res = json_decode($response, true);
            $this->response->redirect('/index/testapi/');
        } else {

            if ($login['role'] === 'user') {
                $this->response->redirect('/api');
            }
        }
    }
    public function orderAction(): void
    {
    }
    public function updateAction(): void
    {
    }
    public function listAction(): void
    {
        $admindb = $this->mongo->rest_api->users->find();
        foreach ($admindb as $key => $value) {
        }
        $orderlist = $this->mongo->rest_api->orders->find();
        echo "<pre>";
        $list = '';
        $list .= "<table>
                    <tr>
                    <th>Customer Email</th>
                    <th>Product ID</th>
                    <th>Quantity</th>
                    <th>Payment</th>
                    <th>Status</th>

                    </tr>";
        foreach ($orderlist as $k => $v) {
            $orderlst = json_decode(json_encode($v), true);
            $list .= "<tr>
                <td>" . $orderlst['customer_email'] . "</td>
                <td>" . $orderlst['product_id'] . "</td>
                <td>" . $orderlst['quantity'] . "</td>
                <td>" . $orderlst['payment'] . "</td>
                <td>" . $orderlst['status'] . "</td>
                </tr>";
        }
        $list .= "</table>";
        $this->view->orderlist = $list;
    }
    public function addorderAction()
    {
        $orderdata = $this->request->getPost();
        if (count($orderdata) > 0) {
            $orderdata = $this->mongo->rest_api->orders->InsertOne([
                'customer_email' => $orderdata['email'],
                'product_id' => $orderdata['pid'],
                'quantity' => $orderdata['quantity'],
            ]);
            // print_r($orderdata);
            $response = new Response();
            $response->setStatusCode(200, 'OK')
                ->setJsonContent(
                    [
                        $orderdata
                    ],
                    JSON_PRETTY_PRINT
                );
            return $response;
            $this->response->redirect('/orderlist/list/');
        }
    }
}
