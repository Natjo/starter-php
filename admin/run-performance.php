<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';
require_once __DIR__ . '/inc/performance.php';

$requestedProfileId = isset($_POST['profile']) && is_string($_POST['profile'])
    ? $_POST['profile']
    : (isset($_GET['profile']) && is_string($_GET['profile']) ? $_GET['profile'] : null);

$data = admin_performance_run($requestedProfileId);
$isAjax = (
    (isset($_GET['ajax']) && $_GET['ajax'] === '1')
    || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && str_contains((string) $_SERVER['HTTP_ACCEPT'], 'application/json'))
);

if ($isAjax) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'status' => $data['status'] ?? 'idle',
        'message' => $data['message'] ?? '',
        'toast' => ($data['status'] ?? 'idle') === 'success'
            ? ['type' => 'success', 'message' => 'Mesure locale terminee.']
            : ['type' => 'error', 'message' => (string) ($data['message'] ?? 'La mesure a echoue.')],
        'localHtml' => admin_render_performance_local_sections($data, admin_base_url() . '/run-performance.php'),
    ], JSON_UNESCAPED_SLASHES);
    exit;
}

header('Location: ' . admin_page_url('performance'));
exit;
