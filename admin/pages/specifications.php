<?php
declare(strict_types=1);

$specOverviewCards = [
    [
        'label' => 'Document',
        'value' => 'V0.3',
        'target' => 'Prototype de structure',
        'description' => 'Base de travail pour cadrer besoins, contenus, contraintes et validation.',
    ],
    [
        'label' => 'Statut',
        'value' => 'En cours',
        'target' => 'Phase de cadrage',
        'description' => 'Le perimetre est pose, mais les arbitrages produit et contenus restent a confirmer.',
    ],
    [
        'label' => 'Priorite',
        'value' => 'Haute',
        'target' => 'Socle projet',
        'description' => 'Cette page peut devenir le point d entree commun entre design, contenu, SEO et technique.',
    ],
];

$cadrageItems = [
    [
        'id' => 'cadrage-contexte',
        'kicker' => 'Contexte',
        'title' => 'Refonte d un site editorial et marketing',
        'body' => 'Le projet vise a produire un site rapide, accessible et simple a maintenir, avec une logique de strates, de composants et de theming evolutif.',
    ],
    [
        'id' => 'cadrage-objectif',
        'kicker' => 'Objectif',
        'title' => 'Clarifier ce qui doit etre livre',
        'body' => 'Cette zone peut servir a figer le besoin, les livrables, les dependances, les risques et la definition de termine pour chaque partie du projet.',
    ],
    [
        'id' => 'cadrage-perimetre',
        'kicker' => 'Perimetre',
        'title' => 'Pages et modules attendus',
        'body' => 'Home, pages editoriales, systeme de strates, composants communs, theming, administration de suivi et contraintes de performance a respecter.',
    ],
];

$prototypeItems = [
    [
        'id' => 'prototype-arborescence',
        'kicker' => '01',
        'title' => 'Arborescence',
        'body' => 'Organisation des pages, priorites de navigation, types de contenus et profondeur attendue.',
    ],
    [
        'id' => 'prototype-wireframes',
        'kicker' => '02',
        'title' => 'Wireframes',
        'body' => 'Structure des pages cles, zones hero, ordre des strates et variations desktop/mobile.',
    ],
    [
        'id' => 'prototype-wireflows',
        'kicker' => '03',
        'title' => 'Wireflows',
        'body' => 'Enchainement des ecrans, etapes de parcours et points de friction a valider avant integration.',
    ],
    [
        'id' => 'prototype-contenus',
        'kicker' => '04',
        'title' => 'Contenus',
        'body' => 'Textes, images, tags, categories, champs attendus et responsabilites de contribution.',
    ],
    [
        'id' => 'prototype-priorisation',
        'kicker' => '05',
        'title' => 'Priorisation',
        'body' => 'Ordre de production, blocs indispensables, variantes secondaires et points a arbitrer.',
    ],
];

$specTechItems = [
    [
        'id' => 'spec-tech-pages',
        'title' => 'Pages',
        'body' => 'Definition des types de pages, gabarits disponibles, variations et regles de composition entre templates et contenus.',
        'badge' => 'Structure',
    ],
    [
        'id' => 'spec-tech-composants',
        'title' => 'Composants',
        'body' => 'Liste des composants a produire, variantes, etats, dependances JS/CSS et conventions de nommage. Le composant notification y est documente comme bloc reutilisable pour les retours de succes, d erreur et d information.',
        'badge' => 'UI',
    ],
    [
        'id' => 'spec-tech-strates',
        'title' => 'Strates',
        'body' => 'Definition des blocs de page, logique de composition, ordre de chargement et besoins d hydratation.',
        'badge' => 'Layout',
    ],
    [
        'id' => 'spec-tech-css-hydratation',
        'title' => 'CSS et hydratation',
        'body' => 'Chargement critique pour les premiers blocs, hydratation/lazy pour les suivants, et theming evolutif sans surcharger le bundle principal.',
        'badge' => 'Front',
    ],
];

