<?php

namespace lbs\fab\app\controller;

use lbs\fab\app\models\Commande;
use Slim\Container;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;


class ControllerFabrication
{
    private $c;

    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    public function getCommandes(Request $request, Response $response){
        $param = $request->getQueryParams();
        if(isset($param['s'])){
            $commands = Commande::where('status', '=', $param['s'])->get()->toArray();
        } else {
            $commands = Commande::all()->toArray();
        }

        $count = count($commands);


        if(isset($param['page'])){
            if($param['page'] < 0) {
                $param['page'] = 0;
            }
            if(isset($param['size']) && $param['size'] > 0) {
                $commands = array_slice($commands, $param['page'], $param['size']);
            } else {
                $commands = array_slice($commands, $param['page'], 10);
            }
        } else {
            if(isset($param['size']) && $param['size'] > 0) {
                $commands = array_slice($commands, 0, $param['size']);
            } else {
                $commands = array_slice($commands, 0, 10);
            }
        }

        $tabres = [];

        foreach ($commands as $command) {
            $tabres[] = ['command' => $command,
                         'links' => [
                             'self' => ['href' => '/commands/' . $command['id']]
                         ]];
        }


        if(isset($param['page'])){
            $pageprev = $param['page'] - 1;
            $pagesuiv = $param['page'] + 1;
        } else {
            $pagesuiv = "1";
            $pageprev = "0";
        }

        $size = $param['size'] ?? 10;

        $data = [
            'type' => 'collection',
            'count' => $count,
            'size' => $size,
            'links' => [
                'next' => [
                    'href' => '/commandes/?page=' . $pagesuiv . '&size='. $size
                ],
                'prev' => [
                    'href' => '/commandes/?page=' . $pageprev . '&size='. $size
                ],
                'last' => [
                    'href' => '/commandes/?page=' . strval(intdiv($count, $size)) . '&size='. $size
                ],
                'first' => [
                    'href' => '/commandes/?page=0&size='.$size
                ]
            ],
            'commands' => $tabres
        ];

        $response = $response->withHeader('Content-type', 'application/json')->withStatus(200);
        $response->getBody()->write(json_encode($data));
        return $response;
    }
}