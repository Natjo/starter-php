<?php
declare(strict_types=1);

function admin_web_vitals_cookie_name(): string
{
    return 'admin_web_vitals_collecting';
}

function admin_settings_file(): string
{
    return dirname(__DIR__) . '/data/settings.json';
}

function admin_settings_load(): array
{
    return [
        'web_vitals_collecting' => admin_web_vitals_collecting(),
    ];
}

function admin_settings_save(array $settings): void
{
    $settings;
}

function admin_web_vitals_collecting(): bool
{
    return isset($_COOKIE[admin_web_vitals_cookie_name()]) && $_COOKIE[admin_web_vitals_cookie_name()] === '1';
}

function admin_web_vitals_start(): void
{
    setcookie(admin_web_vitals_cookie_name(), '1', 0, '/', '', false, true);
    $_COOKIE[admin_web_vitals_cookie_name()] = '1';
}

function admin_web_vitals_stop(): void
{
    setcookie(admin_web_vitals_cookie_name(), '', time() - 3600, '/', '', false, true);
    unset($_COOKIE[admin_web_vitals_cookie_name()]);
}

function admin_real_vitals_reset(): void
{
    $file = dirname(__DIR__) . '/data/real-vitals.json';
    $dir = dirname($file);

    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    file_put_contents(
        $file,
        json_encode([
            'updatedAt' => null,
            'pages' => [],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
    );
}
