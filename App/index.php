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

//sem renderizar templates
$app->get('/',function(){
	//ao entrar na rota "www.projetoseguranca.com.br , configurada no DNS gera a chave publica e privada que sera utilizada no handshake e armazena em duas variáveis de sessão"
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


$app->get('/novocadastro',function(){
	$page = new Page();
	$page->setTpl("cadastro");
});

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

$app->post('/upload',function(){
	Users::verifyLogin();
	$json = file_get_contents('php://input');
	$results = json_decode($json,true);
	$chaveAES = Users::privateKeyDecrypt($_SESSION['optionClient'], $_SESSION['optionPrivate']);

	$fileName = Users::CryptoJSAesDecrypt($chaveAES, $results['salt'] , $results['iv'] ,$results['data']['fileName']);
	$fileContent = Users::CryptoJSAesDecrypt($chaveAES, $results['salt'] , $results['iv'] ,$results['data']['fileContent']);

	$objeto = $fileContent.$fileName;
	$hash = hash("sha256",$objeto ,false);

	if($results['hash'] === $hash){
		$user = new Users();
		$data = $user->encryptFile($fileContent, $_SESSION['User']['iduser']);
		Users::insertFile($fileName , $data, $_SESSION['User']['deslogin']);
		return Users::returnSucess();
	}

});


$app->get('/listar/arquivos',function(){
	Users::verifyLogin();
	$user = new Users();
	$data = $user->listFilesUser($_SESSION['User']['iduser']);
	$salt = $user->generatedSalt();
	$iv = $user->generatedIV();

	$chaveAES = Users::privateKeyDecrypt($_SESSION['optionClient'], $_SESSION['optionPrivate']);

	$dataEncrypt = array();
	foreach ($data as $key => $value) {
		$aux = array('idArquivo' => base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$value['idArquivo'])),
		'idUsuario' => base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$value['idUsuario'])),
		'modificationData' => base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$value['modificationData'])),
		'fileName' => base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$value['fileName'])),
		'fileType' => base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$value['fileType'])),
		'fileSize' => base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$value['fileSize']))
		 );

		array_push($dataEncrypt, $aux);	
	}
	
	return json_encode(array(
		"listaArquivos" => $dataEncrypt,
		"salt" => $salt,
		"iv" => $iv
	));
});



$app->get("/arquivos/delete/{idArquivo}",function(Requests $request,Response $response,array $args){
	Users::verifyLogin();
	$user = new Users();
	$data = $user->getFileForId($args['idArquivo']);
	if($data!= null){
		$user->deleteArquivo($data['idArquivo'], $data['fileName']);
		return json_encode(array(
			"msg" => "Sucesso"
		));
	}
	if($data === null){
		return json_encode(array(
			"msg" => "Falha"
		));
	}
});

$app->post('/arquivos/download',function(){
	Users::verifyLogin();
	$json = file_get_contents('php://input');
	$results = json_decode($json,true);
	$chaveAES = Users::privateKeyDecrypt($_SESSION['optionClient'], $_SESSION['optionPrivate']);
	
	$idArquivo = Users::CryptoJSAesDecrypt($chaveAES, $results['salt'] , $results['iv'] ,$results['data']);

	$hash1 = hash("sha256",$idArquivo ,false);
	
	if($results['hash'] != $hash1){
		return json_encode(array(
			"msg" => "Falha"
		));
	}

	$user = new Users();
	$data = $user->getFileForId($idArquivo);
	$dirUser = "./users/".$_SESSION['User']['deslogin'];
	$dirArquivo = $dirUser . "/" . $data['fileName'];
	$file  = file_get_contents($dirArquivo);
	$fileDecrypted = $user->decryptedFile($file , $_SESSION['User']['iduser']);
	$salt = $user->generatedSalt();
	$iv = $user->generatedIV();
	$chaveAES = Users::privateKeyDecrypt($_SESSION['optionClient'], $_SESSION['optionPrivate']);
	$fileCryptJs =  base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$fileDecrypted));
	$nomeArquivo =  base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$data['fileName']));

	$objeto = base64_encode($fileDecrypted).$data['fileName'];
	$hash = hash("sha256",$objeto ,false);

	return json_encode(array(
		"fileContent"=> $fileCryptJs,
		"fileName" => $nomeArquivo,
		"iv"=> $iv,
		"salt" => $salt,
		"hash" => $hash
	));
});

