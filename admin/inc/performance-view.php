<?php
declare(strict_types=1);

function admin_performance_local_vitals(): array
{
    return [
        [
            'key' => 'lcp',
            'label' => 'LCP',
            'target' => '< 2.5 s',
            'description' => 'Temps de rendu du plus grand element visible au chargement.',
        ],
        [
            'key' => 'cls',
            'label' => 'CLS',
            'target' => '< 0.1',
            'description' => 'Stabilite visuelle pendant le chargement et les mises a jour.',
        ],
        [
            'key' => 'tbt',
            'label' => 'TBT',
            'target' => '< 200 ms',
            'description' => 'Temps de blocage cumule du thread principal en phase de chargement.',
        ],
    ];
}

function admin_render_performance_local_sections(array $performance, string $runUrl): string
{
    $selectedProfileId = isset($performance['profile']) && is_string($performance['profile']) ? $performance['profile'] : 'desktop-local';
    $profile = admin_performance_run_profile($selectedProfileId);
    $profiles = admin_performance_run_profiles();
    $summary = is_array($performance['summary'] ?? null) ? $performance['summary'] : [];
    $pages = is_array($performance['pages'] ?? null) ? $performance['pages'] : [];
    $status = isset($performance['status']) && is_string($performance['status']) ? $performance['status'] : 'idle';
    $message = isset($performance['message']) && is_string($performance['message']) ? $performance['message'] : '';
    $updatedAt = admin_performance_format_date(is_string($performance['updatedAt'] ?? null) ? $performance['updatedAt'] : null);
    $displayLocalPages = admin_performance_display_local_pages($pages);
    $localGlobalScore = admin_performance_global_score($summary, ['lcp', 'cls', 'tbt']);
    $localDiagnosticGroups = admin_performance_build_local_diagnostic_groups($displayLocalPages);

    ob_start();
    ?>
    <section class="admin-panel">
        <div class="admin-panel-heading">
            <h2>Mesures locales</h2>
            <p class="admin-panel-meta">Derniere mise a jour : <?= admin_escape($updatedAt) ?></p>
        </div>
        <p>
            Cette section regroupe les releves locaux Lighthouse.
            Elle permet de suivre les pages auditees et les metriques de chargement sans donnees terrain.
        </p>
        <p class="admin-panel-meta">Preset utilise : <?= admin_escape($profile['label']) ?>. <?= admin_escape($profile['description']) ?></p>
        <form class="admin-actions admin-performance-run-form" method="post" action="<?= admin_escape($runUrl) ?>" data-performance-run-form>
            <label class="admin-label admin-performance-run-profile">
                <span>Mode</span>
                <select class="admin-select" name="profile">
                    <?php foreach ($profiles as $profileOption) : ?>
                        <option value="<?= admin_escape($profileOption['id']) ?>" <?= $profileOption['id'] === $profile['id'] ? 'selected' : '' ?>>
                            <?= admin_escape($profileOption['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </label>
            <button class="admin-button" type="submit">Lancer une mesure</button>
        </form>

        <?php if ($message !== '' && $status === 'error') : ?>
            <div class="admin-notice is-error">
                <p><?= admin_escape($message) ?></p>
            </div>
        <?php endif; ?>
    </section>

    <div class="admin-performance-local-sections">
        <section class="admin-card-grid" aria-label="Mesures locales">
            <article class="admin-stat-card">
                <p class="admin-stat-label">Score global</p>
                <div
                    class="admin-semi-gauge <?= admin_escape(admin_performance_score_class($localGlobalScore)) ?>"
                    style="--score: <?= (int) ($localGlobalScore ?? 0) ?>"
                    role="meter"
                    aria-label="Score global"
                    aria-valuemin="0"
                    aria-valuemax="100"
                    aria-valuenow="<?= (int) ($localGlobalScore ?? 0) ?>"
                >
                    <span class="admin-semi-gauge-arc" aria-hidden="true"></span>
                    <span class="admin-semi-gauge-value"><?= admin_escape($localGlobalScore !== null ? $localGlobalScore . '/100' : '-') ?></span>
                </div>
                <p class="admin-stat-target"><?= admin_escape(admin_performance_score_label($localGlobalScore)) ?></p>
                <p class="admin-stat-description">Score synthetique calcule a partir de LCP, CLS et TBT.</p>
            </article>
            <?php foreach (admin_performance_local_vitals() as $vital) : ?>
                <?php
                $vitalValue = $summary[$vital['key']] ?? null;
                $vitalScore = admin_performance_metric_score($vitalValue, $vital['key']);
                ?>
                <article class="admin-stat-card">
                    <p class="admin-stat-label"><?= admin_escape($vital['label']) ?></p>
                    <div
                        class="admin-semi-gauge <?= admin_escape(admin_performance_score_class($vitalScore)) ?>"
                        style="--score: <?= (int) ($vitalScore ?? 0) ?>"
                        role="meter"
                        aria-label="<?= admin_escape($vital['label']) ?>"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        aria-valuenow="<?= (int) ($vitalScore ?? 0) ?>"
                    >
                        <span class="admin-semi-gauge-arc" aria-hidden="true"></span>
                        <span class="admin-semi-gauge-value"><?= admin_escape(admin_performance_format_value($vitalValue, $vital['key'])) ?></span>
                    </div>
                    <p class="admin-stat-target">Objectif <?= admin_escape($vital['target']) ?></p>
                    <p class="admin-stat-description"><?= admin_escape($vital['description']) ?></p>
                </article>
            <?php endforeach; ?>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-heading">
                <h2>Resultats par page</h2>
                <p class="admin-panel-meta">Derniere mesure : <?= admin_escape($updatedAt) ?></p>
            </div>

            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Page</th>
                            <th>LCP</th>
                            <th>CLS</th>
                            <th>TBT</th>
                            <th>Score global</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($displayLocalPages as $page) : ?>
                            <?php $pageScore = admin_performance_global_score($page, ['lcp', 'cls', 'tbt']); ?>
                            <tr>
                                <td><?= admin_escape((string) ($page['label'] ?? '-')) ?></td>
                                <td><?= admin_escape(admin_performance_format_value($page['lcp'] ?? null, 'lcp')) ?></td>
                                <td><?= admin_escape(admin_performance_format_value($page['cls'] ?? null, 'cls')) ?></td>
                                <td><?= admin_escape(admin_performance_format_value($page['tbt'] ?? null, 'tbt')) ?></td>
                                <td>
                                    <span class="admin-score-badge <?= admin_escape(admin_performance_score_class($pageScore)) ?>">
                                        <strong><?= admin_escape($pageScore !== null ? $pageScore . '/100' : '-') ?></strong>
                                        <span><?= admin_escape(admin_performance_score_label($pageScore)) ?></span>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="admin-panel">
            <div class="admin-panel-heading">
                <h2>Detail des erreurs</h2>
                <p class="admin-panel-meta">Diagnostics LCP, CLS et TBT par page</p>
            </div>

            <div class="admin-diagnostic-tabs" data-admin-tabs>
                <div class="admin-diagnostic-tablist" role="tablist" aria-label="Diagnostics">
                    <?php $tabIndex = 0; ?>
                    <?php foreach ($localDiagnosticGroups as $metric => $diagnosticGroup) : ?>
                        <button
                            class="admin-diagnostic-tab<?= $tabIndex === 0 ? ' is-active' : '' ?>"
                            type="button"
                            role="tab"
                            id="admin-diagnostic-tab-<?= admin_escape($metric) ?>"
                            aria-controls="admin-diagnostic-panel-<?= admin_escape($metric) ?>"
                            aria-selected="<?= $tabIndex === 0 ? 'true' : 'false' ?>"
                            data-admin-tab-trigger
                        >
                            <span><?= admin_escape($diagnosticGroup['label']) ?></span>
                            <span class="admin-diagnostic-count"><?= count($diagnosticGroup['items']) ?></span>
                        </button>
                        <?php $tabIndex++; ?>
                    <?php endforeach; ?>
                </div>

                <?php $panelIndex = 0; ?>
                <?php foreach ($localDiagnosticGroups as $metric => $diagnosticGroup) : ?>
                    <section
                        class="admin-diagnostic-panel<?= $panelIndex === 0 ? ' is-active' : '' ?>"
                        role="tabpanel"
                        id="admin-diagnostic-panel-<?= admin_escape($metric) ?>"
                        aria-labelledby="admin-diagnostic-tab-<?= admin_escape($metric) ?>"
                        <?= $panelIndex === 0 ? '' : 'hidden' ?>
                    >
                        <article class="admin-diagnostic-card">
                            <div class="admin-diagnostic-heading">
                                <div>
                                    <p class="admin-stat-label"><?= admin_escape($diagnosticGroup['label']) ?></p>
                                    <p class="admin-diagnostic-target">Objectif : <?= admin_escape($diagnosticGroup['target']) ?></p>
                                </div>
                            </div>

                            <?php if ($diagnosticGroup['items'] === []) : ?>
                                <p class="admin-diagnostic-empty">Aucune erreur detectee sur les pages mesurees.</p>
                            <?php else : ?>
                                <div class="admin-diagnostic-list">
                                    <?php foreach ($diagnosticGroup['items'] as $diagnosticItem) : ?>
                                        <article class="admin-diagnostic-item">
                                            <div class="admin-diagnostic-item-head">
                                                <span><?= admin_escape($diagnosticItem['label']) ?></span>
                                                <span class="admin-score-badge <?= admin_escape(admin_performance_score_class($diagnosticItem['score'])) ?>">
                                                    <?= admin_escape($diagnosticItem['value']) ?>
                                                </span>
                                            </div>
                                            <div class="admin-diagnostic-body">
                                                <p><strong>Cible :</strong> <?= admin_escape($diagnosticItem['focus']) ?></p>
                                                <p><strong>Action :</strong> <?= admin_escape($diagnosticItem['action']) ?></p>
                                            </div>
                                            <details class="admin-diagnostic-details">
                                                <summary>Indices Lighthouse</summary>
                                                <ul>
                                                    <?php foreach ($diagnosticItem['messages'] as $diagnosticMessage) : ?>
                                                        <li><?= admin_escape($diagnosticMessage) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            </details>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </article>
                    </section>
                    <?php $panelIndex++; ?>
                <?php endforeach; ?>
            </div>
        </section>
    </div>
    <?php

    return (string) ob_get_clean();
}
