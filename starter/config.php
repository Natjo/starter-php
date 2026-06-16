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

if (!function_exists('starter_image_size_position')) {
    function starter_image_size_position(array $crop): string
    {
        $horizontal = strtolower((string) ($crop[0] ?? 'center'));
        $vertical = strtolower((string) ($crop[1] ?? 'center'));

        if (!in_array($horizontal, ['left', 'center', 'right'], true)) {
            $horizontal = 'center';
        }

        if (!in_array($vertical, ['top', 'center', 'bottom'], true)) {
            $vertical = 'center';
        }

        return match ($vertical . '-' . $horizontal) {
            'top-left' => 'top-left',
            'top-center' => 'top',
            'top-right' => 'top-right',
            'center-left' => 'left',
            'center-right' => 'right',
            'bottom-left' => 'bottom-left',
            'bottom-center' => 'bottom',
            'bottom-right' => 'bottom-right',
            default => 'center',
        };
    }
}

if (!function_exists('add_image_size')) {
    function add_image_size(string $name, int $width, int $height, bool|array $crop = false): void
    {
        $name = preg_replace('/[^A-Za-z0-9_-]/', '', $name);

        if ($name === '' || $width <= 0 || $height <= 0) {
            return;
        }

        $position = 'center';
        if (is_array($crop)) {
            $position = starter_image_size_position($crop);
        }

        $GLOBALS['starter_image_sizes'][$name] = [
            'width' => $width,
            'height' => $height,
            'fit' => $crop === false ? 'contain' : 'cover',
            'position' => $position,
        ];
    }
}

if (!function_exists('starter_registered_image_sizes')) {
    function starter_registered_image_sizes(): array
    {
        $sizes = $GLOBALS['starter_image_sizes'] ?? [];

        return is_array($sizes) ? $sizes : [];
    }
}


add_image_size("130x87", 130, 87, ['center', 'center']);
