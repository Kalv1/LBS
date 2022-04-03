<?php
/**
 * File:  index.php
 *
 */

require_once __DIR__ . '/../src/vendor/autoload.php';

use DavidePastore\Slim\Validation\Validation;
use Illuminate\Database\Capsule\Manager;
use lbs\command\app\controller\ControllerCommandes;
use lbs\command\app\middleware\TokenMiddleware;
use Respect\Validation\Validator;
use Slim\App;
use \Psr\Http\Message\ServerRequestInterface as Request ;
use \Psr\Http\Message\ResponseInterface as Response ;

$settings = require_once __DIR__ . '/../src/app/conf/settings.php';
$errors = require_once __DIR__ . '/../src/app/conf/errors.php';



$c = new \Slim\Container(array_merge($settings,$errors));
$app = new App($c);


$capsule = new Manager();
$capsule->addConnection(parse_ini_file($c['settings']['dbconf']));
$capsule->setAsGlobal();
$capsule->bootEloquent();


$validate = [
    'nom' => Validator::alpha(' ', '-')->length(3,50),
    'mail' => Validator::email(),
    'livraison' => [
        'date' => Validator::date(),
        'heure' => Validator::date('H:i:s')->min('now'),
    ],
    'items' => Validator::arrayType()
];



$app->get('/commands[/]', ControllerCommandes::class . ':getCommandes');
$app->get('/commands/{id}[/]', ControllerCommandes::class . ':uneCommande')->setName('command')->add(TokenMiddleware::class . ':checkToken');
$app->put('/commands/{id}[/]', ControllerCommandes::class . ':modifCommande');
$app->get('/commands/{id}/items', ControllerCommandes::class. ':getItemCommande');
$app->post('/commands[/]', ControllerCommandes::class . ':createCommande')->add(new Validation($validate));

$app->run();