$app->post('/compartilhar/arquivo',function(){
	Users::verifyLogin();
	$user = new Users();
	$json = file_get_contents('php://input');
	$results = json_decode($json,true);
	
	$chaveAES = Users::privateKeyDecrypt($_SESSION['optionClient'], $_SESSION['optionPrivate']);
	$_SESSION['results'] = $results;
	$_SESSION['chaveAES'] = $chaveAES;
	$nome = Users::CryptoJSAesDecrypt($chaveAES, $results['salt'] , $results['iv'] ,$results['nomeDestinatario']);
	$idarquivo =  Users::CryptoJSAesDecrypt($chaveAES, $results['salt'] , $results['iv'] ,$results['idArquivo']);
	$list = $user->getUserForDeslogin($nome); 

	$objeto = $nome.$idarquivo;
	$hash = hash("sha256",$objeto ,false);

	if($list != null || $results['hash'] === $hash){
		$user->sharingRecorder($_SESSION['User']['iduser'], $list, $idarquivo);
	}

	if($list === null){
		return json_encode(array(
			"msg" => "Falha"
		));
	}

	return json_encode(array(
			"msg" => "Sucesso"
		));
});

$app->get('/compartilhar/arquivo/checar',function(){
	Users::verifyLogin();
	$user = new Users();
	$data = $user->listSharing($_SESSION['User']['iduser']);
	$salt = $user->generatedSalt();
	$iv = $user->generatedIV();

	$chaveAES = Users::privateKeyDecrypt($_SESSION['optionClient'], $_SESSION['optionPrivate']);

	$dataEncrypt = array();
	foreach ($data as $key => $value) {
		$aux = array('idCompartilhamento' => base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$value['idCompartilhamento'])),
		'nomeRemetente' => base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$value['nomeRemetente'])),
		'idArquivo' => base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$value['idarquivo'])),
		'fileName' => base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$value['fileName'])),
		'fileSize' => base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$value['fileSize']))
		 );

		array_push($dataEncrypt, $aux);	
	}
	
	return json_encode(array(
		"listaCompartilhamentos" => $dataEncrypt,
		"salt" => $salt,
		"iv" => $iv
	));
});

$app->post('/compartilhar/arquivo/aceitar',function(){
	Users::verifyLogin();
	$json = file_get_contents('php://input');
	$results = json_decode($json,true);
	$chaveAES = Users::privateKeyDecrypt($_SESSION['optionClient'], $_SESSION['optionPrivate']);
	$idcompartilhamento = Users::CryptoJSAesDecrypt($chaveAES, $results['salt'] , $results['iv'] ,$results['idCompartilhamento']);
	$idarquivo = Users::CryptoJSAesDecrypt($chaveAES, $results['salt'] , $results['iv'] ,$results['idArquivo']);
	
	$objeto = $idcompartilhamento.$idarquivo;
	$hash = hash("sha256",$objeto ,false);
	
	if($results['hash'] != $hash){
		return json_encode(array(
			"msg" => "Falha"
		));		
	}

	$user = new Users();
	$user->confirmSharing(true, $idcompartilhamento, $idarquivo);
	return Users::returnSucess();
});

$app->post('/compartilhar/arquivo/negar',function(){
	Users::verifyLogin();
	$json = file_get_contents('php://input');
	$results = json_decode($json,true);
	$chaveAES = Users::privateKeyDecrypt($_SESSION['optionClient'], $_SESSION['optionPrivate']);
	$idcompartilhamento = Users::CryptoJSAesDecrypt($chaveAES, $results['salt'] , $results['iv'] ,$results['idCompartilhamento']);
	$idarquivo = Users::CryptoJSAesDecrypt($chaveAES, $results['salt'] , $results['iv'] ,$results['idArquivo']);

	$objeto = $idcompartilhamento.$idarquivo;
	$hash = hash("sha256",$objeto ,false);
	
	if($results['hash'] != $hash){
		return json_encode(array(
			"msg" => "Falha"
		));		
	}

	$user = new Users();
	$user->confirmSharing(false, $idcompartilhamento, $idarquivo);
	return Users::returnSucess();
});

$app->get('/logout',function(Requests $request,Response $response,array $args){
	Users::logout();
	return $response->withRedirect('/', 301);
});

$app->get('/teste', function(Requests $request, Response $response,array $args){
		$password = password_hash("teste223", PASSWORD_DEFAULT, [ "cost"=>12]);
		$user = hash("sha512","teste223",false);
		$encryption_key = base64_encode(openssl_random_pseudo_bytes(32));
		return json_encode(array(
		"user" => $user,
		"password" => $password,
		"encryption_key" => $encryption_key
	));
});
$app->run();


 ?>
