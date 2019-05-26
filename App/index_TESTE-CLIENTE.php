<?php 
//não deixa a variavel de sessão com o nome padrão
session_name(hash("sha512",'seg'.$_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'],false));
//sessão irá expirar em 15 minutos de inatividade
session_cache_expire(15);
//inicia sessão
session_start();



require_once("vendor/autoload.php");

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

//sem renderizar templates
$app->get('/',function(){
	//ao entrar na rota "www.projetoseguranca.com.br , configurada no DNS gera a chave publica e privada que sera utilizada no handshake e armazena em duas variáveis de sessão"
	$rsa = new RSA();
	extract($rsa->createKey());
	$_SESSION['optionPublic'] = $publickey;
	$_SESSION['optionPrivate'] = $privatekey;
	$_SESSION["arquivos"] = array(
		array(
			"name" => "Arquivo 1",
			"modificationData" => "2018-12-10",
			"type" => "file",
			"ext" => "jpeg",
			"size" => "1234234"
		),
		array(
			"name" => "Arquivo 2",
			"modificationData" => "2018-12-10",
			"type" => "file",
			"ext" => "jpeg",
			"size" => "1234234"
		)
	);
	
	$page = new Page();

    $page->setTpl("login");
});

$app->get('/novocadastro', function(){
	$page = new Page();
    $page->setTpl("cadastro");
});

$app->get('/teste', function(){
	header('Content-Type: application/json; charset=utf-8');
	return json_encode($_SESSION["teste"]);
});

$app->post('/usuario/novo', function(Requests $request){
	$json = file_get_contents('php://input');
	$_SESSION["teste"] = json_decode($json);
	return json_encode(array(
		"msg" => "Sucesso"
	));
});

$app->post('/upload', function(){
	$json = file_get_contents('php://input');
	$_SESSION["teste"] = json_decode($json);
	return json_encode(array(
		"msg" => "Sucesso"
	));
});

$app->get('/listar/arquivos', function(){
	if($_SESSION["arquivos"] != null){
		return json_encode($_SESSION["arquivos"]);
	}
});

$app->get('/listar/usuarios', function(){
	return json_encode(array(
		"camila",
		"renan",
		"canderley",
		"admin"
	));
});

$app->delete('/arquivo/delete/', function(){
	return json_encode(array(
		"msg" => "Sucesso"
	));
});

$app->post('/compartilhar/arquivo', function(){
	$retorno = array(
		'msg' => "Sucesso"
	);
	return json_encode($retorno);
});

$app->post('/compartilhar/arquivo/negar', function(){
	$retorno = array(
		'msg' => "Sucesso"
	);
	return json_encode($retorno);
});

$app->post('/compartilhar/arquivo/aceitar', function(){
	$retorno = array(
		'msg' => "Sucesso"
	);
	return json_encode($retorno);
});

$app->get('/compartilhar/arquivo/checar', function(){
	$retorno = array(
		"existe" => false, // <--- TESTAR NOTIFICAÇÕES = TRUE
		"idConvite" => 1,
		"user" => "mirgs1234",
		"arquivo" => "trabalhoSeg.pptx"
	);
	return json_encode($retorno);
});

//gera um json com a chave publica que será usada no Cliente
$app->get('/keyencodeserver',function(Requests $request,Response $response,array $args){
	
	$response->withHeader('Content-Type', 'application/json');
	//array que vai ser usado pelo json contendo chave publica e vetor de inicialização 
	$data = array('chave' => $_SESSION['optionPublic']);
	//json que sera usado pelo js
    $response->write(json_encode($data));
    return $response;
	
});

// essa rota foi um teste , não precisa incluir no relatório
$app->get('/keyencodecliente',function(){
	var_dump( $_SESSION['optionClient']);
	});

//pega chave aes cliente e hash da chave e salva em var de sessão
$app->post('/keyencodecliente',function(){

	$_SESSION['optionClient'] =  base64_decode($_POST['clientkey']);
	$_SESSION['otherValue'] = $_POST['clientkey'];
	$_SESSION['hashClient'] = $_POST['hashkey'];

	});

//pega o post feito no login , valida a chave com o hash , descriptografa a chaveA AES com a chave privada gerada pelo servidor , decodifica o json enviado pelo post do Cliente para o Servidor, decriptografa o login e a senha e valida os dois no banco para permitir o usuário logar 
$app->post('/',function(Requests $request,Response $response,array $args){
	if(hash("sha256",$_SESSION['otherValue'] ,false)!=$_SESSION['hashClient']){
		throw new \Exception("Erro no handshake,chave não integra");
	}
		
	$chaveAES = Users::privateKeyDecrypt($_SESSION['optionClient'], $_SESSION['optionPrivate']);
	
	$results = json_decode($_POST['data'],true);

	$decryptedLogin = Users::CryptoJSAesDecrypt($chaveAES,$results['salt'] ,$results['iv'] ,$results['login']);
	$decryptedPass = Users::CryptoJSAesDecrypt($chaveAES,$results['salt'] ,$results['iv'] ,$results['pass']);
	$_SESSION['desuser'] = $decryptedLogin;
	$user = new Users();
	$user->login($decryptedLogin , $decryptedPass);

	return $response->withRedirect('/arquivos', 301);
	
});

//renderiza o nome do do cliente na página de arquivos ("www.projetoseguranca.com.br/arquivos", também valida se o usuario esta logado para poder acessar a página)
$app->get('/arquivos',function(){
	Users::verifyLogin();
	 $data = array('data'=>array(
        'user'=>$_SESSION['desuser']
    ));
	 $page = new Page();

    $page->setTpl("arquivos",$data);
});

$app->run();


 ?>