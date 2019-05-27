/**
 * Esta função gera uma chave AES aleatório de 32 bytes.
 */
const makeClientKey = function () {
    var text = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for (var i = 0; i < 32; i++) {
        text += possible.charAt(Math.floor(Math.random() * possible.length));
    }
    return text;
}

/**
 * Está função encripta dados de login e senha, gerando randomicamente 
 * um vetor de inicialização, além de utilizar o método salt para a
 * definizaçõ da chave AES de criptografia.
 */
function CryptoJSAESEncrypt(login, pass) {

    var salt = CryptoJS.lib.WordArray.random(256);
    var iv = CryptoJS.lib.WordArray.random(16);

    var key = CryptoJS.PBKDF2(window.clientkey, salt, { hasher: CryptoJS.algo.SHA512, keySize: 64 / 8, iterations: 999 });

    var encryptedLogin = CryptoJS.AES.encrypt(login, key, { iv: iv });
    var encryptedPass = CryptoJS.AES.encrypt(pass, key, { iv: iv });

    var data = {
        login: CryptoJS.enc.Base64.stringify(encryptedLogin.ciphertext),
        pass: CryptoJS.enc.Base64.stringify(encryptedPass.ciphertext),
        salt: CryptoJS.enc.Hex.stringify(salt),
        iv: CryptoJS.enc.Hex.stringify(iv)
    }

    return JSON.stringify(data);
}

function CryptoJSAESEncryptNewUser(data) {

    var salt = CryptoJS.lib.WordArray.random(256);
    var iv = CryptoJS.lib.WordArray.random(16);

    var key = CryptoJS.PBKDF2(window.clientkey, salt, { hasher: CryptoJS.algo.SHA512, keySize: 64 / 8, iterations: 999 });

    var loginEncrypted = CryptoJS.AES.encrypt(data.login, key, { iv: iv });
    var passEncrypted = CryptoJS.AES.encrypt(data.pass, key, { iv: iv });
    var emailEncrypted = CryptoJS.AES.encrypt(data.email, key, { iv: iv });

    const obj = {
        login: CryptoJS.enc.Base64.stringify(loginEncrypted.ciphertext),
        pass: CryptoJS.enc.Base64.stringify(passEncrypted.ciphertext),
        email: CryptoJS.enc.Base64.stringify(emailEncrypted.ciphertext)
    }

    return {
        data: obj,
        salt: CryptoJS.enc.Hex.stringify(salt),
        iv: CryptoJS.enc.Hex.stringify(iv),
        hash: CryptoJS.SHA256(data.login.concat(data.pass).concat(data.email)).toString()
    }
}

function CryptoJSAESEncryptSC(data) {

    var salt = CryptoJS.lib.WordArray.random(256);
    var iv = CryptoJS.lib.WordArray.random(16);
    var key = CryptoJS.PBKDF2(window.clientkey, salt, { hasher: CryptoJS.algo.SHA512, keySize: 64 / 8, iterations: 999 });

    var nomeDestinatarioEncrypted = CryptoJS.AES.encrypt(data.nomeDestinatario, key, { iv: iv });
    var idArquivoEncrypted = CryptoJS.AES.encrypt(data.idArquivo, key, { iv: iv });

    const nomeDestinatario = CryptoJS.enc.Base64.stringify(nomeDestinatarioEncrypted.ciphertext)
    const idArquivo = CryptoJS.enc.Base64.stringify(idArquivoEncrypted.ciphertext)

    return {
        nomeDestinatario: nomeDestinatario,
        idArquivo: idArquivo,
        salt: CryptoJS.enc.Hex.stringify(salt),
        iv: CryptoJS.enc.Hex.stringify(iv),
        hash: CryptoJS.SHA256(data.nomeDestinatario.concat(data.idArquivo)).toString()
    }
}

function CryptoJSAESEncryptIDs(idCompartilhamento, idArquivo) {

    var salt = CryptoJS.lib.WordArray.random(256);
    var iv = CryptoJS.lib.WordArray.random(16);
    var key = CryptoJS.PBKDF2(window.clientkey, salt, { hasher: CryptoJS.algo.SHA512, keySize: 64 / 8, iterations: 999 });

    var idCompartilhamentoEncrypted = CryptoJS.AES.encrypt(`${idCompartilhamento}`, key, { iv: iv });
    var idArquivoEncrypted = CryptoJS.AES.encrypt(`${idArquivo}`, key, { iv: iv });

    const hashIdCompartilhamento = idCompartilhamento
    const hashIdArquivo = idArquivo

    idCompartilhamento = CryptoJS.enc.Base64.stringify(idCompartilhamentoEncrypted.ciphertext)
    idArquivo = CryptoJS.enc.Base64.stringify(idArquivoEncrypted.ciphertext)

    return {
        idCompartilhamento: idCompartilhamento,
        idArquivo: idArquivo,
        salt: CryptoJS.enc.Hex.stringify(salt),
        iv: CryptoJS.enc.Hex.stringify(iv),
        hash: CryptoJS.SHA256(`${hashIdCompartilhamento}`.concat(`${hashIdArquivo}`)).toString()
    }
}

