<?php if(!class_exists('Rain\Tpl')){exit;}?><!DOCTYPE html>
<html lang="en">

<head>
	<title>Login</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<script src="./views/src/testeCliente/js/login.js"></script>
	<script src="./views/src/testeCliente/js/handshake.js"></script>
	<script src="./views/src/testeCliente/crypto-js/crypto-js.js"></script>
	<script src="./views/src/testeCliente/jscriptografia/jsencrypt.min.js"></script>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<!-- jQuery library -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<!-- Latest compiled JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="./views/src/testeCliente/css/login.css">
</head>

<body>
	<div class="formLogin">
		<div class="row">
			<div class="col">
				<form action=" " name="formLogin" id="formLogin" method="post" onsubmit="return validar(this)">
					<h1>Login</h1>
					<div class="form-group">
						<input type="text" class="form-control" id="userLogin" placeholder="Usuario">
						<input type="password" class="form-control" id="userPassword" type="password" placeholder="Senha">
					</div>
					<div class="form-group">
						<input type="hidden" name="data" id="data" value="">
						<input class="btn btn-primary" type="submit" value="Entrar">
						<a class="btn btn-success" href="novocadastro" title="Novo usuário">Novo usuário</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>