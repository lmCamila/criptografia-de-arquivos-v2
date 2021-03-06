<?php if(!class_exists('Rain\Tpl')){exit;}?><!DOCTYPE html>
<html lang="en">

<head>
	<title>Login</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<script src="./views/src/js/login.js"></script>
	<script src="./views/src/js/handshake.js"></script>
	<script src="./views/src/crypto-js/crypto-js.js"></script>
	<script src="./views/src/jscriptografia/jsencrypt.min.js"></script>
	<!-- Latest compiled and minified CSS -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
	<!-- jQuery library -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<!-- Latest compiled JavaScript -->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
	<link rel="stylesheet" href="./views/src/css/login.css">
</head>

<body id="image" style="background:linear-gradient(rgba(253, 253, 253, 0.3), rgba(243, 243, 243, 0.3)), url('https://images.unsplash.com/photo-1477039181047-efb4357d01bd?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=750&q=80');
background-size: 100% 100%;
background-repeat: no-repeat;">
	<div class="formLogin">
		<div class="row">
			<div class="col">
				<form action=" " name="formLogin" id="formLogin" method="post" onsubmit="return validar(this)">
					<h1 style="color:aliceblue ;text-shadow: 2px 2px rgba(0, 0, 0, 0.411)">Login</h1>
					<div class="form-group">
						<input type="text" class="form-control" id="userLogin" placeholder="Usuario">
						<input type="password" class="form-control" id="userPassword" type="password" placeholder="Senha">
					</div>
					<div class="form-group">
						<input type="hidden" name="data" id="data" value="">
						<input class="btn btn-light" type="submit" value="Entrar">
						<a class="btn btn-info" href="novocadastro" title="Novo usuário">Novo usuário</a>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>