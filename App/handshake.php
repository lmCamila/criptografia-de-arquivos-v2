<?php

use \phpseclib\Crypt\RSA;
use \Psr\Http\Message\ServerRequestInterface as Requests;
use \Psr\Http\Message\ResponseInterface as Response;
use Seguranca\Page;
//sem renderizar templates
$app->get('/',function(){
	//ao entrar na rota "www.criptografia.com.br , configurada no DNS gera a chave publica e privada que sera 
	//utilizada no handshake e armazena em duas variáveis de sessão"
	$rsa = new RSA();
	extract($rsa->createKey());
	$_SESSION['optionPublic'] = $publickey;
	$_SESSION['optionPrivate'] = $privatekey;
	
	$page = new Page();

    $page->setTpl("login");
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

//pega chave aes cliente e hash da chave e salva em var de sessão
$app->post('/keyencodecliente',function(){

	$_SESSION['optionClient'] =  base64_decode($_POST['clientkey']);
	$_SESSION['otherValue'] = $_POST['clientkey'];
	$_SESSION['hashClient'] = $_POST['hashkey'];

});


?>