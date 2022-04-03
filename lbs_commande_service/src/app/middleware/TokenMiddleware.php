<?php

namespace lbs\command\app\middleware;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use lbs\command\app\models\Commande;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class TokenMiddleware
{
    public function checkToken(Request $req, Response $res, callable $next)
    {
        $id = $req->getAttribute('route')->getArgument('id');

        $param = $req->getQueryParam('token');
        $body = isset($req->getHeader('X-lbs-token')[0]) ? $req->getHeader('X-lbs-token')[0] : null;
        if (!isset($param) && !isset($body)) {
            $res = $res->withStatus(401)->withHeader('Content-Type', 'application/json');
            $res->getBody()->write(json_encode(['error' => 'You should specifie token.']));
            return $res;
        } else {
            if (!is_null($param)) {
                try {
                    Commande::where('id', '=', $id)
                        ->where('token', '=', $param)
                        ->firstOrFail();
                } catch (ModelNotFoundException $e) {
                    $res = $res->withStatus(404)->withHeader('Content-Type', 'application/json');
                    $res->getBody()->write(json_encode(['error' => 'No command found']));
                    return $res;
                }
            } else {
                try {
                    Commande::where('id', '=', $id)
                        ->where('token', '=', $body)
                        ->firstOrFail();
                } catch (ModelNotFoundException $e) {
                    $res = $res->withStatus(404)->withHeader('Content-Type', 'application/json');
                    $res->getBody()->write(json_encode(['error' => 'No command found']));
                    return $res;
                }
            }
            return $next($req, $res);
        }
    }
}