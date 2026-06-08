<?php
declare(strict_types=1);

require_once __DIR__ . '/inc/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . admin_url('performance'));
    exit;
}

$action = isset($_POST['action']) && is_string($_POST['action']) ? $_POST['action'] : '';

if ($action === 'start') {
    admin_real_vitals_reset();
    admin_web_vitals_start();

    header('Location: ' . admin_page_url('performance', ['webvitals' => 'started']));
    exit;
}

admin_web_vitals_stop();

header('Location: ' . admin_page_url('performance', ['webvitals' => 'stopped']));
exit;
