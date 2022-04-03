<?php

namespace lbs\auth\api\utils;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Writer {

    public static function jsonOutput(Response $resp, int $code, array $data = [], array $headers = []) : Response {

        $data_json = json_encode($data);

        $resp = $resp->withStatus($code);
        $resp = $resp->withHeader('Content-Type', 'application/json;charset=utf-8');

        if(!empty($headers)) {
            foreach($headers as $header) {
                $resp = $resp->withHeader($header[0], $header[1]);
            }
        }

        $resp->getBody()->write($data_json);

        return $resp;
    }
}

?>