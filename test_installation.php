<?php
echo "Test de l'installation Symfony...\n\n";

// Charger autoload
if (!file_exists('vendor/autoload.php')) {
    die('❌ vendor/autoload.php manquant');
}

require 'vendor/autoload.php';
echo '✅ Autoload chargé\n';

// Vérifier quelques classes
$tests = [
    'Symfony\\Component\\Console\\Command\\Command',
    'Symfony\\Component\\HttpFoundation\\Request',
    'Symfony\\Component\\Routing\\Router'
];

$success = 0;
foreach ($tests as $class) {
    if (class_exists($class)) {
        echo '✅ ' . $class . '\n';
        $success++;
    } else {
        echo '⚠️  ' . $class . ' (non trouvée)\n';
    }
}

echo '\n';
if ($success >= 2) {
    echo '🎉 Installation réussie ! Vous pouvez utiliser Symfony.\n';
    echo '\nCommandes disponibles :\n';
    echo '- php bin/console\n';
    echo '- php -S localhost:8000 -t public\n';
} else {
    echo '⚠️  Installation partielle. Certains composants manquent.\n';
}
