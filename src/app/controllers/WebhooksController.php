<?php

use Phalcon\Mvc\Controller;

class WebhooksController extends Controller
{
    public function indexAction(): void
    {
    }
    public function hookAction(): void
    {
        $data = $this->request->getPost();
        if (count($data) > 0) {
            foreach ($data['webhook'] as $key => $value) {
                if ($value === 'add_product') {
                    $this->mongo->Webhooks->Addproducts->insertOne(
                        [
                            'name' => $data['name'],
                            'url' => $data['url'],
                        ]
                    );
                }
                if ($value === 'update_product') {
                    $this->mongo->Webhooks->Updateproducts->insertOne(
                        [
                            'name' => $data['name'],
                            'url' => $data['url'],
                        ]
                    );
                }
            }
            $this->response->redirect('/webhooks/');
        }
    }
}
