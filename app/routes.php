<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;

return function (App $app) {
    
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });
    $app->get('/', function (Request $request, Response $response) {

        $response->getBody()->write('Hello world!');
        return $response;
    });
    $app->get('/infonominatives/{type}/{id}', function (Request $request, Response $response) {
        if(isset($arg['id'])){
            $db = $this->get(PDO::class);
            $requete = $db->prepare("SELECT $arg FROM infirmiere");
            $requete->execute();
            $data = $requete->fetchAll(PDO::FETCH_ASSOC);
            $payload = json_encode($data);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }/*else if(isset($args ['id'])){
            $db = $this->get(PDO::class);
            $sth = $db->prepare("SELECT $args FROM personne");
            $sth->execute();
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);
            $payload = json_encode($data);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }*/
    });
    $app->post('/', function (Request $request, Response $response) {
        $response->getBody()->write('Hello world post !');
        return $response;
    });
    /*$app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });*/
    $app->post('/test', function(Request $request, Response $response){
        $db = $this->post(PDO::class);
        $sth = $db->prepare("SELECT * FROM personne Where id = 17");
        $sth->execute();
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    });
};
