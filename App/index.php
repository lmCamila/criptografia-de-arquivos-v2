<?php 
//não deixa a variavel de sessão com o nome padrão
session_name(hash("sha512",'seg'.$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'],false));
//sessão irá expirar em 15 minutos de inatividade
session_cache_expire(15);
//inicia sessão
session_start();

require_once("vendor/autoload.php");

use \Seguranca\DB\Sql;
use Seguranca\Model\Users;
use Seguranca\Page;
use \phpseclib\Crypt\RSA;
use Slim\Http\Request;
use \Psr\Http\Message\ServerRequestInterface as Requests;
use \Psr\Http\Message\ResponseInterface as Response;
// instancia slim
$app =  new \Slim\App([
	'settings'=>[
		'displayErrorDetails'=>true]
	]);

require_once("handshake.php");
require_once("users.php");
require_once("arquivos.php");

$app->run();


 ?>
