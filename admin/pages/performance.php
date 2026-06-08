<?php
declare(strict_types=1);

$assetsDir = WEB_ASSETS_ROOT;
$performance = admin_performance_load();
$realVitals = admin_real_vitals_load();
$summary = is_array($performance['summary'] ?? null) ? $performance['summary'] : [];
$pages = is_array($performance['pages'] ?? null) ? $performance['pages'] : [];
$realPages = is_array($realVitals['pages'] ?? null) ? $realVitals['pages'] : [];
$status = isset($performance['status']) && is_string($performance['status']) ? $performance['status'] : 'idle';
$message = isset($performance['message']) && is_string($performance['message']) ? $performance['message'] : '';
$updatedAt = admin_performance_format_date(is_string($performance['updatedAt'] ?? null) ? $performance['updatedAt'] : null);
$webVitalsCollecting = admin_web_vitals_collecting();
$realVitalsUpdatedAt = admin_performance_format_date(is_string($realVitals['updatedAt'] ?? null) ? $realVitals['updatedAt'] : null);
$webVitalsStarted = isset($_GET['webvitals']) && $_GET['webvitals'] === 'started';
$webVitalsStopped = isset($_GET['webvitals']) && $_GET['webvitals'] === 'stopped';

if (!function_exists('admin_format_bytes')) {
    function admin_format_bytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' o';
        }

        return round($bytes / 1024, 1) . ' Ko';
    }
}

if (!function_exists('admin_asset_size')) {
    function admin_asset_size(string $assetsDir, string $file): ?int
    {
        $path = rtrim($assetsDir, '/') . '/' . ltrim($file, '/');

        return is_file($path) ? filesize($path) : null;
    }
}

if (!function_exists('admin_css_bundle_manifest')) {
    function admin_css_bundle_manifest(string $assetsDir): array
    {
        $path = rtrim($assetsDir, '/') . '/css-bundles.json';
        if (!is_file($path)) {
            return [];
        }

        $json = json_decode((string) file_get_contents($path), true);

        return is_array($json) ? $json : [];
    }
}

if (!function_exists('admin_asset_gauge')) {
    function admin_asset_gauge(string $assetsDir, string $label, string $file, int $maxSize, ?bool $used = null): array
    {
        $size = admin_asset_size($assetsDir, $file);
        $percent = $size !== null ? min(100, round(($size / $maxSize) * 100, 1)) : 0;
        $class = 'admin-gauge';
        $status = $size !== null ? null : 'non genere';

        if ($percent >= 100) {
            $class .= ' is-danger';
        } elseif ($percent >= 75) {
            $class .= ' is-warning';
        }

        if ($size === null && $used === false) {
            $status = 'non utilise';
        }

        return [
            'label' => $label,
            'file' => $file,
            'size' => $size,
            'max' => $maxSize,
            'percent' => $percent,
            'class' => $class,
            'status' => $status,
        ];
    }
}

$cssBundleManifest = admin_css_bundle_manifest($assetsDir);
$cssBundles = is_array($cssBundleManifest['bundles'] ?? null) ? $cssBundleManifest['bundles'] : [];
$assetGauges = [
    admin_asset_gauge($assetsDir, 'CSS critique', 'critical.css', 14 * 1024),
    admin_asset_gauge($assetsDir, 'Bundle common CSS', $cssBundles['common'] ?? 'common.css', 80 * 1024, isset($cssBundles['common'])),
    admin_asset_gauge($assetsDir, 'Bundle app JS', 'app.js', 120 * 1024),
];

$fieldVitals = [
    [
        'key' => 'lcp',
        'label' => 'LCP',
        'target' => '< 2.5 s',
        'description' => 'Mesure terrain du chargement percu sur les navigateurs des visiteurs.',
    ],
    [
        'key' => 'inp',
        'label' => 'INP',
        'target' => '< 200 ms',
        'description' => 'Reactivite reelle apres interaction utilisateur.',
    ],
    [
        'key' => 'cls',
        'label' => 'CLS',
        'target' => '< 0.1',
        'description' => 'Stabilite visuelle observee sur les sessions reelles.',
    ],
];

