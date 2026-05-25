<?php
declare(strict_types=1);

define('APP_ROOT', __DIR__);
define('WEB_ROOT', APP_ROOT . '/dist');
define('ASSETS_ROOT', WEB_ROOT . '/assets');

$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
$baseUrl = $basePath === '' ? '' : $basePath;
define('THEME_ASSETS', $baseUrl . '/dist/assets/');

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

$file = $uri === ''
    ? WEB_ROOT . '/index.php'
    : WEB_ROOT . '/' . $uri . '/index.php';

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/components.php';
    
if (is_file($file)) {
    require $file;
    exit;
}

http_response_code(404);
$notFound = WEB_ROOT . '/404/index.php';

if (is_file($notFound)) {
    require $notFound;
    exit;
}

echo '404';
