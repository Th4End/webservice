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
    $app->get('/infonominatives/{type}/{id}', function (Request $request, Response $response, $arg) {
        if(isset($arg['id'])){
            $db = $this->get(PDO::class);
            $sth = $db->prepare("SELECT nom, prenom FROM personne where id in (SELECT id from infirmiere)");
            $sth->execute();
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);
            $payload = json_encode($data);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }if(isset($arg['{type}'])){
            switch(substr($arg,-1)){
                case 'infirmiere':
                    $response->getBody()->write('Hello world!');
                    return $response;
                    $db = $this->get(PDO::class);
                    $sth = $db->prepare("SELECT nom, prenom FROM personne where id in (SELECT id from infirmiere)");
                    $sth->execute();
                    $data = $sth->fetchAll(PDO::FETCH_ASSOC);
                    $payload = json_encode($data);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                    break;
                case 'patient':
                    $db = $this->get(PDO::class);
                    $sth = $db->prepare("SELECT * FROM patient");
                    $sth->execute();
                    $data = $sth->fetchAll(PDO::FETCH_ASSOC);
                    $payload = json_encode($data);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                    break;
                case 'administrateur':
                    $db = $this->get(PDO::class);
                    $sth = $db->prepare("SELECT * FROM administrateur");
                    $sth->execute();
                    $data = $sth->fetchAll(PDO::FETCH_ASSOC);
                    $payload = json_encode($data);
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json');
                    break;
            }
        }
       
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
