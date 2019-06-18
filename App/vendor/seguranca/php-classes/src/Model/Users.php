<?php 
namespace Seguranca\Model;

use \Seguranca\Model;
use \Seguranca\DB\Sql;
use \phpseclib\Crypt\RSA;

class Users extends Model{
	const SESSION = "User";
// realiza o login validando as informações envidas pelo usuario
	public function login($login , $password){
		$sql = new Sql();
		//realiza hash do login do usuario 
		$user = hash("sha512",$login,false);
		//recupera dados do usuario do servidor
		$results = $sql->select("SELECT * FROM  users WHERE login = :LOGIN",array(
			':LOGIN'=>$user
		));
		//verifica se foi retornado algum usúario do banco
		if(count($results) === 0){
			throw new \Exception("Usuário inexistente ou senha inválida.");
		}
		// se retornado algum valor , pega o primeiro valor do array e atribui a variavel data
		$data = $results[0];

		// se a senha estiver correta ele criará um usúario, atribuira os valores retornados na variavel data e seta na sessão
		if(password_verify($password,$data["password"]) === true){
			$user = new Users();
			$user->setData($data);
			$_SESSION[Users::SESSION] = $user->getValues();
			
		}else{
			throw new \Exception("Usuário inexistente ou senha inválida.");
			
		}

		$dir = "./users/".$data['login'];
		if(!is_dir($dir)){
			mkdir($dir);
		}
	}
	//verifica se o usuario está logado
	public static function verifyLogin(){
		//verifica se o usuario possui uma sessão aberta 
		if(!isset($_SESSION[Users::SESSION])
			|| !$_SESSION[Users::SESSION]
			|| !(int)$_SESSION[Users::SESSION]["id"] > 0		
		){
			header("Location: /");
			exit();
		}
	}

	public static function logout()
	{
		$_SESSION[Users::SESSION] = NULL;
	}
 // esta função ainda não está sendo utilizada ainda 
	public static function save($login,$email,$pass){

		$password = password_hash($pass, PASSWORD_DEFAULT, [ "cost"=>12]);
		$user = hash("sha512",$login,false);
		$encryption_key = base64_encode(openssl_random_pseudo_bytes(32));

		$sql = new Sql();
		$resutls = $sql->select("SELECT * FROM  users WHERE login = :LOGIN",array(
			':LOGIN'=>$user));

		if(count($resutls) === 0){
			$sql->query("INSERT INTO users(dtregister, password, email, login, AESKey) VALUES ( NOW(), :PASSWORD, :EMAIL, :LOGIN, :AESKey)",array(
			':LOGIN'=> $user,
			':PASSWORD'=> $password,
			':EMAIL'=>$email,
			':AESKey'=>$encryption_key
		));
		}else{
			throw new \Exception("Usuário ja existente");
			
		}
	}

	public static function returnSucess(){
		return json_encode(array(
			"msg" => "Sucesso"
		));
	}
	//esta função descriptografa a chave do AES com a chave privada ( utiliza RSA)
	public static function privateKeyDecrypt($data, $privatekey){
		$clientkey = base64_decode($data);
		openssl_private_decrypt($clientkey, $decrypted, $privatekey ,OPENSSL_PKCS1_PADDING);
		return $decrypted ; 
		
	}

	//esta função decriptografa as informações enviadas criptografadas pelo AES
	public static function CryptoJSAesDecrypt($passphrase, $salt ,$iv ,$data){
		$salt1 = hex2bin($salt);
		$iv1 = hex2bin($iv);
	    $info = base64_decode($data);
	   
	    $iterations = 999;

	    $key = hash_pbkdf2("sha512", $passphrase, $salt1, $iterations, 64);
	   
	    $decrypted= openssl_decrypt($info , 'aes-256-cbc', hex2bin($key), OPENSSL_RAW_DATA, $iv1);
	   
	    return $decrypted;

	}
	 public static function insertFile($fileName , $data , $deslogin){
		
		$dirUser = "./users/".$deslogin;
		$dir = $dirUser . "/" . $fileName;
		file_put_contents($dir, $data);
		$type = explode(".", $fileName);
		$fileType = $type[1];
		$tamanho = Users::FileSizeConvert(filesize($dir));
		$sql = new Sql();
		$sql->query("INSERT INTO files (idUser , modificationData , fileName, fileType, fileSize) VALUES ( :IDUSER, NOW(), :FILENAME, :FILETYPE, :FILESIZE)",array(
			':IDUSER'=> $_SESSION['User']['id'],
			':FILENAME'=>$fileName,
			':FILETYPE'=>$fileType,
			':FILESIZE'=>$tamanho
		));
			
	}