function CryptoJSAESEncryptFile(data) {

    var salt = CryptoJS.lib.WordArray.random(256);
    var iv = CryptoJS.lib.WordArray.random(16);

    var key = CryptoJS.PBKDF2(window.clientkey, salt, { hasher: CryptoJS.algo.SHA512, keySize: 64 / 8, iterations: 999 });

    var fileContentEncrypted = CryptoJS.AES.encrypt(data.content, key, { iv: iv });
    var fileNameEncrypted = CryptoJS.AES.encrypt(data.fileName, key, { iv: iv });

    const obj = {
        fileContent: CryptoJS.enc.Base64.stringify(fileContentEncrypted.ciphertext),
        fileName: CryptoJS.enc.Base64.stringify(fileNameEncrypted.ciphertext)
    }

    const objHash = data.content.concat(data.fileName)

    return {
        data: obj,
        salt: CryptoJS.enc.Hex.stringify(salt),
        iv: CryptoJS.enc.Hex.stringify(iv),
        hash: CryptoJS.SHA256(objHash).toString()
    }
}

function CryptoJSAESEncryptId(data) {

    var salt = CryptoJS.lib.WordArray.random(256);
    var iv = CryptoJS.lib.WordArray.random(16);

    var key = CryptoJS.PBKDF2(window.clientkey, salt, { hasher: CryptoJS.algo.SHA512, keySize: 64 / 8, iterations: 999 });

    var dataEncrypted = CryptoJS.AES.encrypt(data, key, { iv: iv });

    return {
        data: CryptoJS.enc.Base64.stringify(dataEncrypted.ciphertext),
        salt: CryptoJS.enc.Hex.stringify(salt),
        iv: CryptoJS.enc.Hex.stringify(iv),
        hash: CryptoJS.SHA256(`${data}`).toString()
    }
}

function CryptoJSAesDecrypt(data, salt1, iv1) {

    var encrypted = data;

    var salt = CryptoJS.enc.Hex.parse(salt1);
    var iv = CryptoJS.enc.Hex.parse(iv1);

    var key = CryptoJS.PBKDF2(window.clientkey, salt, { hasher: CryptoJS.algo.SHA512, keySize: 64 / 8, iterations: 999 });

    var decrypted = CryptoJS.AES.decrypt(encrypted, key, { iv: iv });

    return decrypted.toString(CryptoJS.enc.Utf8);
}

function CryptoJSAesDecryptFile(data, salt1, iv1) {

    var encrypted = data;

    var salt = CryptoJS.enc.Hex.parse(salt1);
    var iv = CryptoJS.enc.Hex.parse(iv1);

    var key = CryptoJS.PBKDF2(window.clientkey, salt, { hasher: CryptoJS.algo.SHA512, keySize: 64 / 8, iterations: 999 });

    var decrypted = CryptoJS.AES.decrypt(encrypted, key, { iv: iv });

    return decrypted.toString(CryptoJS.enc.Base64);
}

/**
 * Está função criptografa a chave AES gerada pelo cliente com a chave
 * pública do servidor. Esta função é utiizada para enviar a chave AES
 * para o servidor toder descriptografar os dados.
 */
const encryptPublicKey = function (data) {
    var encrypt = new JSEncrypt()
    encrypt.setPublicKey(window.publickey)
    var encryptedData = encrypt.encrypt(data)
    return encryptedData
}

const encryptAESClient = function (data) {
    return CryptoJS.AES.encrypt(data, window.clientkey, { iv: window.iv })
}

/**
 * Está função é responsável por obter a chave pública do servidor.
 * Recebe como parametro uma callback, que enviará a chave AES que
 * está sendo usado pelo clientepara o servidor.
 */
const getPublicKey = function (callback1, callback2) {
    fetch(`keyencodeserver`) // <---- mudar
        .then(
            function (response) {
                if (response.status !== 200) {
                    console.log('Looks like there was a problem. Status Code: ' +
                        response.status);
                    return;
                }
                response.json().then(function (data) {
                    publickey = data.chave // <---- mudar para data.chave
                    vi = data.vi
                    console.log('Request succeeded to public key')
                    callback1()
                    if (callback2 != undefined) {
                        callback2()
                    }
                });
            }
        )
        .catch(function (err) {
            console.log('Fetch Error :-S', err);
        });
}

/**
 * Esta função envia para o servidor a chave AES que o cliente esta
 * usando para criptografar os dados. A chave AES é criptografada com
 * a chave pública no servidor, e assim, enviada para o mesmo.
 */
const sendAESKey = function () {
    clientkey = makeClientKey()
    const encKey = encryptPublicKey(clientkey)
    const encryptedWord = CryptoJS.enc.Utf8.parse(encKey);
    const encrypted = CryptoJS.enc.Base64.stringify(encryptedWord);
    const hashKey = CryptoJS.SHA256(encrypted).toString()
    fetch('keyencodecliente', { // <---- mudar
        method: 'post',
        headers: {
            "Content-type": "application/x-www-form-urlencoded; charset=UTF-8"
        },
        body: `clientkey=${encrypted}&hashkey=${hashKey}`
    })
        .then(`{}`)
        .then(function (data) {
            window.clientkey = clientkey
            window.publickey = publickey
            window.vi = vi
            console.log('Request succeeded to client key');
            console.log('Cliente key AES: ', window.clientkey)
            console.log('Public key RSA: ', window.publickey)
        })
        .catch(function (error) {
            console.log('Request failed', error);
        });
}