<?php

use \Psr\Http\Message\ServerRequestInterface as Request ;
use \Psr\Http\Message\ResponseInterface as Response ;

return [
    'notFoundHandler' => function(\Slim\Container $c) {
        return function(Request $req , Response $res) {
            $res = $res->withStatus(400)->withHeader('Content-Type', 'application/json');

            $res->write(json_encode([
                'type' => 'error',
                'code' => 400,
                'message' => 'ressource non disponible : '. $req->getUri(),
            ]));
            return $res;
        };
    },
    'notAllowedHandler' => function(\Slim\Container $c) {
        return function(Request $req, Response $res, array $methods){
            $method = $req->getMethod();
            $url = $req->getUri();

            $res = $res->withStatus(405)->withHeader('Content-Type', 'application/json')->withHeader('Allow', implode(',', $methods))
                ->write(json_encode(['type' => 'error',
                    'error' => 405,
                    'message' => "method $method not allowed for uri. Should be ". implode(',', $methods)]));
            return $res;
        };
    },
    'phpErrorHandler' => function (\Slim\Container $c) {
        return function(Request $req, Response $res, Throwable $error) : Response {
            $res = $res->withStatus(500)
                ->withHeader('Content-Type', 'application/json')
                ->write(json_encode([
                    'type' => 'error',
                    'error' => 500,
                    'message' => "internal server error : {$error->getMessage()}",
                    'trace' => $error->getTraceAsString(),
                    'file' => $error->getFile() . 'line : ' . $error->getLine()
                ]));
            return $res;

        };
    }
];
