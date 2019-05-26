<?php

use Seguranca\Model\Users;
use \Psr\Http\Message\ServerRequestInterface as Requests;
use \Psr\Http\Message\ResponseInterface as Response;
use Seguranca\Page;
//pega o post feito no login , valida a chave com o hash , descriptografa a chaveA AES com a chave privada
// gerada pelo servidor , decodifica o json enviado pelo post do Cliente para o Servidor, decriptografa o 
//login e a senha e valida os dois no banco para permitir o usuário logar 
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

	return $response->withRedirect('/blank', 301);
	
});


//renderiza pagina de novo usuário
$app->get('/novocadastro',function(){
	$page = new Page();
	$page->setTpl("cadastro");
});


//cria novo usuário
$app->post('/usuario/novo',function(){
	$json = file_get_contents('php://input');
	$results = json_decode($json,true);
	$chaveAES = Users::privateKeyDecrypt($_SESSION['optionClient'], $_SESSION['optionPrivate']);
	
	$decryptedLogin = Users::CryptoJSAesDecrypt($chaveAES,$results['salt'] ,$results['iv'] ,$results['data']['login']);
	$decryptedPass = Users::CryptoJSAesDecrypt($chaveAES,$results['salt'] ,$results['iv'] ,$results['data']['pass']);
	$decryptedEmail = Users::CryptoJSAesDecrypt($chaveAES,$results['salt'] ,$results['iv'] ,$results['data']['email']);

	$objeto = $decryptedLogin.$decryptedPass.$decryptedEmail;
	$hash = hash("sha256",$objeto ,false);

	if($results['hash'] === $hash){
		Users::save($decryptedLogin,$decryptedEmail,$decryptedPass);
	}

	return Users::returnSucess();
});

//realiza logout do usuario
$app->get('/logout',function(Requests $request,Response $response,array $args){
	Users::logout();
	return $response->withRedirect('/', 301);
});

//pagina em branco
$app->get('/blank', function(){

});

?>