
$.getJSON("http://www.projetoseguranca.com.br/keyencodeserver", function(data){
    $.each(data,function(key,value){
        console.log(key , value);
    });
});