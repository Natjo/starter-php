<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';

$pages = [
    'dashboard' => __DIR__ . '/pages/dashboard.php',
    'performance' => __DIR__ . '/pages/performance.php',
    'ux' => __DIR__ . '/pages/ux.php',
    'specifications' => __DIR__ . '/pages/specifications.php',
    'wordpress' => __DIR__ . '/pages/wordpress.php',
    'accessibilite' => __DIR__ . '/pages/accessibilite.php',
    'seo' => __DIR__ . '/pages/seo.php',
    'images' => __DIR__ . '/pages/images.php',
];

$requestedPage = isset($_GET['page']) && is_string($_GET['page']) && $_GET['page'] !== ''
    ? $_GET['page']
    : '';
$requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
$requestPath = str_replace('\\', '/', is_string($requestPath) ? $requestPath : '');

if (($requestPath === '/admin/index.php' || $requestPath === '/admin/index.php/') && $requestedPage === '') {
    header('Location: ' . admin_page_url('dashboard'));
    exit;
}

$page = $requestedPage !== ''
    ? $requestedPage
    : 'dashboard';

if (!isset($pages[$page])) {
    http_response_code(404);
    $page = 'dashboard';
}

$view = require $pages[$page];

if (!is_array($view)) {
    $view = [];
}

admin_render_layout(array_replace([
    'title' => 'Admin',
    'page' => $page,
    'heading' => 'Admin',
    'intro' => '',
    'content' => '',
], $view));
