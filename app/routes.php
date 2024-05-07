<?php

declare(strict_types=1);

use App\Application\Actions\User\ListUsersAction;
use App\Application\Actions\User\ViewUserAction;
use Firebase\JWT\JWK;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PHPUnit\Framework\MockObject\Stub\ReturnReference;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Interfaces\RouteCollectorProxyInterface as Group;
use \Tuupola\Middleware\JwtAuthentication;

use function PHPUnit\Framework\isNull;

class identification
{
    public int $leDroit;
    public int $id ;
    public function __construct()
    {
        $this->leDroit=0;

    }
}
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
        //$droit = 0;
        $lidentification=new identification();
        $verif= verifJWT($request, $lidentification);
        //var_dump($lidentification->leDroit);exit();
if($verif)
{
        
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
                 if(isset($lidentification->id))
                {
                    if(!in_array($arg['type'],$typeAvecId))
                    {
                        return $response->withStatus(404);
                    }
                    else{
                        switch($arg['type']){
                            case 'infirmiere':
                                if($lidentification->leDroit == 1){
                                 $db = $this->get(PDO::class); 
                                 $sth = $db->prepare("SELECT * from personne p ,infirmiere i where p.id = :vID and p.id=i.id "); 
                                 $sth->bindParam(':vID',$lidentification->id);
                                
                                }else{
                                    if($lidentification->leDroit == 2 || $lidentification->leDroit == 3){
                                        $db = $this->get(PDO::class); 
                                    $sth = $db->prepare("SELECT * from personne p ,infirmiere i where p.id = :vID and p.id=i.id "); 
                                    $sth->bindParam(':vID',$arg['id']);
                                    }else{
                                        return $response->withStatus(403);
                                    }
                                }
                                    
                                
                               //$this->droit = 2;
                               //var_dump($lidentification->id);exit();
                                 
                                //var_dump($arg['id']); exit();
                                
                                break;
                            case 'patient':
                                if($lidentification->leDroit == 0){
                                    $db = $this->get(PDO::class); 
                                    $sth = $db->prepare("SELECT * from personne p ,patient t where p.id = :vID and p.id=t.id "); 
                                    $sth->bindParam(':vID',$arg['id']); 
                                }else{
                                    if($lidentification->leDroit == 3 || $lidentification->leDroit == 2 || $lidentification->leDroit == 1){
                                        $db = $this->get(PDO::class); 
                                        $sth = $db->prepare("SELECT * from personne p ,patient t where p.id = :vID and p.id=t.id "); 
                                        $sth->bindParam(':vID',$lidentification->id); 
                                    }else{
                                        return $response->withStatus(403);
                                    }
                                    
                                }
                                
                                
                                break;
                            case 'administrateur':
                                if($lidentification->leDroit == 3){
                                    $db = $this->get(PDO::class); 
                                    $sth = $db->prepare("SELECT * from personne p , administrateur a where p.id = :vID and p.id=a.id "); 
                                    $sth->bindParam(':vID', $lidentification->id); 
                                }else{
                                    return $response->withStatus(403);
                                }
                             
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
                       case ('infirmieres'):
                                if( $lidentification->leDroit == 3 || $lidentification->leDroit == 2){
                                    $db = $this->get(PDO::class);
                                    $sth = $db->prepare("SELECT nom, prenom FROM personne where id in (SELECT id from infirmiere)");
                                    $sth->execute();
                                    $data = $sth->fetchAll(PDO::FETCH_ASSOC);
                                    $payload = json_encode($data);
                                    $response->getBody()->write($payload);
                                    return $response->withHeader('Content-Type', 'application/json');
                                }else{
                                    return $response->withStatus(403);
                                }
                                //var_dump($lidentification->leDroit);exit();
                                
                        break;
                        case ('patients'):
                                if($lidentification->leDroit == 3 || $lidentification->leDroit == 2){
                                $db = $this->get(PDO::class);
                                $sth = $db->prepare("SELECT * from patient");
                                $sth->execute();
                                $data = $sth->fetchAll(PDO::FETCH_ASSOC);
                                $payload = json_encode($data);
                                $response->getBody()->write($payload);
                                return $response->withHeader('Content-Type', 'application/json');
                                }else{
                                    return $response->withStatus(403);
                                }
                                
                        break;
                            case ('administrateurs'):
                                if( $lidentification->leDroit == 3){
                                    $db = $this->get(PDO::class);
                                    $sth = $db->prepare("SELECT nom, prenom FROM personne where id in (Select id from administrateur)");
                                    $sth->execute();
                                    $data = $sth->fetchAll(PDO::FETCH_ASSOC);
                                    $payload = json_encode($data);
                                    $response->getBody()->write($payload);
                                    return $response->withHeader('Content-Type', 'application/json');
                                }else{
                                    return $response->withStatus(403);
                                }
                              
                           break;
                        }
                    }
            }
        }
    }
}else{
    return $response->withStatus(403);
}


});
    $app->post('/visite', function (Request $request, Response $response) {
        $lidentification = new identification();
        if($lidentification->leDroit == 3 || $lidentification->leDroit == 2 ||$lidentification->leDroit == 1){
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
        }else{
            return $response->withStatus(403);
        }
        
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
            $requete1 = $db->prepare('SELECT mp from personne_login where login = :login');
            $requete1->bindParam(':login', $arg['login']);
            $requete1->execute();
            $data = $requete1->fetchAll(PDO::FETCH_ASSOC);
            $requete2 = $db->prepare('SELECT fonction from personne f, personne_login p where p.login = :login and p.id = f.id');
            $requete2->bindParam(':login',$arg['login']);
            $requete2->execute();
            $data1 = $requete2->fetchAll(PDO::FETCH_ASSOC);
            $requete3 = $db->prepare('SELECT login from personne_login where login = :login');
            $requete3->bindParam(':login',$arg['login']);
            $requete3->execute();
            $login = $requete3->fetchAll(PDO::FETCH_ASSOC);
            $fonc = $data1[0]['fonction'];
            $requete4 = $db->prepare('SELECT id from personne_login where login = :login');
            $requete4->bindParam(':login', $arg['login']);
            $requete4->execute();
            $idi = $requete4->fetchAll(PDO::FETCH_ASSOC);
            $id = $idi[0]['id'];
            //var_dump($id);exit();
            $mdp = $data[0]['mp'];
            $bonjour = md5($arg['mdp']);
            //var_dump($data);exit();
            //var_dump($mdp);exit();
            if($bonjour === $mdp){
                $now_seconds = time();
                $jti = $id;
                //var_dump($jti);exit();
                $fonction = $fonc;
                $secret = "12345test";
                $payload = [
                    "id" => $jti,
                    "exp" => $now_seconds+(60*60),
                    "fonction" => $fonction
                ];
                $jwt = JWT::encode($payload,$secret,"HS256");
                //var_dump($jwt);exit();
                $request = $request->withAttribute('jwt',$jwt);
                return $response->withStatus(200)
                                ->withHeader('Content-Type', 'application/json')
                                ->withHeader('Authorization', 'Bearer '.$jwt);
            }else{
                if($bonjour != $data && $arg['login'] != $login){
                    return $response->withStatus(403);
                }
            }
       // var_dump($arg['login']);exit();
        //var_dump($data);exit(); 
        }
    });
    $app->get('/testjeton',function(Request $request, Response $response, $args){
        //vérification
        $verif = false;
        $lidentification=new identification();
        $verif= verifJWT($request,$lidentification);
       
        //$decodeJWT = JWT::decode($arr1[1], $secret, );

        //$authHeader = $authorizationHeader->getValue(); //line 149 error, caused $authHeader is null
        //$token      = $authHeader;
        //$jwt = $request->getAttribute('jwt');
        //$secret = "12345test";
       //var_dump($authorizationHeader);exit();
        /*if(isNull($authorizationHeader)){
            $response->getBody()->write('false');
            return $response;
        }else{
           JWT::decode($authorizationHeader,$secret,"HS256");
            $response->getBody()->write('true');
            return $response;
        }*/
        $response->withHeader('Content-Type', 'application/json')
                    ->getBody()->write(json_encode($verif));
            return $response;

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
function verifJWT($request, $lidentification){
    $now_seconds = time()+(60*60);
    $vretour=false;
    $jwt = null;
    $authorizationHeader = $request->getHeaders();

    foreach ($authorizationHeader as $name => $values) {
            if(str_contains($name,"Authorization")){
                
                $arr= explode(", ", $values[0]);
                
                $arr1=explode(" ", $arr[0]);
                
                if(strcmp($arr1[0],'Bearer')==0){

                    $jwt= $arr1[1];

                    break;
                }
            }
            
    }
    if(!is_null($jwt)){
        try{
            $decodeJWT = JWT::decode($jwt, new Key('12345test','HS256'));
            //var_dump($decodeJWT);exit();
            if($decodeJWT->exp <= $now_seconds){
                $vretour = true;
            }
            $droit=$decodeJWT->fonction;
            //var_dump($droit);exit();
            $lidentification->leDroit=$droit;
            $lidentification->id = $decodeJWT->id;
            //svar_dump($lidentification->id);exit();
        }catch (Exception $e) {

        }
    }
        
    return $vretour;
        
}
