<?php
// Désactiver SSL
stream_context_set_default(array(
    'ssl' => array(
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
    )
));
include 'composer.phar';
?>