$displayLocalPages = admin_performance_display_local_pages($pages);

$realSummary = [
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
        if ($realSummary[$metric] === null || $value > $realSummary[$metric]) {
            $realSummary[$metric] = $metric === 'cls' ? round($value, 3) : ($metric === 'lcp' ? round($value, 2) : round($value));
        }
    }
}

$localGlobalScore = admin_performance_global_score($summary, ['lcp', 'cls', 'tbt']);
$fieldGlobalScore = admin_performance_global_score($realSummary, ['lcp', 'inp', 'cls']);

ob_start();
?>
<section class="admin-panel">
    <h2>Assets</h2>

    <div class="admin-gauges">
        <?php foreach ($assetGauges as $gauge) : ?>
            <div class="<?= admin_escape($gauge['class']) ?>">
                <p>
                    <?= admin_escape($gauge['label']) ?>:
                    <?= $gauge['size'] !== null ? admin_escape(admin_format_bytes($gauge['size'])) : admin_escape((string) $gauge['status']) ?>
                    / <?= admin_escape(admin_format_bytes($gauge['max'])) ?>
                </p>
                <p class="admin-gauge-meta">
                    Fichier: <?= admin_escape($gauge['file']) ?>.
                    Max conseille: <?= admin_escape(admin_format_bytes($gauge['max'])) ?>.
                    Utilisation: <?= admin_escape((string) $gauge['percent']) ?>%.
                </p>

                <div class="admin-gauge-track" role="meter" aria-valuemin="0" aria-valuemax="<?= (int) $gauge['max'] ?>" aria-valuenow="<?= (int) ($gauge['size'] ?? 0) ?>" aria-label="Poids <?= admin_escape($gauge['label']) ?>">
                    <span class="admin-gauge-value" style="--value: <?= admin_escape((string) $gauge['percent']) ?>%"></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<section class="admin-section-separator" aria-hidden="true"></section>

<div data-performance-local-root>
    <?= admin_render_performance_local_sections($performance, admin_base_url() . '/run-performance.php') ?>
</div>

<section class="admin-section-separator" aria-hidden="true"></section>

<section class="admin-panel">
    <h2>Web Vitals</h2>
    <p>Cette section regroupe les resultats issus des donnees terrain des visiteurs.</p>

    <form class="admin-form" method="post" action="<?= admin_escape(admin_base_url() . '/toggle-web-vitals.php') ?>">
        <div class="admin-actions">
            <button
                class="admin-button<?= $webVitalsCollecting ? ' is-secondary' : '' ?>"
                type="submit"
                name="action"
                value="<?= $webVitalsCollecting ? 'stop' : 'start' ?>"
                data-web-vitals-toggle
                data-start-label="Start"
                data-stop-label="Stop"
            >
                <?= $webVitalsCollecting ? 'Stop' : 'Start' ?>
            </button>
        </div>
    </form>

    <?php if ($webVitalsStarted) : ?>
        <div class="admin-notice">
            <p>Collecte demarree. Navigue sur le front puis reviens ici pour cliquer sur Stop et conserver les mesures.</p>
        </div>
    <?php endif; ?>

    <?php if ($webVitalsStopped) : ?>
        <div class="admin-notice">
            <p>Collecte arretee. Les resultats terrain affiches ci-dessous ont ete conserves.</p>
        </div>
    <?php endif; ?>

    <p>Etat : <?= $webVitalsCollecting ? 'collecte en cours' : 'arret' ?>. Derniere reception : <?= admin_escape($realVitalsUpdatedAt) ?></p>
</section>