$specGroups = [
    'cadrage' => [
        'label' => 'Cadrage',
        'meta' => 'Vision et perimetre',
        'type' => 'blocks',
        'hero' => true,
        'items' => $cadrageItems,
    ],
    'prototype' => [
        'label' => 'Prototype',
        'meta' => 'Organisation et livrables UX/UI',
        'type' => 'blocks',
        'grid_class' => 'admin-spec-grid admin-spec-grid-3',
        'items' => $prototypeItems,
    ],
    'specifications-techniques' => [
        'label' => 'Specifications techniques',
        'meta' => 'Socle, contraintes et qualite',
        'type' => 'rows',
        'items' => $specTechItems,
    ],
];

$specSections = [];
foreach ($specGroups as $groupId => $group) {
    $specSections[$groupId] = [
        'id' => $groupId,
        'group' => $groupId,
        'group_label' => $group['label'],
        'label' => $group['label'],
        'meta' => $group['meta'],
        'type' => $group['type'],
        'hero' => !empty($group['hero']),
        'items' => $group['items'],
        'is_group' => true,
    ];

    foreach ($group['items'] as $item) {
        $specSections[$item['id']] = [
            'id' => $item['id'],
            'group' => $groupId,
            'group_label' => $group['label'],
            'label' => $item['title'],
            'meta' => $group['meta'],
            'type' => $group['type'],
            'hero' => false,
            'items' => [$item],
            'is_group' => false,
        ];
    }
}

