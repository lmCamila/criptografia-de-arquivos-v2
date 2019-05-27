# Criptografia-arquivos
A ideia deste projeto é desenvolver um sistema que permite usuários gravarem dados em um servidor não conﬁável. O servidor não deve ser capaz de observar os dados 4 enviados por usuários e também não deve ser capaz de corromper os arquivos enviados sem que isto seja notado. O sistema deve permitir a coexistência de diferentes usuários que podem compartilhar arquivos entre si. Para cada arquivo deve ser possível controlar o conjunto de usuários que podem ler e/ou escrever para aquele arquivo.

## Executar
Adicione os seguintes códigos aos seguintes arquivos : A pasta do projeto deve estar em C:. C:\Windows\System32\drivers\etc Arquivo: hosts 


    127.0.0.1	http://www.criptografia.com.br

Em C:\xampp\apache\conf\extra Arquivo:httpd-vhosts.conf
adicionar:



    <VirtualHost *:80>
        ServerAdmin webmaster@criptografia.com.br
        DocumentRoot "C:/criptografia-de-arquivos-v2/App"
        ServerName www.criptografia.com.br
        ErrorLog "logs/dummy-host2.example.com-error.log"
        CustomLog "logs/dummy-host2.example.com-access.log" common
        <Directory "C:/criptografia-de-arquivos-v2/App">
            Require all granted
            RewriteEngine On
            RewriteCond %{REQUEST_FILENAME} !-d
            RewriteCond %{REQUEST_FILENAME} !-f
            RewriteRule ^ index.php [QSA,L]
        </Directory>
    </VirtualHost>