<section class="admin-card-grid" aria-label="Web Vitals terrain">
    <article class="admin-stat-card">
        <p class="admin-stat-label">Score global</p>
        <div
            class="admin-semi-gauge <?= admin_escape(admin_performance_score_class($fieldGlobalScore)) ?>"
            style="--score: <?= (int) ($fieldGlobalScore ?? 0) ?>"
            role="meter"
            aria-label="Score global Web Vitals"
            aria-valuemin="0"
            aria-valuemax="100"
            aria-valuenow="<?= (int) ($fieldGlobalScore ?? 0) ?>"
        >
            <span class="admin-semi-gauge-arc" aria-hidden="true"></span>
            <span class="admin-semi-gauge-value"><?= admin_escape($fieldGlobalScore !== null ? $fieldGlobalScore . '/100' : '-') ?></span>
        </div>
        <p class="admin-stat-target"><?= admin_escape(admin_performance_score_label($fieldGlobalScore)) ?></p>
        <p class="admin-stat-description">Score synthetique calcule a partir de LCP, INP et CLS.</p>
    </article>
    <?php foreach ($fieldVitals as $vital) : ?>
        <?php
        $fieldValue = $realSummary[$vital['key']] ?? null;
        $fieldScore = admin_performance_metric_score($fieldValue, $vital['key']);
        ?>
        <article class="admin-stat-card">
            <p class="admin-stat-label"><?= admin_escape($vital['label']) ?></p>
            <div
                class="admin-semi-gauge <?= admin_escape(admin_performance_score_class($fieldScore)) ?>"
                style="--score: <?= (int) ($fieldScore ?? 0) ?>"
                role="meter"
                aria-label="<?= admin_escape($vital['label']) ?> Web Vitals"
                aria-valuemin="0"
                aria-valuemax="100"
                aria-valuenow="<?= (int) ($fieldScore ?? 0) ?>"
            >
                <span class="admin-semi-gauge-arc" aria-hidden="true"></span>
                <span class="admin-semi-gauge-value"><?= admin_escape(admin_performance_format_value($fieldValue, $vital['key'])) ?></span>
            </div>
            <p class="admin-stat-target">Objectif <?= admin_escape($vital['target']) ?></p>
            <p class="admin-stat-description"><?= admin_escape($vital['description']) ?></p>
        </article>
    <?php endforeach; ?>
</section>

<section class="admin-panel">
    <div class="admin-panel-heading">
        <h2>Resultats terrain</h2>
        <p class="admin-panel-meta">Derniere reception : <?= admin_escape($realVitalsUpdatedAt) ?></p>
    </div>

    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Page</th>
                    <th>LCP</th>
                    <th>INP</th>
                    <th>CLS</th>
                    <th>Samples</th>
                    <th>Score global</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($realPages === []) : ?>
                    <tr>
                        <td>Accueil</td>
                        <td>-</td>
                        <td>Mesure terrain requise</td>
                        <td>-</td>
                        <td>0</td>
                        <td>
                            <span class="admin-score-badge is-pending">
                                <strong>-</strong>
                                <span>En attente</span>
                            </span>
                        </td>
                    </tr>
                <?php else : ?>
                    <?php foreach ($realPages as $realPage) : ?>
                        <?php
                        $metrics = is_array($realPage['metrics'] ?? null) ? $realPage['metrics'] : [];
                        $realPageScore = admin_performance_global_score($metrics, ['lcp', 'inp', 'cls']);
                        ?>
                        <tr>
                            <td><?= admin_escape((string) ($realPage['label'] ?? '-')) ?></td>
                            <td><?= admin_escape(admin_performance_format_value($metrics['lcp'] ?? null, 'lcp')) ?></td>
                            <td><?= admin_escape(admin_performance_format_value($metrics['inp'] ?? null, 'inp')) ?></td>
                            <td><?= admin_escape(admin_performance_format_value($metrics['cls'] ?? null, 'cls')) ?></td>
                            <td><?= admin_escape((string) ($realPage['samples'] ?? 0)) ?></td>
                            <td>
                                <span class="admin-score-badge <?= admin_escape(admin_performance_score_class($realPageScore)) ?>">
                                    <strong><?= admin_escape($realPageScore !== null ? $realPageScore . '/100' : '-') ?></strong>
                                    <span><?= admin_escape(admin_performance_score_label($realPageScore)) ?></span>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php

return [
    'title' => 'Admin - Performance',
    'heading' => 'Performance',
    'intro' => 'Base de travail pour suivre les budgets d assets, les futures mesures locales et les metriques LCP, INP, CLS et TBT.',
    'content' => (string) ob_get_clean(),
];