function admin_spec_render_blocks(array $items, string $gridClass = 'admin-spec-grid'): string
{
    ob_start();
    ?>
    <div class="<?= admin_escape($gridClass) ?>">
        <?php foreach ($items as $item) : ?>
            <article id="<?= admin_escape((string) $item['id']) ?>" class="admin-spec-block">
                <p class="admin-spec-kicker"><?= admin_escape((string) $item['kicker']) ?></p>
                <h3><?= admin_escape((string) $item['title']) ?></h3>
                <p><?= admin_escape((string) $item['body']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
    <?php

    return (string) ob_get_clean();
}

function admin_spec_render_rows(array $items): string
{
    ob_start();
    ?>
    <div class="admin-spec-list">
        <?php foreach ($items as $item) : ?>
            <article id="<?= admin_escape((string) $item['id']) ?>" class="admin-spec-row">
                <div>
                    <h3><?= admin_escape((string) $item['title']) ?></h3>
                    <p><?= admin_escape((string) $item['body']) ?></p>
                </div>
                <span class="admin-spec-badge"><?= admin_escape((string) $item['badge']) ?></span>
            </article>
        <?php endforeach; ?>
    </div>
    <?php

    return (string) ob_get_clean();
}

function admin_spec_render_notification_preview(): string
{
    if (!function_exists('admin_notification')) {
        return '';
    }

    ob_start();
    ?>
    <div class="admin-notification-preview-grid">
        <?= admin_notification([
            'type' => 'success',
            'title' => 'Notification',
            'message' => 'Retour visuel pour confirmer une action ou une sauvegarde.',
            'duration' => 0,
        ]) ?>
        <?= admin_notification([
            'type' => 'error',
            'title' => 'Notification',
            'message' => 'Retour d erreur reutilisable sur les formulaires et actions asynchrones.',
            'duration' => 0,
        ]) ?>
    </div>
    <?php

    return (string) ob_get_clean();
}

$legacySection = isset($_GET['section']) && is_string($_GET['section']) ? trim($_GET['section']) : '';
$route = isset($_GET['route']) && is_string($_GET['route']) ? trim($_GET['route'], '/') : '';

if ($legacySection !== '' && isset($specSections[$legacySection])) {
    header('Location: ' . admin_specification_url($legacySection), true, 301);
    exit;
}

$activeSection = admin_specification_section_from_route($route);

if ($route !== '' && $activeSection === '') {
    http_response_code(404);
}

$selectedSection = $activeSection !== '' && isset($specSections[$activeSection]) ? $specSections[$activeSection] : null;

ob_start();

if ($selectedSection === null) :
?>
<section class="admin-card-grid" aria-label="Vue d ensemble des specifications">
    <?php foreach ($specOverviewCards as $card) : ?>
        <article class="admin-stat-card">
            <p class="admin-stat-label"><?= admin_escape($card['label']) ?></p>
            <p class="admin-stat-value"><?= admin_escape($card['value']) ?></p>
            <p class="admin-stat-target"><?= admin_escape($card['target']) ?></p>
            <p class="admin-stat-description"><?= admin_escape($card['description']) ?></p>
        </article>
    <?php endforeach; ?>
</section>

<section id="cadrage" class="admin-panel admin-spec-hero">
    <div class="admin-panel-heading">
        <h2>Cadrage</h2>
        <p class="admin-panel-meta">Vision et perimetre</p>
    </div>
    <?= admin_spec_render_blocks($cadrageItems) ?>
</section>

<section id="prototype" class="admin-panel">
    <div class="admin-panel-heading">
        <h2>Prototype</h2>
        <p class="admin-panel-meta">Organisation et livrables UX/UI</p>
    </div>
    <?= admin_spec_render_blocks($prototypeItems, 'admin-spec-grid admin-spec-grid-3') ?>
</section>

<section id="specifications-techniques" class="admin-panel">
    <div class="admin-panel-heading">
        <h2>Specifications techniques</h2>
        <p class="admin-panel-meta">Socle, contraintes et qualite</p>
    </div>
    <?= admin_spec_render_rows($specTechItems) ?>
</section>
<?php
else :
    $groupId = $selectedSection['group'];
    $group = $specGroups[$groupId];
    $panelClass = !empty($selectedSection['hero']) ? 'admin-panel admin-spec-hero' : 'admin-panel';
?>
<section class="admin-panel">
    <div class="admin-panel-heading">
        <h2><?= admin_escape($selectedSection['group_label']) ?> / <?= admin_escape($selectedSection['label']) ?></h2>
        <p class="admin-panel-meta">Sous-page Specifications</p>
    </div>
    <p>
        Vue dediee pour travailler uniquement cette sous-partie.
        Tu peux revenir a l ensemble ou rester dans cette section pour la documenter plus finement.
    </p>
    <div class="admin-actions">
        <a class="admin-button is-secondary" href="<?= admin_escape(admin_page_url('specifications')) ?>">Voir toute la page</a>
        <a class="admin-button is-secondary" href="<?= admin_escape(admin_page_url('specifications', ['section' => $groupId])) ?>">Voir la section <?= admin_escape($selectedSection['group_label']) ?></a>
    </div>
</section>

<section id="<?= admin_escape($selectedSection['id']) ?>" class="<?= admin_escape($panelClass) ?>">
    <div class="admin-panel-heading">
        <h2><?= admin_escape($selectedSection['label']) ?></h2>
        <p class="admin-panel-meta"><?= admin_escape($selectedSection['meta']) ?></p>
    </div>

    <?php if ($selectedSection['type'] === 'rows') : ?>
        <?= admin_spec_render_rows($selectedSection['items']) ?>
    <?php else : ?>
        <?= admin_spec_render_blocks(
            $selectedSection['items'],
            $selectedSection['is_group'] ? (!empty($group['grid_class']) ? (string) $group['grid_class'] : 'admin-spec-grid') : 'admin-spec-grid'
        ) ?>
    <?php endif; ?>
</section>

<?php if ($selectedSection['id'] === 'spec-tech-composants') : ?>
<section class="admin-panel admin-spec-hero">
    <div class="admin-panel-heading">
        <h2>Notification</h2>
        <p class="admin-panel-meta">Composant reutilisable pour les retours d action</p>
    </div>
    <p>
        Le composant `notification` sert aux retours de succes, d erreur et d information dans l admin.
        Il est concu pour fonctionner en PHP comme en JavaScript, avec un affichage temporaire ou persistent selon le besoin.
    </p>
    <?= admin_spec_render_notification_preview() ?>
</section>
<?php endif; ?>
<?php
endif;

return [
    'title' => 'Admin - Specifications',
    'page' => 'specifications',
    'active_section' => $selectedSection['id'] ?? '',
    'heading' => $selectedSection !== null ? $selectedSection['label'] : 'Specifications',
    'intro' => $selectedSection !== null
        ? 'Sous-page de specifications pour travailler un point precis du projet sans passer par toute la vue d ensemble.'
        : 'Prototype d une page de specifications pour cadrer le projet, partager les attentes et centraliser les decisions produit, contenu et technique.',
    'content' => (string) ob_get_clean(),
];
