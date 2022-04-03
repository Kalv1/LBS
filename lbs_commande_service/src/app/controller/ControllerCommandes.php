<?php

namespace lbs\command\app\controller;


use Illuminate\Database\Eloquent\ModelNotFoundException;
use lbs\command\app\models\Commande;
use lbs\command\app\models\Item;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Ramsey\Uuid\Uuid;
use Slim\Container;
use Slim\Router;

class ControllerCommandes
{
    private $c;

    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    public function getCommandes(Request $request, Response $response, $args): Response
    {
        $commandes = Commande::all();
        $data = ["type" => "collection",
            "count" => count($commandes),
            "commande" => $commandes];
        $response = $response->withHeader('Content-Type', 'application/json;charset=utf-8');
        $response->getBody()->write(json_encode($data));
        return $response;
    }

    public function uneCommande(Request $request, Response $response, $args): Response
    {


        try {
            $commande = Commande::findOrFail($args['id']);
        } catch (ModelNotFoundException $e) {
            $response = $response->withStatus(404)->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(json_encode([
                "error" => 404,
                "message" => 'Model innexistant',
            ]));
            return $response;
        }

        $queryparam = $request->getQueryParams();
        if (!empty($queryparam)) {
            if (isset($queryparam['embed']) && $queryparam['embed'] === 'items') {
                $items = Item::where('command_id', '=', $args['id'])->get();
                $items = $items->makeHidden(['uri', 'command_id']);


                $data = ["type" => "ressource",
                    "commande" => $commande,
                    "items" => $items,
                    "links" => [
                        'items' => ['href' => $request->getUri() . '/items'],
                        'self' => ['href' => $request->getUri() . '']
                    ]];
                $response = $response->withHeader('Content-Type', 'application/json;charset=utf-8');
                $response->getBody()->write(json_encode($data));
                return $response;
            }

        }

        $data = ["type" => "ressource",
            "commande" => $commande,
            "links" => [
                'items' => ['href' => $request->getUri() . '/items'],
                'self' => ['href' => $request->getUri() . '']
            ]];
        $response = $response->withHeader('Content-Type', 'application/json;charset=utf-8');
        $response->getBody()->write(json_encode($data));
        return $response;
    }

    public function modifCommande(Request $request, Response $res, $args): Response
    {
        $bodyparam = $request->getQueryParams();
        try {
            $commande = Commande::findOrFail($args['id']);
        } catch (ModelNotFoundException $e) {
            $res = $res->withStatus(404)->withHeader('Content-Type', 'application/json');
            $res->getBody()->write(json_encode([
                "error" => 404,
                "message" => 'Model inexistant',
            ]));
            return $res;
        }

        $commande->mail = filter_var($bodyparam['mail'], FILTER_SANITIZE_EMAIL);
        $commande->nom = filter_var($bodyparam['nom'], FILTER_SANITIZE_STRING);
        $commande->livraison = filter_var($bodyparam['livraison'], FILTER_SANITIZE_STRING);

        $commande->save();

        $res = $res->withStatus(204)->withHeader('Content-Type', 'application/json');

        return $res;
    }

    public function getItemCommande(Request $req, Response $res, $args): Response
    {
        try {
            $items = Item::where('command_id', '=', $args['id'])->get();
            $items = $items->makeHidden(['command_id', 'uri']);
        } catch (ModelNotFoundException $e) {
            $res = $res->withStatus(404)->withHeader('Content-Type', 'application/json');
            $res->getBody()->write(json_encode([
                "error" => 404,
                "message" => 'Model inexistant',
            ]));
            return $res;
        }

        $data = ['type' => 'collection',
            'count' => count($items),
            'items' => $items];

        $res = $res->withStatus(200)->withHeader('Content-Type', 'application/json');
        $res->getBody()->write(json_encode($data));
        return $res;
    }

    public function createCommande(Request $request, Response $res){

        $hasError = $request->getAttribute('has_errors');

        if(isset($hasError)){
            $error = $request->getAttribute('errors');
            $res = $res->withStatus(406)->withHeader('Content-Type', 'application/json');
            $res->getBody()->write(json_encode(['error' => 406, 'message' => $error]));
        } else {
            $body = $request->getParsedBody();
            $nom = filter_var($body['nom'], FILTER_SANITIZE_STRING);
            $mail = filter_var($body['mail'], FILTER_SANITIZE_EMAIL);
            $livraison = $body['livraison'];
            $items = $body['items'];

            $montant = 0.00;


            $command = new Commande();
            $command->nom = $nom;
            $command->mail = $mail;
            $command->livraison = $livraison['date'] . ' ' . $livraison['heure'];
            $id = Uuid::uuid4()->toString();
            $command->id = $id;
            $command->status = 1;


            $token = random_bytes(32);
            $token = bin2hex($token);

            $command->token = $token;

            foreach ($items as $item) {
                $newItem = new Item();
                $newItem->command_id = $id;
                $newItem->quantite = $item['q'];
                $newItem->uri = $item['uri'];
                $newItem->libelle = $item['libelle'];
                $newItem->tarif = $item['tarif'];
                $newItem->save();
                $montant = $montant + ($item['tarif'] * $item['q']);
            }

            $command->montant = $montant;
            $command->makeVisible('token');
            $command->save();


            $data = [
                "commande" => $command
            ];


            $res = $res->withStatus(201)->withHeader('Content-Type', 'application/json')->withHeader('Location', $this->c['router']->pathFor('command',['id' => $command->id]));
            $res->getBody()->write(json_encode($data));
        }
        return $res;
    }
}