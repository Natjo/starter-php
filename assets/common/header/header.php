<!DOCTYPE html>
<html lang="fr">
<?php
$page_title = isset($page_title) && is_scalar($page_title) && trim((string) $page_title) !== ""
    ? trim((string) $page_title)
    : "Mon projet";
$page_description = isset($page_description) && is_scalar($page_description)
    ? trim((string) $page_description)
    : "";
$theme_color = isset($theme_color) && is_scalar($theme_color) && trim((string) $theme_color) !== ""
    ? trim((string) $theme_color)
    : "#ffffff";
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc_html($page_title) ?></title>
    <?php if ($page_description !== "") : ?>
        <meta name="description" content="<?= esc_attr($page_description) ?>">
    <?php endif; ?>
    <meta name="theme-color" content="<?= esc_attr($theme_color) ?>">
    <link rel="icon" href="<?= esc_url(dist_asset_url('favicon/favicon.ico')) ?>" sizes="any">
    <link rel="icon" href="<?= esc_url(dist_asset_url('favicon/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="icon" href="<?= esc_url(dist_asset_url('favicon/favicon-32x32.png')) ?>" type="image/png" sizes="32x32">
    <link rel="icon" href="<?= esc_url(dist_asset_url('favicon/favicon-16x16.png')) ?>" type="image/png" sizes="16x16">
    <link rel="apple-touch-icon" href="<?= esc_url(dist_asset_url('favicon/apple-touch-icon.png')) ?>">
    <link rel="preload" href="<?= esc_url(dist_asset_url('font/roboto.woff2')) ?>" as="font" type="font/woff2" crossorigin>
    <?php dist_critical_styles(); ?>
    <?php dist_style_placeholder(); ?>
</head>

<body>
