@echo off
echo ========================================
echo Installation Symfony SANS SSL
echo ========================================
echo.

REM T?l?charger composer.phar via HTTP
echo T?l?chargement de composer.phar...
powershell -Command "Invoke-WebRequest -Uri 'http://getcomposer.org/composer.phar' -OutFile 'composer.phar' -UseBasicParsing"

REM Cr?er un wrapper PHP qui d?sactive SSL
echo Cr?ation du wrapper SSL...
echo ^<?php > composer-no-ssl.php
echo // D?sactiver SSL >> composer-no-ssl.php
echo stream_context_set_default([ >> composer-no-ssl.php
echo     'ssl' => [ >> composer-no-ssl.php
echo         'verify_peer' => false, >> composer-no-ssl.php
echo         'verify_peer_name' => false, >> composer-no-ssl.php
echo         'allow_self_signed' => true >> composer-no-ssl.php
echo     ] >> composer-no-ssl.php
echo ]); >> composer-no-ssl.php
echo include 'composer.phar'; >> composer-no-ssl.php
echo ?^> >> composer-no-ssl.php

REM Configurer Composer pour HTTP uniquement
echo Configuration de Composer...
C:\wamp64\bin\php\php8.1.31\php.exe composer-no-ssl.php config -g repos.packagist composer http://repo.packagist.org
C:\wamp64\bin\php\php8.1.31\php.exe composer-no-ssl.php config -g secure-http false
C:\wamp64\bin\php\php8.1.31\php.exe composer-no-ssl.php config -g disable-tls true

REM Nettoyer le cache
echo Nettoyage du cache...
C:\wamp64\bin\php\php8.1.31\php.exe composer-no-ssl.php clear-cache

REM Installer
echo Installation des d?pendances...
echo Cette op?ration peut prendre plusieurs minutes...
C:\wamp64\bin\php\php8.1.31\php.exe composer-no-ssl.php install --prefer-source --no-interaction --no-progress --ignore-platform-reqs

echo.
echo ========================================
if exist vendor (
    echo ? Installation R?USSIE !
    echo Vous pouvez maintenant utiliser votre projet Symfony.
) else (
    echo ? Installation ?CHOU?E.
    echo V?rifiez les messages d'erreur ci-dessus.
)
echo ========================================
pause
