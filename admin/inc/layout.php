<?php
declare(strict_types=1);

function admin_render_layout(array $view): void
{
    $title = isset($view['title']) && is_string($view['title']) ? $view['title'] : 'Admin';
    $page = isset($view['page']) && is_string($view['page']) ? $view['page'] : 'dashboard';
    $activeSection = isset($view['active_section']) && is_string($view['active_section']) ? $view['active_section'] : '';
    $heading = isset($view['heading']) && is_string($view['heading']) ? $view['heading'] : 'Admin';
    $intro = isset($view['intro']) && is_string($view['intro']) ? $view['intro'] : '';
    $content = isset($view['content']) && is_string($view['content']) ? $view['content'] : '';

    $primaryNavItem = [
        'dashboard' => 'Dashboard',
    ];

    $navGroups = [
        'Audit' => [
            'performance' => 'Performance',
            'accessibilite' => 'Accessibilite',
            'seo' => 'SEO',
            'ux' => 'Ux',
        ],
        'Reglage' => [
            'images' => 'Images',
        ],
        'Outils' => [
            'webp' => 'WebP',
            'icons' => 'Icons',
        ],
        'Projet' => [
            'specifications' => 'Specifications',
            'wordpress' => 'WordPress',
        ],
    ];
    $navChildren = [
        'specifications' => [
            'cadrage' => [
                'label' => 'Cadrage',
                'children' => [
                    'cadrage-contexte' => 'Contexte',
                    'cadrage-objectif' => 'Objectif',
                    'cadrage-perimetre' => 'Perimetre',
                ],
            ],
            'prototype' => [
                'label' => 'Prototype',
                'children' => [
                    'prototype-arborescence' => 'Arborescence',
                    'prototype-wireframes' => 'Wireframes',
                    'prototype-wireflows' => 'Wireflows',
                    'prototype-contenus' => 'Contenus',
                    'prototype-priorisation' => 'Priorisation',
                ],
            ],
            'specifications-techniques' => [
                'label' => 'Specifications techniques',
                'children' => [
                    'spec-tech-pages' => 'Pages',
                    'spec-tech-composants' => 'Composants',
                    'spec-tech-strates' => 'Strates',
                    'spec-tech-css-hydratation' => 'CSS et hydratation',
                ],
            ],
        ],
    ];
    $requestPath = parse_url((string) ($_SERVER['REQUEST_URI'] ?? ''), PHP_URL_PATH);
    $requestPath = trim(str_replace('\\', '/', is_string($requestPath) ? $requestPath : ''), '/');
    $requestPath = preg_replace('#/+#', '/', $requestPath) ?? '';
    $activePage = $page;

    if ($requestPath === 'admin' || $requestPath === 'admin/index.php') {
        $activePage = 'dashboard';
    } elseif (str_starts_with($requestPath, 'admin/')) {
        $adminRoute = trim(substr($requestPath, strlen('admin/')), '/');

        if ($adminRoute === 'performance') {
            $activePage = 'performance';
        } elseif ($adminRoute === 'ux') {
            $activePage = 'ux';
        } elseif ($adminRoute === 'wordpress') {
            $activePage = 'wordpress';
        } elseif ($adminRoute === 'accessibilite') {
            $activePage = 'accessibilite';
        } elseif ($adminRoute === 'seo') {
            $activePage = 'seo';
        } elseif ($adminRoute === 'images') {
            $activePage = 'images';
        } elseif ($adminRoute === 'webp') {
            $activePage = 'webp';
        } elseif ($adminRoute === 'icons') {
            $activePage = 'icons';
        } elseif ($adminRoute === 'specification' || str_starts_with($adminRoute, 'specification/')) {
            $activePage = 'specifications';
        }
    }
    ?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= admin_escape($title) ?></title>
    <link rel="stylesheet" href="<?= admin_escape(admin_asset_url('admin.css')) ?>">
</head>
<body class="admin-shell">
    <header class="admin-header">
        <div class="admin-header-inner">
            <strong class="admin-brand">Admin</strong>
        </div>
    </header>

    <div class="admin-layout">
        <aside class="admin-sidebar" aria-label="Navigation admin">
            <nav class="admin-nav">
                <ul class="admin-nav-list admin-nav-list-primary">
                    <?php foreach ($primaryNavItem as $navKey => $navLabel) : ?>
                        <li>
                            <a
                                class="admin-nav-link<?= $activePage === $navKey ? ' is-active' : '' ?>"
                                href="<?= admin_escape(admin_page_url($navKey)) ?>"
                                <?= $activePage === $navKey ? 'aria-current="page"' : '' ?>
                            >
                                <?= admin_escape($navLabel) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <?php foreach ($navGroups as $groupLabel => $navItems) : ?>
                    <?php
                    $groupSlug = strtolower((string) preg_replace('/[^a-z0-9]+/i', '-', $groupLabel));
                    $groupSlug = trim($groupSlug, '-');
                    $groupId = 'admin-nav-group-' . ($groupSlug !== '' ? $groupSlug : 'group');
                    $hasActiveItem = in_array($activePage, array_keys($navItems), true);
                    ?>
                    <section class="admin-nav-group" aria-label="<?= admin_escape($groupLabel) ?>" data-admin-nav-group data-default-open="<?= $hasActiveItem ? 'true' : 'false' ?>">
                        <button
                            class="admin-nav-group-toggle"
                            type="button"
                            aria-expanded="<?= $hasActiveItem ? 'true' : 'false' ?>"
                            aria-controls="<?= admin_escape($groupId) ?>"
                            data-admin-nav-toggle
                        >
                            <span class="admin-nav-group-title"><?= admin_escape($groupLabel) ?></span>
                            <span class="admin-nav-group-icon" aria-hidden="true"></span>
                        </button>
                        <ul class="admin-nav-list <?= admin_escape($groupId) ?>" id="<?= admin_escape($groupId) ?>"<?= $hasActiveItem ? '' : ' hidden' ?>>
                            <?php foreach ($navItems as $navKey => $navLabel) : ?>
                                <li>
                                    <a
                                        class="admin-nav-link<?= $activePage === $navKey ? ' is-active' : '' ?>"
                                        href="<?= admin_escape(admin_page_url($navKey)) ?>"
                                        <?= $activePage === $navKey ? 'aria-current="page"' : '' ?>
                                    >
                                        <?= admin_escape($navLabel) ?>
                                    </a>

                                    <?php if (isset($navChildren[$navKey]) && $activePage === $navKey) : ?>
                                        <ul class="admin-subnav-list">
                                            <?php foreach ($navChildren[$navKey] as $childAnchor => $childLabel) : ?>
                                                <li>
                                                    <?php if (is_array($childLabel)) : ?>
                                                        <?php
                                                        $childGroupLabel = isset($childLabel['label']) && is_string($childLabel['label']) ? $childLabel['label'] : '';
                                                        $childGroupChildren = isset($childLabel['children']) && is_array($childLabel['children']) ? $childLabel['children'] : [];
                                                        ?>
                                                        <a
                                                            class="admin-subnav-link<?= $activeSection === $childAnchor ? ' is-active' : '' ?>"
                                                            href="<?= admin_escape(admin_page_url($navKey, ['section' => $childAnchor])) ?>"
                                                        >
                                                            <?= admin_escape($childGroupLabel) ?>
                                                        </a>

                                                        <?php if ($childGroupChildren !== []) : ?>
                                                            <ul class="admin-subnav-list admin-subnav-list-level-3" aria-label="Sous-navigation <?= admin_escape($childGroupLabel) ?>">
                                                                <?php foreach ($childGroupChildren as $grandChildAnchor => $grandChildLabel) : ?>
                                                                    <li>
                                                                        <a
                                                                            class="admin-subnav-link admin-subnav-link-level-3<?= $activeSection === $grandChildAnchor ? ' is-active' : '' ?>"
                                                                            href="<?= admin_escape(admin_page_url($navKey, ['section' => $grandChildAnchor])) ?>"
                                                                        >
                                                                            <?= admin_escape((string) $grandChildLabel) ?>
                                                                        </a>
                                                                    </li>
                                                                <?php endforeach; ?>
                                                            </ul>
                                                        <?php endif; ?>
                                                    <?php else : ?>
                                                        <a
                                                            class="admin-subnav-link<?= $activeSection === $childAnchor ? ' is-active' : '' ?>"
                                                            href="<?= admin_escape(admin_page_url($navKey, ['section' => $childAnchor])) ?>"
                                                        >
                                                            <?= admin_escape((string) $childLabel) ?>
                                                        </a>
                                                    <?php endif; ?>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php endforeach; ?>
            </nav>
        </aside>

        <main class="admin-main">
            <div class="admin-page">
                <header class="admin-page-head">
                    <h1><?= admin_escape($heading) ?></h1>
                    <?php if ($intro !== '') : ?>
                        <p><?= admin_escape($intro) ?></p>
                    <?php endif; ?>
                </header>

                <?= $content ?>
            </div>
        </main>
    </div>

    <script src="<?= admin_escape(admin_asset_url('admin.js')) ?>"></script>
</body>
</html>
    <?php
}
