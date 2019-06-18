<?php if(!class_exists('Rain\Tpl')){exit;}?><!DOCTYPE html>
<html lang="en">

<head>
  <title>Arquivos</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <script src="./views/src/js/arquivos.js"></script>
  <script src="./views/src/js/handshake.js"></script>
  <script src="./views/src/crypto-js/crypto-js.js"></script>
  <script src="./views/src/jscriptografia/jsencrypt.min.js"></script>
  <!-- Latest compiled and minified CSS -->
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
  <!-- jQuery library -->
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <!-- Latest compiled JavaScript -->
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  <link rel="stylesheet" href="./views/src/css/arquivos.css">
</head>

<body style="background:linear-gradient(rgba(253, 253, 253, 0.3), rgba(243, 243, 243, 0.3)), url('https://images.unsplash.com/photo-1477039181047-efb4357d01bd?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&auto=format&fit=crop&w=750&q=80');
background-size: 100% 100%;
background-repeat: no-repeat;min-height: 100vh;">
  <div id="carregamento">
    <div id="barraDiv"></div>
  </div>
  <div id="compartilhar">
    <div id="formCompart">
      <div class="cabecalho">
        <h3>Compartilhar arquivo</h2>
          <button onclick="openModalCompartilhar(false, event);" class="btn btn-danger btn-xs">X</button>
      </div>
      <form method="post" name="formCompartilhar" onsubmit="return sendCompartilhamento(event);">
        <div class="form-group">
          <input onkeyup="validarUser()" type="text" class="form-control" name="loginCompart" id="loginCompart" placeholder="Compartilhar com: ">
        </div>
        <div class="form-group">
          <label for="">Escolha o arquivo: </label>
          <div id="listaArquivos" class="listaArquivos"></div>
        </div>
        <div class="form-group">
          <button class="btn btn-primary" type="submit">Compartilhar</button>
        </div>
      </form>
    </div>
  </div>
  <div id="solicitacao">
    <div id="formSolicitacao">
      <h3>Solicitação de compartilhamento</h3>
      <p id="msgSolicitacao"></p>
      <button class="btn btn-success">Aceitar</button>
      <button class="btn btn-danger">Recusar</button>
    </div>
  </div>
  <div class="container">
    <div style="background: #EEE; margin-bottom: 20px; padding-left: 50px;">
      <h1>Olá,<?php echo htmlspecialchars( $data["user"], ENT_COMPAT, 'UTF-8', FALSE ); ?></h1>
      <li><a>Meus arquivos</a></li>
      <li><a href="#" onclick="openModalCompartilhar();">Compartilhar</a></li>
      <li><a href="/logout">Sair</a></li>
    </div>
    <div class="arquivos">
      <h1>Lista de arquivos</h1>
      <div class="formEnviar">
        <input type="file" name="file" id="file">
        <input class="btn btn-warning btn-xs" type="button" onclick="sendFile()" value="Enviar arquivo">
      </div>
      <div id="divAlert" class="alert fade in">
        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
      </div>
      <div class="table-responsive">
        <table id="tableFiles" class="table table-striped table-hover">
          <tr>
            <th>Nome</th>
            <th class="ocultar">Data de Modificação</th>
            <th class="ocultar">Extensão</th>
            <th>Tamanho</th>
            <th>Ações</th>
          </tr>
          <tbody id="tableFilesData">

          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div id="arquivo"></div>
  <div id="arquivo2"></div>
</body>

</html>