<?php

namespace lbs\backoffice\api\controller;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ControllerBackOffice
{

    private $client;

    public function __construct()
    {
        $this->client = new Client();

    }


    public function redirectAuth(Request $rq, Response $res)
    {
        $result = $this->client->get('http://api.auth.local/auth', [
            'headers' => [
                'Authorization' => $rq->getHeader('Authorization')
            ]
        ]);
        return $result;
    }

    public function redirectCommand(Request $rq, Response $res){
        $res = $this->client->get('http://api.auth.local/me', [
            'headers' => [
                'Authorization' => $rq->getHeader('Authorization')
            ]
        ]);

        if($res->getStatusCode() === 200) {
            return $this->client->get('http://api.fabrication.local/commandes', [
                'query' => $rq->getQueryParams(),
            ]);
        }

        return $res;
    }
}