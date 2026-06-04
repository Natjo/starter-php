<?php
declare(strict_types=1);

define('STARTER_ROOT', __DIR__);
define('APP_ROOT', dirname(__DIR__));
define('WEB_ROOT', APP_ROOT . '/dist');
define('ASSETS_ROOT', WEB_ROOT . '/assets');
define('UPLOADS_ROOT', WEB_ROOT . '/uploads');
define('ENV_LOCAL', isset($_SERVER['SERVER_NAME']) && false !== strrpos($_SERVER['SERVER_NAME'], '.code'));

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath = $scriptName === '' ? '' : rtrim(str_replace('\\', '/', dirname($scriptName)), '/');
$baseUrl = $basePath === '' ? '' : $basePath;
define('THEME_ASSETS', $baseUrl . '/dist/assets/');
define('THEME_UPLOADS', $baseUrl . '/dist/uploads/');

require_once STARTER_ROOT . '/method.php';
require_once STARTER_ROOT . '/wp-compat.php';
require_once STARTER_ROOT . '/functions.php';
require_once APP_ROOT . '/components.php';

$uri = safe_request_path($_SERVER['REQUEST_URI'] ?? '/');
$file = dist_page_file($uri);

if (is_file($file)) {
    ob_start();
    require $file;
    $content = ob_get_clean();
    ob_start();
    require STARTER_ROOT . '/layout.php';
    echo starter_render_dist_style_placeholders(ob_get_clean());
    exit;
}

http_response_code(404);
$notFound = WEB_ROOT . '/404/index.php';

if (is_file($notFound)) {
    ob_start();
    require $notFound;
    $content = ob_get_clean();
    ob_start();
    require STARTER_ROOT . '/layout.php';
    echo starter_render_dist_style_placeholders(ob_get_clean());
    exit;
}

echo '404';

function safe_request_path($requestUri)
{
    $path = parse_url((string) $requestUri, PHP_URL_PATH);
    $path = rawurldecode(is_string($path) ? $path : '/');
    $path = trim(preg_replace('#/+#', '/', str_replace('\\', '/', $path)), '/');

    if ($path === '') {
        return '';
    }

    if (str_contains($path, "\0") || in_array('..', explode('/', $path), true)) {
        return null;
    }

    return preg_match('#^[A-Za-z0-9_/-]+$#', $path) ? $path : null;
}

function dist_page_file($uri)
{
    if ($uri === null) {
        return '';
    }

    $file = $uri === ''
        ? WEB_ROOT . '/index.php'
        : WEB_ROOT . '/' . $uri . '/index.php';

    $realWebRoot = realpath(WEB_ROOT);
    $realFile = is_file($file) ? realpath($file) : false;

    if ($realWebRoot === false || $realFile === false) {
        return $file;
    }

    return str_starts_with($realFile, rtrim($realWebRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)
        ? $realFile
        : '';
}
