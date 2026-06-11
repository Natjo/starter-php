<?php
$content = isset($content) && is_scalar($content) ? (string) $content : "";
?>
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
$theme_color_dark = isset($theme_color_dark) && is_scalar($theme_color_dark) && trim((string) $theme_color_dark) !== ""
    ? trim((string) $theme_color_dark)
    : "#101923";
$admin_base = preg_replace('#/web/assets/?$#', '', rtrim((string) THEME_ASSETS, '/'));
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc_html($page_title) ?></title>
    <?php if ($page_description !== "") : ?>
        <meta name="description" content="<?= esc_attr($page_description) ?>">
    <?php endif; ?>
    <meta name="theme-color" content="<?= esc_attr($theme_color) ?>" data-light="<?= esc_attr($theme_color) ?>" data-dark="<?= esc_attr($theme_color_dark) ?>">
    <link rel="icon" href="<?= esc_url(THEME_ASSETS . 'favicon/favicon.ico') ?>" sizes="any">
    <link rel="icon" href="<?= esc_url(THEME_ASSETS . 'favicon/favicon.svg') ?>" type="image/svg+xml">
    <link rel="icon" href="<?= esc_url(THEME_ASSETS . 'favicon/favicon-32x32.png') ?>" type="image/png" sizes="32x32">
    <link rel="icon" href="<?= esc_url(THEME_ASSETS . 'favicon/favicon-16x16.png') ?>" type="image/png" sizes="16x16">
    <link rel="apple-touch-icon" href="<?= esc_url(THEME_ASSETS . 'favicon/apple-touch-icon.png') ?>">
    <link rel="preload" href="<?= esc_url(THEME_ASSETS . 'fonts/SequelSansLight.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= esc_url(THEME_ASSETS . 'fonts/SequelSansBook.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="<?= esc_url(THEME_ASSETS . 'fonts/SequelSansHeavy.woff2') ?>" as="font" type="font/woff2" crossorigin>
    <script>
        (() => {
            try {
                const legacyTheme = localStorage.getItem("theme");
                const lightdark = localStorage.getItem("lightdark");

                if (lightdark === "light" || lightdark === "dark") {
                    document.documentElement.dataset.lightdark = lightdark;
                } else if (legacyTheme === "light" || legacyTheme === "dark") {
                    document.documentElement.dataset.lightdark = legacyTheme;
                }
            } catch (error) {}
        })();
    </script>
    <?php starter_dist_resource_placeholder(); ?>
    <?php starter_dist_critical_styles(); ?>
    <?php starter_dist_style_placeholder(); ?>
    <script>
        (() => {
            try {
                if (window.sessionStorage.getItem("admin-web-vitals-collecting") !== "1") {
                    return;
                }

                window.__adminWebVitals = {
                    endpoint: <?= json_encode($admin_base . '/admin/collect-vitals.php', JSON_UNESCAPED_SLASHES) ?>
                };

                const script = document.createElement("script");
                script.type = "module";
                script.src = <?= json_encode($admin_base . '/admin/assets/web-vitals.js', JSON_UNESCAPED_SLASHES) ?>;
                document.head.appendChild(script);
            } catch (error) {}
        })();
    </script>
</head>

<body>
    
    <?= $content ?>

    <?php starter_dist_scripts(); ?>
</body>

</html>
