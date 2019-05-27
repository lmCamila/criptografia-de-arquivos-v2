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
 * Está função limpa os dcampos do formulário de login.
 */
const clearFields = function () {
    const inputLogin = document.getElementById('userLogin').value = ''
    const inputPass = document.getElementById('userPassword').value = ''
}

/**
 * Está função válida os campos do formulário de login, antes de enviar os dados
 */
const validar = function (e) {
    const inputLogin = document.getElementById('userLogin')
    const inputPass = document.getElementById('userPassword')
    const inputData = document.getElementById('data')
    if (window.clientkey && window.publickey) {

        if (inputLogin.value.trim() == '') {
            alert("O login deve ser informado")
            return false;
        }
        if (inputPass.value.trim() == '') {
            alert("A senha deve ser informada")
            return false;
        }

        const dataEnd = CryptoJSAESEncrypt(inputLogin.value, inputPass.value)
        inputData.value = dataEnd
        return true
    } else {
        alert('Não foi encontrado as chaves para troca de informações.')
        return false;
    }
}
