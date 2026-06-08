<?php
declare(strict_types=1);

$performance = admin_performance_load();
$realVitals = admin_real_vitals_load();
$performanceSummary = is_array($performance['summary'] ?? null) ? $performance['summary'] : [];
$realPages = is_array($realVitals['pages'] ?? null) ? $realVitals['pages'] : [];
$performanceScore = admin_performance_global_score($performanceSummary, ['lcp', 'cls', 'tbt']);
$webVitalsSummary = [
    'lcp' => null,
    'inp' => null,
    'cls' => null,
];

foreach ($realPages as $realPage) {
    $metrics = is_array($realPage['metrics'] ?? null) ? $realPage['metrics'] : [];

    foreach (['lcp', 'inp', 'cls'] as $metric) {
        if (!is_numeric($metrics[$metric] ?? null)) {
            continue;
        }

        $value = (float) $metrics[$metric];
        if ($webVitalsSummary[$metric] === null || $value > $webVitalsSummary[$metric]) {
            $webVitalsSummary[$metric] = $metric === 'cls' ? round($value, 3) : ($metric === 'lcp' ? round($value, 2) : round($value));
        }
    }
}

$webVitalsScore = admin_performance_global_score($webVitalsSummary, ['lcp', 'inp', 'cls']);
$globalNotes = [
    [
        'label' => 'Performance locale',
        'score' => $performanceScore,
        'description' => 'Synthese des dernieres mesures Lighthouse locales sur LCP, CLS et TBT.',
        'target' => admin_performance_score_label($performanceScore),
    ],
    [
        'label' => 'Web Vitals',
        'score' => $webVitalsScore,
        'description' => 'Donnees terrain issues des visites reelles quand une collecte a ete enregistree.',
        'target' => admin_performance_score_label($webVitalsScore),
    ],
    [
        'label' => 'Accessibilite',
        'score' => null,
        'description' => 'Zone prevue pour agreger les futurs controles de contraste, structure et navigation clavier.',
        'target' => 'En attente',
    ],
    [
        'label' => 'SEO',
        'score' => null,
        'description' => 'Zone prevue pour la note globale de balisage, indexation et contenus.',
        'target' => 'En attente',
    ],
];

ob_start();
?>
<section class="admin-panel">
    <div class="admin-panel-heading">
        <h2>Notes globales</h2>
        <p class="admin-panel-meta">Vue rapide des indicateurs de qualite disponibles dans l admin</p>
    </div>

    <div class="admin-card-grid" aria-label="Notes globales">
        <?php foreach ($globalNotes as $note) : ?>
            <article class="admin-stat-card">
                <p class="admin-stat-label"><?= admin_escape($note['label']) ?></p>
                <div
                    class="admin-semi-gauge <?= admin_escape(admin_performance_score_class($note['score'])) ?>"
                    style="--score: <?= (int) ($note['score'] ?? 0) ?>"
                    role="meter"
                    aria-label="<?= admin_escape($note['label']) ?>"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="<?= (int) ($note['score'] ?? 0) ?>"
                >
                    <span class="admin-semi-gauge-arc" aria-hidden="true"></span>
                    <span class="admin-semi-gauge-value"><?= admin_escape($note['score'] !== null ? $note['score'] . '/100' : '-') ?></span>
                </div>
                <p class="admin-stat-target"><?= admin_escape($note['target']) ?></p>
                <p class="admin-stat-description"><?= admin_escape($note['description']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="admin-card-grid" aria-label="Apercu dashboard">
    <article class="admin-card">
        <h2>Performance</h2>
        <p>Suivi des mesures locales, des Web Vitals et du poids des assets critiques du projet.</p>
    </article>
    <article class="admin-card">
        <h2>Accessibilite</h2>
        <p>Zone reservee pour les controles de contraste, structure, labels et parcours clavier.</p>
    </article>
    <article class="admin-card">
        <h2>SEO</h2>
        <p>Zone reservee pour les titres, metas, indexation et controles de maillage.</p>
    </article>
</section>

<section class="admin-empty">
    <h2>Dashboard en preparation</h2>
    <p>Cette page sert de base pour centraliser les indicateurs de qualite du site dans une interface admin distincte du starter.</p>
</section>
<?php

return [
    'title' => 'Admin - Dashboard',
    'heading' => 'Dashboard',
    'intro' => 'Vue d ensemble de l administration. Cette interface a son propre design et ne depend pas du starter front.',
    'content' => (string) ob_get_clean(),
];
