<?php

namespace App\Listener;

use Phalcon\Di\Injectable;
use Phalcon\Events\Event;
use GuzzleHttp\Client;

class Webhooks extends Injectable
{
    public function webhooks(Event $event): void
    {
        $ip = $this->config->ip;
        $update = json_decode(json_encode($event->getData()), true);
        $data = $this->mongo->Webhooks->Updateproducts->find();
        foreach ($data as $url) {
            $uri = $url['url'];
            print_r($uri);




            $url = $ip;
            $client = new Client(
                [
                    'base_uri' => $url,
                ]

            );
            $response = $client->request('POST', $uri, ['form_params' => $update]);
            echo $response->getBody();
        }
    }
    public function addproduct(Event $event): void
    {
        $ip = $this->config->ip;
        $addproduct = json_decode(json_encode($event->getData()), true);
        $data = $this->mongo->Webhooks->Addproducts->find();
        foreach ($data as $url) {

            $uri = $url['url'];
            // print_r($uri);
            // die;
            $url = $ip;
            $client = new Client(
                [
                    'base_uri' => $url,
                ]

            );
            $response = $client->request('POST', $uri, ['form_params' => $addproduct]);
            echo $response->getBody();
            // die;

        }
    }
}