	public static function FileSizeConvert($bytes){
    	$bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

    	foreach($arBytes as $arItem){
        	if($bytes >= $arItem["VALUE"]){
            	$result = $bytes / $arItem["VALUE"];
            	$result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
            break;
        	}
    	}
    	return $result;
	}

	public  function encryptFile($fileContent,$iduser){
		$file = explode(',', $fileContent);
		$dataDecrypted = base64_decode($file[1]);
		$sql = new Sql();
		$data = $sql->select("SELECT * FROM  users WHERE id = :id",array(
			':id'=>$_SESSION['User']['id'])); 
		$results = $data[0];
		$chaveAES = $results['AESKey'];
		$encryption_key = base64_decode($chaveAES);
		$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
		$code = openssl_encrypt($dataDecrypted,'aes-256-cbc',$encryption_key,0,$iv);
		$result = base64_encode($code.'::'.$iv);
		return $result;
	}

	public function decryptedFile($data ,$iduser){
		$sql = new Sql();
		$chave = $sql->select("SELECT * FROM  users WHERE id = :iduser",array(
			':iduser'=>$iduser)); 
		//$result= $sql->select("SELECT * FROM  arquivos WHERE idarquivo = 3");
		//$arquivo = $result[0]['fileName'];
		$results = $chave[0];
		$chaveAES = $results['AESKey'];
		$encryption_key = base64_decode($chaveAES);
 		list($code , $iv) = explode('::', base64_decode($data), 2);
 		$file = openssl_decrypt($code, 'aes-256-cbc', $encryption_key, 0, $iv);
 		//$dirUser = "./users/".$_SESSION['User']['deslogin'];
		//$dir = $dirUser . "/" ."decrypted".$arquivo;
		//file_put_contents($dir, $file);
 		return $file;

	}

	public function generatedSalt(){
		$salt = openssl_random_pseudo_bytes(256);
		return bin2hex($salt);
	}
	
	public function generatedIV(){
    	$iv = openssl_random_pseudo_bytes(16);
    	return bin2hex($iv);
	}

	public function CryptoJSAesEncrypt($passphrase, $salt ,$iv ,$data){
		$salt1 = hex2bin($salt);
		$iv1 = hex2bin($iv);
	    //$info = base64_decode($data);
	   
	    $iterations = 999;

	    $key = hash_pbkdf2("sha512", $passphrase, $salt1, $iterations, 64);
	   
	    $encrypted= openssl_encrypt($data , 'aes-256-cbc', hex2bin($key), OPENSSL_RAW_DATA, $iv1);
	   
	    return $encrypted;
	}

