<?php
// Désactiver complètement la vérification SSL
ini_set('openssl.cafile', 'C:\wamp64\bin\php\php8.1.31\cacert.pem');
ini_set('curl.cainfo', 'C:\wamp64\bin\php\php8.1.31\cacert.pem');

// Créer un contexte stream global sans vérification SSL
$GLOBALS['_no_ssl_context'] = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    ]
]);

// Redéfinir file_get_contents pour utiliser notre contexte
function no_ssl_file_get_contents($filename, $use_include_path = false, $context = null, $offset = 0, $maxlen = null) {
    if (is_string($filename) && (strpos($filename, 'https://') === 0 || strpos($filename, 'http://') === 0)) {
        if ($context === null) {
            $context = $GLOBALS['_no_ssl_context'];
        }
    }
    return call_user_func_array('file_get_contents', func_get_args());
}

// Redéfinir fopen de la même manière
function no_ssl_fopen($filename, $mode, $use_include_path = false, $context = null) {
    if (is_string($filename) && (strpos($filename, 'https://') === 0 || strpos($filename, 'http://') === 0)) {
        if ($context === null) {
            $context = $GLOBALS['_no_ssl_context'];
        }
    }
    return call_user_func_array('fopen', func_get_args());
}

// Remplacer les fonctions originales
rename_function('file_get_contents', 'original_file_get_contents');
override_function('file_get_contents', '$filename, $use_include_path = false, $context = null, $offset = 0, $maxlen = null', '
    return no_ssl_file_get_contents($filename, $use_include_path, $context, $offset, $maxlen);
');

rename_function('fopen', 'original_fopen');
override_function('fopen', '$filename, $mode, $use_include_path = false, $context = null', '
    return no_ssl_fopen($filename, $mode, $use_include_path, $context);
');

// Exécuter Composer
include "C:\wamp64\bin\php\php8.3.14\composer.phar";
