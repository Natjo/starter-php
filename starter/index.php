<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once STARTER_ROOT . '/method.php';
require_once STARTER_ROOT . '/wp-compat.php';
require_once STARTER_ROOT . '/functions.php';
require_once STARTER_ROOT . '/components.php';

$uri = safe_request_path($_SERVER['REQUEST_URI'] ?? '/');
$file = page_file($uri);

if (is_file($file)) {
    if ($uri === 'admin' || str_starts_with((string) $uri, 'admin/')) {
        require $file;
        exit;
    }

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

function page_file($uri): string
{
    if ($uri === null) {
        return '';
    }

    if ($uri === 'admin' || str_starts_with($uri, 'admin/')) {
        return admin_page_file($uri);
    }

    if ($uri === 'styleguide' || str_starts_with($uri, 'styleguide/')) {
        return safe_page_file(APP_ROOT . '/' . $uri . '/index.php', APP_ROOT . '/styleguide');
    }

    return web_page_file($uri);
}

function admin_page_file($uri): string
{
    if ($uri === null) {
        return '';
    }

    $adminRoot = APP_ROOT . '/admin';
    $route = trim(substr($uri, strlen('admin')), '/');

    if ($route === '') {
        $_GET['page'] = 'dashboard';

        return safe_page_file($adminRoot . '/index.php', $adminRoot);
    }

    $simpleRoutes = [
        'performance',
        'ux',
        'wordpress',
        'accessibilite',
        'seo',
        'images',
        'webp',
    ];

    if (in_array($route, $simpleRoutes, true)) {
        $_GET['page'] = $route;
        return safe_page_file($adminRoot . '/index.php', $adminRoot);
    }

    if ($route === 'specification' || str_starts_with($route, 'specification/')) {
        $_GET['page'] = 'specifications';
        $_GET['route'] = trim(substr($route, strlen('specification')), '/');

        return safe_page_file($adminRoot . '/index.php', $adminRoot);
    }

    return '';
}

function web_page_file($uri): string
{
    if ($uri === null) {
        return '';
    }

    $file = $uri === ''
        ? WEB_ROOT . '/index.php'
        : WEB_ROOT . '/' . $uri . '/index.php';

    return safe_page_file($file, WEB_ROOT);
}

function safe_page_file(string $file, string $root): string
{
    $realWebRoot = realpath($root);
    $realFile = is_file($file) ? realpath($file) : false;

    if ($realWebRoot === false || $realFile === false) {
        return $file;
    }

    return str_starts_with($realFile, rtrim($realWebRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)
        ? $realFile
        : '';
}
