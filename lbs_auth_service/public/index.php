<?php
/**
 * File:  index.php
 *
 */

require_once __DIR__ . '/../src/vendor/autoload.php';

use DavidePastore\Slim\Validation\Validation;
use Illuminate\Database\Capsule\Manager;
use lbs\auth\api\controller\LBSAuthController;
use lbs\backoffice\api\controller\ControllerBackOffice;
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

$app->get('/auth[/]', LBSAuthController::class . ':authenticate');
$app->get('/me[/]', LBSAuthController::class . ':checkToken');

$app->run();