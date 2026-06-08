<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!admin_web_vitals_collecting()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Web Vitals disabled']);
    exit;
}

$payload = json_decode((string) file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'message' => 'Invalid payload']);
    exit;
}

$path = isset($payload['path']) && is_string($payload['path']) && $payload['path'] !== '' ? $payload['path'] : '/';
$url = isset($payload['url']) && is_string($payload['url']) ? $payload['url'] : $path;
$metrics = is_array($payload['metrics'] ?? null) ? $payload['metrics'] : [];
$timestamp = date(DATE_ATOM);

$file = dirname(__DIR__) . '/admin/data/real-vitals.json';
$defaults = [
    'updatedAt' => null,
    'pages' => [],
];

$existing = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;
$data = is_array($existing) ? array_replace_recursive($defaults, $existing) : $defaults;

$entry = is_array($data['pages'][$path] ?? null) ? $data['pages'][$path] : [
    'label' => $path === '/' ? 'Accueil' : $path,
    'path' => $path,
    'url' => $url,
    'samples' => 0,
    'updatedAt' => null,
    'metrics' => [
        'lcp' => null,
        'cls' => null,
        'inp' => null,
    ],
];

$entry['url'] = $url;
$entry['samples'] = (int) $entry['samples'] + 1;
$entry['updatedAt'] = $timestamp;

foreach (['lcp', 'cls', 'inp'] as $metric) {
    $value = $metrics[$metric] ?? null;
    if (is_numeric($value)) {
        $entry['metrics'][$metric] = match ($metric) {
            'lcp' => round(((float) $value) / 1000, 2),
            'cls' => round((float) $value, 3),
            default => round((float) $value),
        };
    }
}

$data['updatedAt'] = $timestamp;
$data['pages'][$path] = $entry;

$dir = dirname($file);
if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}

file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo json_encode(['ok' => true]);