	public function listFilesUser($iduser){
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM files where idUser = :iduser",array(
			':iduser' => $iduser
		));
		return $results;
	}

	public function getFileForId($idarquivo){
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM files WHERE id = :idarquivo",array(
			':idarquivo' => $idarquivo
		));
		$data = null;
		foreach ($results as $key => $value) {
			$data = $value;
		}
		return $data;
	}

	public function deleteArquivo($idarquivo, $filename){	
		$dirUser = "./users/".$_SESSION['User']['login'];
		$dirArquivo = $dirUser . "/" . $filename;
		unlink($dirArquivo);
		$sql = new Sql();
		$sql->query("DELETE FROM files WHERE id = :idarquivo",array(
			":idarquivo" => $idarquivo
		));
	}

	public function getUserForDeslogin($deslogin){
		$user = hash("sha512",$deslogin,false);
		$sql = new Sql();
		$results = $sql->select("SELECT * FROM users WHERE login = :deslogin",array(
			":deslogin" => $user
		));
		foreach ($results as $key => $value) {
			$data = $value;
		}
		return $data['id'];
	}

	public function sharingRecorder($remetente, $destinatario, $arquivo){
		$sql = new Sql();
		$sql->query("INSERT INTO shared_files(idSender, idRecipient, idFile, senderName) VALUES(:idRemetente, :idDestinatario, :idArquivo,:nomeremetente)",array(
			":idRemetente" => $remetente, 
			":idDestinatario" => $destinatario, 
			":idArquivo" => $arquivo,
			":nomeremetente" =>$_SESSION["desuser"]
		));
	}

	 public function cryptForSharing($idRemetente , $idDestinatario, $idarquivo){
	 	//decriptografar
	 	$sql = new Sql();
	 	$chave = $sql->select("SELECT * FROM  users WHERE id = :iduser",array(
	 		':iduser'=>$idRemetente));
	 	$dest = $sql->select("SELECT * FROM  users WHERE id = :iduser",array(
	 		':iduser'=>$idDestinatario));
	 	$data = $this->getFileForId($idarquivo);
	 	$results = $chave[0];
	 	$destinatario = $dest[0];
	 	$dirUser = "./users/".$results['login'];
	 	$dirArquivo = $dirUser . "/" . $data['fileName'];
	 	$fileContent  = file_get_contents($dirArquivo); 

	 	$file = $this->decryptedFile($fileContent ,$idRemetente);

	 	$fileEncrypted = $this->encryptFile($file,$idDestinatario);
	 	$results = $data[0];
	 	$chaveAES = $destinatario['AESKey'];
	 	$encryption_key = base64_decode($chaveAES);
	 	$iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
	 	$code = openssl_encrypt($ile,'aes-256-cbc',$encryption_key,0,$iv);
	 	$fileEncrypted = base64_encode($code.'::'.$iv);

	 	$this->insertFile($data['fileName'] , $fileEncrypted , $destinatario['deslogin']);
	 }

	 public function confirmSharing($confirm , $idcompartilhamento , $idarquivo){
	 	$sql = new Sql();
	 	$sql->query("UPDATE shared_files SET status = :status WHERE id = :idcompartilhamento" , array(
	 		":status" =>$confirm,
	 		":id" =>$idcompartilhamento
	 	));
	 	if($confirm === true){
	 		$results = $sql->select("SELECT * FROM shared_files WHERE id = :idcompartilhamento",array(
	 			":idcompartilhamento" =>$idcompartilhamento
	 		));
	 		$result = $results[0];
	 		$this->cryptForSharing($result['idSender'] , $result['idRecipient'], $result['idFile']);
	 		$sql->query("DELETE FROM shared_files WHERE id = :idcompartilhamento",array(
	 			":idcompartilhamento"=>$idcompartilhamento
	 		));
	 	}
	 	if($confirm ===false){
	 		$sql->query("DELETE FROM shared_files WHERE id = :idcompartilhamento",array(
	 			":idcompartilhamento"=>$idcompartilhamento
	 		));
	 	}
	 }
	 public function listSharing($iduser){
	 	$sql = new Sql();
	 	$results = $sql->select("SELECT sh.id  , sh.senderName, sh.idFile, f.fileName , f.fileSize, sh.status
	 		FROM shared_files  sh  INNER JOIN files f ON sh.idFile = f.id
	 		WHERE sh.idRecipient = :idDestinatario",array(
	 		":idDestinatario" => $iduser
	 	));
	 	$aux = array();
	 	foreach ($results as $key => $value) {
	 		if($value['status'] === NULL){
	 			array_push($aux, $value);
	 		}
	 	}
	 	return $aux;
	 }
}



 ?>