<?php
use Seguranca\Model\Users;
use \Psr\Http\Message\ServerRequestInterface as Requests;
use \Psr\Http\Message\ResponseInterface as Response;
use Seguranca\Page;

//renderiza o nome do do cliente na página de arquivos ("www.projetoseguranca.com.br/arquivos", também valida se
// o usuario esta logado para poder acessar a página)
$app->get('/arquivos',function(){
	Users::verifyLogin();
	 $data = array('data'=>array(
        'user'=>$_SESSION['desuser']
    ));
	 $page = new Page();

    $page->setTpl("arquivos",$data);
});


//faz o upload de arquivos
$app->post('/upload',function(){
	Users::verifyLogin();
	$json = file_get_contents('php://input');
	$results = json_decode($json,true);
	
	$chaveAES = Users::privateKeyDecrypt($_SESSION['optionClient'], $_SESSION['optionPrivate']);

	$fileName = Users::CryptoJSAesDecrypt($chaveAES, $results['salt'] , $results['iv'] ,$results['data']['fileName']);
	$fileContent = Users::CryptoJSAesDecrypt($chaveAES, $results['salt'] , $results['iv'] ,$results['data']['fileContent']);

	$objeto = $fileContent.$fileName;
	$hash = hash("sha256",$objeto ,false);

	if($results['hash'] == $hash){
		$user = new Users();
		$data = $user->encryptFile($fileContent, $_SESSION['User']['id']);
		Users::insertFile($fileName , $data, $_SESSION['User']['login']);
		return Users::returnSucess();
	}

});

//pega lista de arquivos 
$app->get('/listar/arquivos',function(){
	Users::verifyLogin();
	$user = new Users();
	$data = $user->listFilesUser($_SESSION['User']['id']);
	$salt = $user->generatedSalt();
	$iv = $user->generatedIV();

	$chaveAES = Users::privateKeyDecrypt($_SESSION['optionClient'], $_SESSION['optionPrivate']);

	$dataEncrypt = array();
	foreach ($data as $key => $value) {
		$aux = array('idArquivo' => base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$value['id'])),
		'idUsuario' => base64_encode($user->CryptoJSAesEncrypt($chaveAES, $salt ,$iv ,$value['idUser'])),
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


//exclui arquivos
$app->get("/arquivos/delete/{idArquivo}",function(Requests $request,Response $response,array $args){
	Users::verifyLogin();
	$user = new Users();
	$data = $user->getFileForId($args['idArquivo']);
	if($data!= null){
		$user->deleteArquivo($data['id'], $data['fileName']);
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

//faz download dos arquivos
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
	$dirUser = "./users/".$_SESSION['User']['login'];
	$dirArquivo = $dirUser . "/" . $data['fileName'];
	$file  = file_get_contents($dirArquivo);
	$fileDecrypted = $user->decryptedFile($file , $_SESSION['User']['id']);
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


//faz compartilhamento dos arquivos
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

	if($list != null || $results['hash'] == $hash){
		$user->sharingRecorder($_SESSION['User']['id'], $list, $idarquivo);
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


//checa se os arquivos foram compartilhados com sucesso
$app->get('/compartilhar/arquivo/checar',function(){
	Users::verifyLogin();
	$user = new Users();
	$data = $user->listSharing($_SESSION['User']['id']);
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

//aceita compartilhamento dos arquivos
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

//nega compartilhamento dos arquivos
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






?>