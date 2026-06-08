<?php
declare(strict_types=1);

if (!defined('STARTER_ROOT')) {
    // Chemin disque du dossier starter.
    define('STARTER_ROOT', __DIR__);
}

if (!defined('APP_ROOT')) {
    // Chemin disque de la racine du projet.
    define('APP_ROOT', dirname(__DIR__));
}

if (!defined('WEB_ROOT')) {
    // Chemin disque du dossier public qui contient les pages, assets et uploads.
    define('WEB_ROOT', APP_ROOT . '/web');
}

if (!defined('WEB_ASSETS_ROOT')) {
    // Chemin disque des assets generes par le builder.
    define('WEB_ASSETS_ROOT', WEB_ROOT . '/assets');
}

if (!defined('WEB_UPLOADS_ROOT')) {
    // Chemin disque des images gerees par l'admin.
    define('WEB_UPLOADS_ROOT', WEB_ROOT . '/uploads');
}

if (!defined('ENV_LOCAL')) {
    // Indique un environnement local base sur le domaine .code.
    define('ENV_LOCAL', isset($_SERVER['SERVER_NAME']) && false !== strrpos($_SERVER['SERVER_NAME'], '.code'));
}

// Base URL relative au dossier courant. Depuis l'admin, on remonte a la racine publique.
$scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = $scriptName === '' ? '' : rtrim(dirname($scriptName), '/');

if ($basePath === '.' || $basePath === '/') {
    $basePath = '';
}

if ($basePath === '/admin') {
    $basePath = '';
} elseif (str_ends_with($basePath, '/admin')) {
    $basePath = substr($basePath, 0, -strlen('/admin'));
}

if (!defined('THEME_ASSETS')) {
    // URL publique des assets generes par le builder.
    define('THEME_ASSETS', $basePath . '/web/assets/');
}

if (!defined('THEME_UPLOADS')) {
    // URL publique des images gerees par l'admin.
    define('THEME_UPLOADS', $basePath . '/web/uploads/');
}
