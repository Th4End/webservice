<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use \Tuupola\Middleware\JwtAuthentication;
use \Tuupola\Base62;

return function (App $app) {
    
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });
    /*
    $app->post('/visite', function (Request $request, Response $response) {
        $data = (array)$request->getParsedBody();

        $response->getBody()->write($data['idpatient']);
        return $response;
    });
    */
    $app->get('/', function (Request $request, Response $response,$arg) {

        $response->getBody()->write('Hello world!');
        return $response;
    });
    $app->get('/infonominatives[/{type}[/{id}]]', function (Request $request, Response $response, $arg) {
        $type = array('infirmiere', 'patient','administrateur','infirmieres','patients','administrateurs');
        $typeAvecId = array('infirmiere', 'patient','administrateur',);
        if(!isset($arg['id']) && !isset($arg['type'])){
            $db = $this->get(PDO::class);
            $sth = $db->prepare("SELECT * from personne");
            $sth->execute();
            $data = $sth->fetchAll(PDO::FETCH_ASSOC);
            $payload = json_encode($data);
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
        }
        else
        { 
            if(!in_array($arg['type'],$type))
            {
                return $response->withStatus(404);
            }
            else
            {
                 if(isset($arg['id']))
                {
                    if(!in_array($arg['type'],$typeAvecId))
                    {
                        return $response->withStatus(404);
                    }
                    else{
                        switch($arg['type']){
                            case 'infirmiere':
                                //var_dump($arg['id']); exit();
                                $db = $this->get(PDO::class); 
                                $sth = $db->prepare("SELECT * from personne p ,infirmiere i where p.id = :vID and p.id=i.id "); 
                                $sth->bindParam(':vID',$arg['id']); 
                                break;
                            case 'patient':
                                $db = $this->get(PDO::class); 
                                $sth = $db->prepare("SELECT * from personne p ,patient t where p.id = :vID and p.id=t.id "); 
                                $sth->bindParam(':vID',$arg['id']); 
                                
                                break;
                            case 'administrateur':
                                $db = $this->get(PDO::class); 
                                $sth = $db->prepare("SELECT * from personne p , administrateur a where p.id = :vID and p.id=a.id "); 
                                $sth->bindParam(':vID', $arg['id']); 
                                break;
                                
                            }
                            $sth->execute(); 
                            $data = $sth->fetchAll(PDO::FETCH_ASSOC); 
                            if(!$data)
                            {
                                return $response->withStatus(400);
                            }
                            else{
                                $payload = json_encode($data); 
                                $response->getBody()->write($payload);
                                return $response->withHeader('Content-Type', 'application/json');

                            }
                    }
                }
            
            else 
            {
                if(isset($arg['type']) && !isset($arg['id']))
                {
                   //var_dump($arg['type']); exit();
                    switch($arg['type']){
                       case 'infirmieres':
                                $db = $this->get(PDO::class);
                                $sth = $db->prepare("SELECT nom, prenom FROM personne where id in (SELECT id from infirmiere)");
                                $sth->execute();
                                $data = $sth->fetchAll(PDO::FETCH_ASSOC);
                                $payload = json_encode($data);
                                $response->getBody()->write($payload);
                                return $response->withHeader('Content-Type', 'application/json');
                        break;
                        case 'patients':
                                $db = $this->get(PDO::class);
                                $sth = $db->prepare("SELECT * from patient");
                                $sth->execute();
                                $data = $sth->fetchAll(PDO::FETCH_ASSOC);
                                $payload = json_encode($data);
                                $response->getBody()->write($payload);
                                return $response->withHeader('Content-Type', 'application/json');
                        break;
                            case 'administrateurs':
                              $db = $this->get(PDO::class);
                              $sth = $db->prepare("SELECT nom, prenom FROM personne where id in (Select id from administrateur)");
                              $sth->execute();
                              $data = $sth->fetchAll(PDO::FETCH_ASSOC);
                              $payload = json_encode($data);
                              $response->getBody()->write($payload);
                              return $response->withHeader('Content-Type', 'application/json');
                           break;
                        }
                    }
            }
        }
}});
    $app->post('/visite', function (Request $request, Response $response) {
        $db = $this->get(PDO::class);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
        $data = (array)$request->getParsedBody();
        $sth = $db->prepare('INSERT into visite (patient,infirmiere,date_prevue,duree) Values (:patient,:infirmiere,:date_prevue,:duree)');
        $sth->bindParam(':patient', $data['patient']);
        $sth->bindParam(':infirmiere',$data['infirmiere']);
        $sth->bindParam(':date_prevue',$data['date_prevue']);
        $sth->bindParam(':duree',$data['duree']);
        $sth->execute();
        return $response->withStatus(201);
    });

    $app->get('/identification[/{login}[/{mdp}]]', function(Request $request, Response $response, $arg){
        $db = $this->get(PDO::class);
        $sth = $db->prepare('SELECT login,mp from personne_login where login = :login and mp = :mdp');
        $sth->bindParam(':login',$arg['login']);
        $sth->bindParam(':mp',$arg['mdp']);
        $data = $sth->fetchAll(PDO::FETCH_ASSOC);
        if(!$arg['login'] && !$arg['mdp']){
            return $response->withStatus(404);
        }else{
            $requete = $db->prepare('SELECT mp from personne_login where login = :login');
            $requete->bindParam(':login', $arg['login']);
            $requete->execute();
            $data = $requete->fetchAll(PDO::FETCH_ASSOC);
            $mdp = $data[0]['mp'];
            $bonjour = md5($arg['mdp']);
            //var_dump($data);
            //var_dump($bonjour);exit();
            if($bonjour === $mdp){
                return $response->withStatus(200);
                $now = new DateTime();
                $future = new DateTime("now +2 hours");
                $jti = Base62::encode(random_bytes(16));
                $secret = "12345test";
                $payload = [
                    "jti" => $jti,
                    "iat" => $now->getTimestamp(),
                    "nbf" => $future->getTimestamp()
                ];

            }else{
                if($bonjour != $data){
                    return $response->withStatus(403);
                }
            }
       // var_dump($arg['login']);exit();
        //var_dump($data);exit();
        return $response->withHeader('Content-Type','application/json');
        }
        
        

    });
    
    /*$app->group('/users', function (Group $group) {
        $group->get('', ListUsersAction::class);
        $group->get('/{id}', ViewUserAction::class);
    });*/
    $app->post('/test', function(Request $request, Response $response){
        $response->getBody()->write('gelo');
        return $response;
    });
};
