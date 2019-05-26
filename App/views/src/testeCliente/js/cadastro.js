/**
 * Está função inicia a captura da chave pública do servidor
 * e envio na chave AES no cliente, além de limpar os campos
 * do formulário como uma medida de precausão, para caso os
 * dados de acesso do mesmo vejam exibidos pelo browser.
 */
window.onload = function () {
    getPublicKey(sendAESKey)
    clearFields()
}

/**
 * Está função limpa os dados do forulário de cadastro
 */
const clearFields = function () {
    const inputLogin = document.getElementById('userLogin')
    const inputPass = document.getElementById('userPassword')
    const inputConfPass = document.getElementById('userConfirmPassword')
    const inputEmail = document.getElementById('userEmail')
    inputLogin.value = ''
    inputPass.value = ''
    inputConfPass.value = ''
    inputEmail.value = ''
}

/**
 * Esta função faz a validação dos dados inseridos no formulário de
 * cadastro antes de enviar para o servidor.
 */
const validar = function (event) {
    event.preventDefault()
    
    const inputLogin = document.getElementById('userLogin')
    const inputPass = document.getElementById('userPassword')
    const inputConfPass = document.getElementById('userConfirmPassword')
    const inputEmail = document.getElementById('userEmail')

    if (inputLogin.value.trim() == '') {
        alert("O login deve ser informado")
        return;
    }
    if (inputPass.value.trim() == '') {
        alert("A senha deve ser informada")
        return;
    }
    if (inputConfPass.value.trim() == '') {
        alert("A senha de confirmação deve ser informada")
        return;
    } else {
        if (inputPass.value != inputConfPass.value) {
            alert("As senhas não batem")
            return;
        }
    }
    if (inputEmail.value.trim() == '') {
        alert("O e-mail deve ser informado")
        return;
    }

    const data = {
        login: inputLogin.value,
        pass: inputPass.value,
        email: inputEmail.value
    }
    cadastrarUsuario(data)
}

const cadastrarUsuario = function(data){
    const dataEncripted = CryptoJSAESEncryptNewUser(data)
    fetch('/usuario/novo', {
        method: 'post',
        headers: {
            "Content-type": "application/json; charset=utf-8"
        },
        body: JSON.stringify(dataEncripted)
    })
    .then(response => {
        if(response.status < 300 && response.status >= 200){
            return response.text()
        } else {
            throw true
        }
    })
    .then(resp => {
        const r = JSON.parse(resp)
        if(r.msg == 'Sucesso'){
            alert('Usuário cadastrado com sucesso')
            window.location.href = '/'
        }
    })
    .catch(err => {
        alert('Erro. Não foi possível cadastrar o usuário')
    })
}