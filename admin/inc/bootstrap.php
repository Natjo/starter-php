<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/starter/config.php';
require_once __DIR__ . '/layout.php';
require_once __DIR__ . '/performance.php';
require_once __DIR__ . '/performance-view.php';
require_once __DIR__ . '/settings.php';

function admin_escape(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function admin_base_url(): string
{
    $requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $requestPath = str_replace('\\', '/', is_string($requestPath) ? $requestPath : '');

    if ($requestPath === '/admin' || str_starts_with($requestPath, '/admin/')) {
        return '/admin';
    }

    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = $scriptName === '' ? '' : rtrim(dirname($scriptName), '/');

    if ($basePath === '.' || $basePath === '/') {
        return '';
    }

    return $basePath;
}

function admin_url(string $page = 'dashboard'): string
{
    return admin_page_url($page);
}

function admin_pretty_page_routes(): array
{
    return [
        'dashboard' => '',
        'performance' => 'performance',
        'ux' => 'ux',
        'specifications' => 'specification',
        'wordpress' => 'wordpress',
        'accessibilite' => 'accessibilite',
        'seo' => 'seo',
        'images' => 'images',
        'webp' => 'webp',
        'icons' => 'icons',
    ];
}

function admin_page_url(string $page = 'dashboard', array $params = []): string
{
    $baseUrl = admin_base_url();

    if ($page === 'dashboard' && $params === []) {
        return $baseUrl . '/';
    }

    if ($page === 'specifications') {
        $section = isset($params['section']) && is_string($params['section']) ? $params['section'] : '';

        return admin_specification_url($section);
    }

    if ($params === []) {
        $route = admin_pretty_page_routes()[$page] ?? null;
        if (is_string($route)) {
            return $route === '' ? $baseUrl . '/' : $baseUrl . '/' . $route;
        }
    }

    $query = array_merge(['page' => $page], $params);

    return $baseUrl . '/index.php?' . http_build_query($query);
}

function admin_specification_routes(): array
{
    return [
        'cadrage' => 'cadrage',
        'cadrage-contexte' => 'cadrage/contexte',
        'cadrage-objectif' => 'cadrage/objectif',
        'cadrage-perimetre' => 'cadrage/perimetre',
        'prototype' => 'prototype',
        'prototype-arborescence' => 'prototype/arborescence',
        'prototype-wireframes' => 'prototype/wireframes',
        'prototype-wireflows' => 'prototype/wireflows',
        'prototype-contenus' => 'prototype/contenus',
        'prototype-priorisation' => 'prototype/priorisation',
        'specifications-techniques' => 'specifications-techniques',
        'spec-tech-pages' => 'specifications-techniques/pages',
        'spec-tech-composants' => 'specifications-techniques/composants',
        'spec-tech-strates' => 'specifications-techniques/strates',
        'spec-tech-css-hydratation' => 'specifications-techniques/css-hydratation',
    ];
}

function admin_specification_url(string $section = ''): string
{
    $baseUrl = admin_base_url() . '/specification/';
    if ($section === '') {
        return $baseUrl;
    }

    $route = admin_specification_routes()[$section] ?? '';

    return $route !== '' ? $baseUrl . $route : $baseUrl;
}

function admin_specification_section_from_route(string $route): string
{
    $route = trim($route, '/');
    if ($route === '') {
        return '';
    }

    $section = array_search($route, admin_specification_routes(), true);

    return is_string($section) ? $section : '';
}

function admin_asset_url(string $file): string
{
    return admin_base_url() . '/assets/' . ltrim($file, '/');
}
