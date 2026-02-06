<?php
// Désactiver la vérification SSL globalement
stream_context_set_default([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);

// Définir les variables d'environnement
putenv('SSL_CERT_FILE=C:\wamp64\bin\php\php8.1.31\cacert.pem');
putenv('CURL_CA_BUNDLE=C:\wamp64\bin\php\php8.1.31\cacert.pem');

// Inclure et exécuter Composer
include 'C:\wamp64\bin\php\php8.3.14\composer.phar';
