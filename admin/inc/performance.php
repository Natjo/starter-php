<?php
declare(strict_types=1);

function admin_performance_data_file(): string
{
    return dirname(__DIR__) . '/data/performance.json';
}

function admin_performance_default_data(): array
{
    return [
        'updatedAt' => null,
        'source' => 'local',
        'profile' => 'desktop-local',
        'status' => 'idle',
        'message' => '',
        'summary' => [
            'lcp' => null,
            'inp' => null,
            'cls' => null,
            'tbt' => null,
        ],
        'pages' => [],
    ];
}

function admin_real_vitals_file(): string
{
    return dirname(__DIR__) . '/data/real-vitals.json';
}

function admin_real_vitals_load(): array
{
    $file = admin_real_vitals_file();
    $defaults = [
        'updatedAt' => null,
        'pages' => [],
    ];

    if (!is_file($file)) {
        return $defaults;
    }

    $json = json_decode((string) file_get_contents($file), true);

    return is_array($json)
        ? array_replace_recursive($defaults, $json)
        : $defaults;
}

function admin_performance_load(): array
{
    $file = admin_performance_data_file();
    if (!is_file($file)) {
        return admin_performance_default_data();
    }

    $json = json_decode((string) file_get_contents($file), true);
    if (!is_array($json)) {
        return admin_performance_default_data();
    }

    return array_replace_recursive(admin_performance_default_data(), $json);
}

function admin_performance_save(array $data): void
{
    $file = admin_performance_data_file();
    $dir = dirname($file);

    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }

    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function admin_performance_base_url(): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = (string) ($_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost');
    $scriptName = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $basePath = $scriptName === '' ? '' : rtrim(dirname($scriptName), '/');

    if ($basePath === '.' || $basePath === '/') {
        $basePath = '';
    }

    if ($basePath === '/admin') {
        $basePath = '';
    } elseif (str_ends_with($basePath, '/admin')) {
        $basePath = substr($basePath, 0, -strlen('/admin'));
    }

    return $scheme . '://' . $host . $basePath;
}

function admin_performance_target_base_url(): string
{
    $publicBaseUrl = admin_performance_base_url();
    $publicHost = parse_url($publicBaseUrl, PHP_URL_HOST);

    if (is_string($publicHost) && $publicHost !== '') {
        $resolvedHost = gethostbyname($publicHost);
        $isLoopback = in_array($resolvedHost, ['127.0.0.1', '127.0.1.1', '::1'], true);

        if ($resolvedHost !== $publicHost && !$isLoopback) {
            return $publicBaseUrl;
        }
    }

    $dockerHosts = ['nginx', 'project-nginx'];

    foreach ($dockerHosts as $dockerHost) {
        $resolved = gethostbyname($dockerHost);
        if ($resolved !== $dockerHost) {
            return 'http://' . $dockerHost;
        }
    }

    return $publicBaseUrl;
}

function admin_performance_default_pages(): array
{
    $baseUrl = rtrim(admin_performance_target_base_url(), '/');
    $pages = [];

    foreach (admin_performance_discover_routes() as $route) {
        $pages[] = [
            'label' => admin_performance_route_label($route),
            'url' => $baseUrl . $route,
        ];
    }

    return $pages;
}

function admin_performance_discover_routes(): array
{
    $webRoot = APP_ROOT . '/web';
    if (!is_dir($webRoot)) {
        return ['/'];
    }

    $routes = ['/'];

    foreach (glob($webRoot . '/*.php') ?: [] as $file) {
        $name = basename($file);
        if ($name === 'index.php') {
            continue;
        }

        $slug = basename($file, '.php');
        if ($slug === '' || $slug === 'admin') {
            continue;
        }

        $routes[] = '/' . $slug;
    }

    foreach (glob($webRoot . '/*/index.php') ?: [] as $file) {
        $dir = basename(dirname($file));
        if ($dir === '' || $dir === 'assets' || $dir === 'uploads') {
            continue;
        }

        $routes[] = '/' . $dir;
    }

    $routes = array_values(array_unique($routes));
    sort($routes);

    if (($homeIndex = array_search('/', $routes, true)) !== false) {
        unset($routes[$homeIndex]);
        array_unshift($routes, '/');
    }

    return array_values($routes);
}

function admin_performance_route_label(string $route): string
{
    if ($route === '/') {
        return 'Accueil';
    }

    $slug = trim($route, '/');
    if ($slug === '') {
        return 'Accueil';
    }

    return ucwords(str_replace(['-', '_'], ' ', $slug));
}

function admin_performance_metric_specs(): array
{
    return [
        'lcp' => ['label' => 'LCP', 'unit' => 's', 'decimals' => 2],
        'inp' => ['label' => 'INP', 'unit' => 'ms', 'decimals' => 0],
        'cls' => ['label' => 'CLS', 'unit' => '', 'decimals' => 3],
        'tbt' => ['label' => 'TBT', 'unit' => 'ms', 'decimals' => 0],
    ];
}

function admin_performance_run_profiles(): array
{
    return [
        'desktop-local' => [
            'id' => 'desktop-local',
            'label' => 'Desktop',
            'description' => 'Mesure desktop locale sans throttling reseau/CPU pour se rapprocher d un Lighthouse lance dans Chrome DevTools en local.',
            'flags' => [
                '--preset=desktop',
                '--form-factor=desktop',
                '--throttling-method=provided',
                '--screenEmulation.disabled',
            ],
        ],
        'mobile-local' => [
            'id' => 'mobile-local',
            'label' => 'Mobile',
            'description' => 'Mesure mobile locale avec form factor mobile Lighthouse, sans throttling reseau/CPU additionnel.',
            'flags' => [
                '--form-factor=mobile',
                '--throttling-method=provided',
            ],
        ],
    ];
}

function admin_performance_run_profile(?string $profileId = null): array
{
    $profiles = admin_performance_run_profiles();
    $profileId = is_string($profileId) && isset($profiles[$profileId]) ? $profileId : 'desktop-local';

    return $profiles[$profileId];
}

function admin_performance_format_value(mixed $value, string $metric): string
{
    if (!is_numeric($value)) {
        return $metric === 'inp' ? 'Mesure terrain requise' : '-';
    }

    $specs = admin_performance_metric_specs();
    $spec = $specs[$metric] ?? ['unit' => '', 'decimals' => 0];
    $number = number_format((float) $value, (int) $spec['decimals'], '.', ' ');

    return $spec['unit'] !== '' ? $number . ' ' . $spec['unit'] : $number;
}

function admin_performance_format_date(?string $date): string
{
    if (!is_string($date) || $date === '') {
        return '-';
    }

    try {
        return (new DateTimeImmutable($date))->format('d/m/Y H:i');
    } catch (Throwable) {
        return '-';
    }
}

function admin_performance_detect_lighthouse_command(): ?string
{
    $candidates = [
        APP_ROOT . '/node_modules/.bin/lighthouse',
        '/usr/local/bin/lighthouse',
        '/opt/homebrew/bin/lighthouse',
        'lighthouse',
    ];

    foreach ($candidates as $candidate) {
        if (str_contains($candidate, '/')) {
            if (is_file($candidate) && is_executable($candidate)) {
                return $candidate;
            }

            continue;
        }

        $detected = [];
        $status = 1;
        @exec('command -v ' . escapeshellarg($candidate) . ' 2>/dev/null', $detected, $status);
        if ($status === 0 && !empty($detected[0]) && is_string($detected[0])) {
            return trim($detected[0]);
        }
    }

    return null;
}

function admin_performance_detect_chrome_command(): ?string
{
    $candidates = [
        '/usr/bin/chromium',
        '/usr/bin/chromium-browser',
        '/usr/bin/google-chrome',
        '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome',
    ];

    foreach ($candidates as $candidate) {
        if (is_file($candidate) && is_executable($candidate)) {
            return $candidate;
        }
    }

    return null;
}

function admin_performance_extract_lighthouse_metrics(array $report): array
{
    $audits = is_array($report['audits'] ?? null) ? $report['audits'] : [];

    $lcpMs = $audits['largest-contentful-paint']['numericValue'] ?? null;
    $cls = $audits['cumulative-layout-shift']['numericValue'] ?? null;
    $tbt = $audits['total-blocking-time']['numericValue'] ?? null;

    return [
        'lcp' => is_numeric($lcpMs) ? round(((float) $lcpMs) / 1000, 2) : null,
        'inp' => null,
        'cls' => is_numeric($cls) ? round((float) $cls, 3) : null,
        'tbt' => is_numeric($tbt) ? round((float) $tbt) : null,
    ];
}

function admin_performance_truncate_text(string $text, int $length = 180): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    if (strlen($text) <= $length) {
        return $text;
    }

    return rtrim(substr($text, 0, $length - 3)) . '...';
}

function admin_performance_node_label(mixed $node): ?string
{
    if (!is_array($node)) {
        return null;
    }

    foreach (['snippet', 'selector', 'nodeLabel'] as $key) {
        if (isset($node[$key]) && is_string($node[$key]) && trim($node[$key]) !== '') {
            return admin_performance_truncate_text($node[$key]);
        }
    }

    return null;
}

function admin_performance_extract_lighthouse_diagnostics(array $report): array
{
    $audits = is_array($report['audits'] ?? null) ? $report['audits'] : [];
    $diagnostics = [
        'lcp' => [],
        'cls' => [],
        'tbt' => [],
    ];

    $lcpItems = $audits['largest-contentful-paint-element']['details']['items'] ?? [];
    if (is_array($lcpItems)) {
        foreach ($lcpItems as $item) {
            $nestedItems = is_array($item['items'] ?? null) ? $item['items'] : [$item];
            foreach ($nestedItems as $nestedItem) {
                $label = admin_performance_node_label($nestedItem['node'] ?? null);
                if ($label !== null) {
                    $diagnostics['lcp'][] = 'Element LCP : ' . $label;
                    break 2;
                }
            }
        }
    }

    $lcpDisplay = $audits['largest-contentful-paint-element']['displayValue'] ?? null;
    if ($diagnostics['lcp'] === [] && is_string($lcpDisplay) && trim($lcpDisplay) !== '') {
        $diagnostics['lcp'][] = admin_performance_truncate_text($lcpDisplay);
    }

    $lcpBreakdownItems = $audits['lcp-breakdown-insight']['details']['items'] ?? [];
    if (is_array($lcpBreakdownItems)) {
        $longestSubpart = null;

        foreach ($lcpBreakdownItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (($item['type'] ?? '') === 'node') {
                $label = admin_performance_node_label($item);
                if ($label !== null) {
                    $diagnostics['lcp'][] = 'Element LCP : ' . $label;
                }
            }

            if (($item['type'] ?? '') !== 'table' || !is_array($item['items'] ?? null)) {
                continue;
            }

            foreach ($item['items'] as $subpart) {
                if (!is_array($subpart) || !is_numeric($subpart['duration'] ?? null)) {
                    continue;
                }

                if ($longestSubpart === null || (float) $subpart['duration'] > (float) $longestSubpart['duration']) {
                    $longestSubpart = $subpart;
                }
            }
        }

        if (is_array($longestSubpart)) {
            $subpartLabels = [
                'timeToFirstByte' => 'Temps de reponse serveur',
                'resourceLoadDelay' => 'Delai avant le chargement de la ressource',
                'resourceLoadDuration' => 'Duree de chargement de la ressource',
                'elementRenderDelay' => 'Delai de rendu de l element',
            ];
            $subpart = isset($longestSubpart['subpart']) && is_string($longestSubpart['subpart'])
                ? $longestSubpart['subpart']
                : '';
            $label = $subpartLabels[$subpart] ?? 'Phase LCP';
            $diagnostics['lcp'][] = $label . ' : ' . round((float) $longestSubpart['duration']) . ' ms';
        }
    }

    $lcpDiscoveryItems = $audits['lcp-discovery-insight']['details']['items'] ?? [];
    if (is_array($lcpDiscoveryItems)) {
        foreach ($lcpDiscoveryItems as $item) {
            if (!is_array($item)) {
                continue;
            }

            if (($item['type'] ?? '') === 'node') {
                $label = admin_performance_node_label($item);
                if ($label !== null) {
                    $diagnostics['lcp'][] = 'Ressource LCP : ' . $label;
                }
            }

            if (($item['type'] ?? '') !== 'checklist' || !is_array($item['items'] ?? null)) {
                continue;
            }

            foreach ($item['items'] as $check) {
                if (
                    !is_array($check)
                    || !empty($check['value'])
                    || !isset($check['label'])
                    || !is_string($check['label'])
                ) {
                    continue;
                }

                $checkLabels = [
                    'fetchpriority=high should be applied to the image preload request' => 'Ajouter fetchpriority="high" a la requete de preload de l image',
                    'Request is discoverable in initial document' => 'La ressource LCP doit etre decouverte dans le document initial',
                    'LCP resources should not use loading=lazy' => 'La ressource LCP ne doit pas utiliser loading="lazy"',
                ];
                $diagnostics['lcp'][] = $checkLabels[$check['label']]
                    ?? admin_performance_truncate_text($check['label']);
            }
        }
    }

    $diagnostics['lcp'] = array_values(array_unique($diagnostics['lcp']));

    $layoutShiftItems = $audits['layout-shifts']['details']['items'] ?? [];
    if (is_array($layoutShiftItems)) {
        foreach (array_slice($layoutShiftItems, 0, 3) as $item) {
            $label = admin_performance_node_label($item['node'] ?? null);
            $score = $item['score'] ?? null;
            $message = $label !== null ? 'Element deplace : ' . $label : 'Deplacement de mise en page detecte';

            if (is_numeric($score)) {
                $message .= ' (impact ' . number_format((float) $score, 3, '.', '') . ')';
            }

            $diagnostics['cls'][] = $message;
        }
    }

    $longTaskItems = $audits['long-tasks']['details']['items'] ?? [];
    if (is_array($longTaskItems)) {
        $longTaskItems = array_values(array_filter($longTaskItems, 'is_array'));
        usort($longTaskItems, static fn (array $a, array $b): int => ((float) ($b['duration'] ?? 0)) <=> ((float) ($a['duration'] ?? 0)));

        foreach (array_slice($longTaskItems, 0, 3) as $item) {
            $duration = $item['duration'] ?? null;
            $url = isset($item['url']) && is_string($item['url']) ? $item['url'] : '';
            $message = is_numeric($duration)
                ? 'Tache longue : ' . round((float) $duration) . ' ms'
                : 'Tache longue detectee';

            if ($url !== '') {
                $path = parse_url($url, PHP_URL_PATH);
                $message .= ' - ' . admin_performance_truncate_text(is_string($path) && $path !== '' ? $path : $url, 100);
            }

            $diagnostics['tbt'][] = $message;
        }
    }

    return $diagnostics;
}

function admin_performance_tbt_resource_label(array $messages): ?string
{
    foreach ($messages as $message) {
        if (!is_string($message) || !str_contains($message, ' - ')) {
            continue;
        }

        $resource = trim((string) substr($message, strrpos($message, ' - ') + 3));
        if ($resource === '') {
            continue;
        }

        $path = parse_url($resource, PHP_URL_PATH);
        $candidate = is_string($path) && $path !== '' ? $path : $resource;
        $basename = basename($candidate);

        return $basename !== '' && $basename !== '/' ? $basename : $candidate;
    }

    return null;
}

function admin_performance_tbt_duration_label(array $messages): ?string
{
    foreach ($messages as $message) {
        if (!is_string($message)) {
            continue;
        }

        if (preg_match('/Tache longue :\s*([0-9]+)\s*ms/i', $message, $matches) === 1) {
            return $matches[1] . ' ms';
        }
    }

    return null;
}

function admin_performance_lcp_resource_label(array $messages): ?string
{
    foreach ($messages as $message) {
        if (!is_string($message)) {
            continue;
        }

        if (str_contains($message, 'Ressource LCP : ')) {
            $resource = trim(substr($message, strlen('Ressource LCP : ')));
            if ($resource === '') {
                continue;
            }

            $path = parse_url($resource, PHP_URL_PATH);
            $candidate = is_string($path) && $path !== '' ? $path : $resource;
            $basename = basename($candidate);

            return $basename !== '' && $basename !== '/' ? $basename : $candidate;
        }
    }

    return null;
}

function admin_performance_lcp_element_label(array $messages): ?string
{
    foreach ($messages as $message) {
        if (!is_string($message)) {
            continue;
        }

        if (str_contains($message, 'Element LCP : ')) {
            $label = trim(substr($message, strlen('Element LCP : ')));
            if ($label !== '') {
                return $label;
            }
        }
    }

    return null;
}

function admin_performance_lcp_phase_label(array $messages): ?string
{
    foreach ($messages as $message) {
        if (!is_string($message)) {
            continue;
        }

        if (preg_match('/^(Temps de reponse serveur|Delai avant le chargement de la ressource|Duree de chargement de la ressource|Delai de rendu de l element)\s*:\s*([0-9]+)\s*ms$/i', $message, $matches) === 1) {
            return $matches[1] . ' : ' . $matches[2] . ' ms';
        }
    }

    return null;
}

function admin_performance_compute_summary(array $pages): array
{
    $metrics = ['lcp', 'inp', 'cls', 'tbt'];
    $summary = [];

    foreach ($metrics as $metric) {
        $values = [];

        foreach ($pages as $page) {
            $value = $page[$metric] ?? null;
            if (is_numeric($value)) {
                $values[] = (float) $value;
            }
        }

        $summary[$metric] = $values === [] ? null : match ($metric) {
            'cls' => round(max($values), 3),
            default => round(max($values), $metric === 'lcp' ? 2 : 0),
        };
    }

    return $summary;
}

function admin_performance_metric_score(mixed $value, string $metric): ?int
{
    if (!is_numeric($value)) {
        return null;
    }

    $numericValue = (float) $value;

    return match ($metric) {
        'lcp' => admin_performance_range_score($numericValue, 2.5, 4.0),
        'inp' => admin_performance_range_score($numericValue, 200, 500),
        'cls' => admin_performance_range_score($numericValue, 0.1, 0.25),
        'tbt' => admin_performance_range_score($numericValue, 200, 600),
        default => null,
    };
}

function admin_performance_range_score(float $value, float $goodMax, float $badMin): int
{
    if ($value <= $goodMax) {
        return 100;
    }

    if ($value >= $badMin) {
        return 0;
    }

    $ratio = ($badMin - $value) / ($badMin - $goodMax);

    return (int) round($ratio * 100);
}

function admin_performance_global_score(array $metrics, array $metricKeys): ?int
{
    $scores = [];

    foreach ($metricKeys as $metricKey) {
        $score = admin_performance_metric_score($metrics[$metricKey] ?? null, $metricKey);
        if ($score !== null) {
            $scores[] = $score;
        }
    }

    if ($scores === []) {
        return null;
    }

    return (int) round(array_sum($scores) / count($scores));
}

function admin_performance_score_label(?int $score): string
{
    if ($score === null) {
        return 'En attente';
    }

    if ($score >= 90) {
        return 'Excellent';
    }

    if ($score >= 50) {
        return 'A surveiller';
    }

    return 'A corriger';
}

function admin_performance_score_class(?int $score): string
{
    if ($score === null) {
        return 'is-pending';
    }

    if ($score >= 90) {
        return 'is-excellent';
    }

    if ($score >= 50) {
        return 'is-warning';
    }

    return 'is-danger';
}

function admin_performance_diagnostic_focus(string $metric, array $messages): string
{
    foreach ($messages as $message) {
        if (!is_string($message)) {
            continue;
        }

        if (str_contains($message, 'Element LCP : ')) {
            $resource = admin_performance_lcp_resource_label($messages);
            if ($resource !== null) {
                return $resource;
            }

            return trim(substr($message, strlen('Element LCP : ')));
        }

        if (str_contains($message, 'Ressource LCP : ')) {
            $resource = admin_performance_lcp_resource_label($messages);
            if ($resource !== null) {
                return $resource;
            }

            return trim(substr($message, strlen('Ressource LCP : ')));
        }

        if (str_contains($message, 'Element deplace : ')) {
            return trim(preg_replace('/ \(impact.*$/', '', substr($message, strlen('Element deplace : '))) ?? '');
        }

        if (str_contains($message, 'Tache longue : ')) {
            $resource = admin_performance_tbt_resource_label($messages);
            if ($resource !== null) {
                return $resource;
            }

            return trim(substr($message, strlen('Tache longue : ')));
        }
    }

    return match ($metric) {
        'lcp' => 'Element principal de la page',
        'cls' => 'Elements qui bougent au chargement',
        'tbt' => 'Scripts et travail du thread principal',
        default => 'A investiguer',
    };
}

function admin_performance_diagnostic_action(string $metric, array $messages): string
{
    $text = implode(' ', array_filter($messages, 'is_string'));

    return match ($metric) {
        'lcp' => (function () use ($messages, $text): string {
            $resource = admin_performance_lcp_resource_label($messages);
            $element = admin_performance_lcp_element_label($messages);
            $phase = admin_performance_lcp_phase_label($messages);
            $target = $resource ?? $element ?? 'l element principal';

            if (str_contains($text, 'fetchpriority')) {
                return 'Verifier ' . $target . ' : aligner le preload et ajouter fetchpriority="high" sur la ressource LCP si necessaire.';
            }

            if (str_contains($text, 'loading="lazy"')) {
                return 'Verifier ' . $target . ' : la ressource LCP ne doit pas etre chargee en lazy.';
            }

            if ($phase !== null) {
                return 'Verifier ' . $target . ' : ' . strtolower($phase) . '. Optimiser la priorite reseau, le preload ou le rendu critique selon cette phase.';
            }

            if ($resource !== null) {
                return 'Verifier ' . $resource . ' : preload, priorite reseau, poids du media et delai de rendu du hero.';
            }

            return 'Optimiser ' . $target . ' : image hero, priorite reseau, preload et rendu critique.';
        })(),
        'cls' => 'Reserver l espace des medias et blocs dynamiques, puis verifier les elements qui changent de taille au chargement.',
        'tbt' => (function () use ($messages): string {
            $resource = admin_performance_tbt_resource_label($messages);
            $duration = admin_performance_tbt_duration_label($messages);

            if ($resource !== null && $duration !== null) {
                return 'Verifier ' . $resource . ' : tache longue de ' . $duration . '. Envisager defer, lazy init, decoupage ou reduction du travail au chargement.';
            }

            if ($resource !== null) {
                return 'Verifier ' . $resource . ' et reduire le travail JavaScript au chargement : defer, lazy init ou decoupage.';
            }

            if ($duration !== null) {
                return 'Une tache longue de ' . $duration . ' bloque le thread principal. Identifier le script associe et reduire son travail au chargement.';
            }

            return 'Identifier le script responsable des taches longues et reduire son travail au chargement : defer, lazy init ou decoupage.';
        })(),
        default => 'Relancer une mesure et analyser la ressource signalee.',
    };
}

function admin_performance_display_local_pages(array $pages): array
{
    $localPagesIndex = [];
    foreach ($pages as $page) {
        $pageUrl = isset($page['url']) && is_string($page['url']) ? $page['url'] : '';
        if ($pageUrl !== '') {
            $localPagesIndex[$pageUrl] = $page;
        }
    }

    $displayLocalPages = [];
    foreach (admin_performance_default_pages() as $knownPage) {
        $knownUrl = isset($knownPage['url']) && is_string($knownPage['url']) ? $knownPage['url'] : '';
        if ($knownUrl === '') {
            continue;
        }

        $displayLocalPages[] = $localPagesIndex[$knownUrl] ?? [
            'label' => $knownPage['label'],
            'url' => $knownUrl,
            'lcp' => null,
            'cls' => null,
            'tbt' => null,
            'diagnostics' => [],
            'updatedAt' => null,
            'source' => 'local',
        ];
    }

    return $displayLocalPages;
}

function admin_performance_build_local_diagnostic_groups(array $displayLocalPages): array
{
    $groups = [
        'lcp' => [
            'label' => 'LCP',
            'target' => '2.5 s',
            'items' => [],
        ],
        'cls' => [
            'label' => 'CLS',
            'target' => '0.1',
            'items' => [],
        ],
        'tbt' => [
            'label' => 'TBT',
            'target' => '200 ms',
            'items' => [],
        ],
    ];

    foreach ($displayLocalPages as $localPage) {
        $pageDiagnostics = is_array($localPage['diagnostics'] ?? null) ? $localPage['diagnostics'] : [];

        foreach ($groups as $metric => &$diagnosticGroup) {
            $value = $localPage[$metric] ?? null;
            if (!is_numeric($value)) {
                continue;
            }

            $metricScore = admin_performance_metric_score($value, $metric);
            $messages = is_array($pageDiagnostics[$metric] ?? null) ? $pageDiagnostics[$metric] : [];
            $messages = array_values(array_filter($messages, 'is_string'));

            if ($metricScore !== null && $metricScore >= 90 && $messages === []) {
                continue;
            }

            if ($messages === []) {
                $messages[] = 'La valeur depasse l objectif. Relance une mesure pour obtenir la cause detaillee Lighthouse.';
            }

            $diagnosticGroup['items'][] = [
                'label' => (string) ($localPage['label'] ?? '-'),
                'value' => admin_performance_format_value($value, $metric),
                'score' => $metricScore,
                'focus' => admin_performance_diagnostic_focus($metric, $messages),
                'action' => admin_performance_diagnostic_action($metric, $messages),
                'messages' => $messages,
            ];
        }
        unset($diagnosticGroup);
    }

    return $groups;
}

function admin_performance_run(?string $requestedProfileId = null): array
{
    $pages = admin_performance_default_pages();
    $lighthouse = admin_performance_detect_lighthouse_command();
    $chrome = admin_performance_detect_chrome_command();
    $profile = admin_performance_run_profile($requestedProfileId);

    if ($lighthouse === null) {
        $data = admin_performance_default_data();
        $data['profile'] = $profile['id'];
        $data['status'] = 'error';
        $data['message'] = 'Lighthouse local n est pas disponible. Installez le binaire pour lancer les mesures.';
        $data['updatedAt'] = date(DATE_ATOM);
        $data['pages'] = array_map(static function (array $page): array {
            return [
                'label' => $page['label'],
                'url' => $page['url'],
                'lcp' => null,
                'inp' => null,
                'cls' => null,
                'tbt' => null,
                'diagnostics' => [],
                'updatedAt' => null,
                'source' => 'local',
            ];
        }, $pages);
        admin_performance_save($data);

        return $data;
    }

    if ($chrome === null) {
        $data = admin_performance_default_data();
        $data['profile'] = $profile['id'];
        $data['status'] = 'error';
        $data['message'] = 'Chrome headless n est pas disponible. Installez Chromium ou Google Chrome dans l environnement PHP.';
        $data['updatedAt'] = date(DATE_ATOM);
        $data['pages'] = array_map(static function (array $page): array {
            return [
                'label' => $page['label'],
                'url' => $page['url'],
                'lcp' => null,
                'inp' => null,
                'cls' => null,
                'tbt' => null,
                'diagnostics' => [],
                'updatedAt' => null,
                'source' => 'local',
            ];
        }, $pages);
        admin_performance_save($data);

        return $data;
    }

    $results = [];
    $errors = [];

    foreach ($pages as $page) {
        $outputFile = tempnam(sys_get_temp_dir(), 'lh-admin-');
        if ($outputFile === false) {
            $errors[] = 'Impossible de creer un fichier temporaire pour Lighthouse.';
            continue;
        }

        $command = implode(' ', [
            escapeshellarg($lighthouse),
            escapeshellarg($page['url']),
            '--quiet',
            escapeshellarg('--chrome-flags=--headless=new --no-sandbox --disable-dev-shm-usage'),
            '--chrome-path=' . escapeshellarg($chrome),
            '--output=json',
            '--output-path=' . escapeshellarg($outputFile),
            '--only-categories=performance',
            ...$profile['flags'],
        ]);

        $lines = [];
        $status = 1;
        @exec($command . ' 2>&1', $lines, $status);

        if ($status !== 0 || !is_file($outputFile)) {
            $errors[] = 'Mesure impossible pour ' . $page['label'] . '.';
            @unlink($outputFile);
            continue;
        }

        $report = json_decode((string) file_get_contents($outputFile), true);
        @unlink($outputFile);

        if (!is_array($report)) {
            $errors[] = 'Rapport invalide pour ' . $page['label'] . '.';
            continue;
        }

        $metrics = admin_performance_extract_lighthouse_metrics($report);
        $diagnostics = admin_performance_extract_lighthouse_diagnostics($report);
        $results[] = [
            'label' => $page['label'],
            'url' => $page['url'],
            'lcp' => $metrics['lcp'],
            'inp' => $metrics['inp'],
            'cls' => $metrics['cls'],
            'tbt' => $metrics['tbt'],
            'diagnostics' => $diagnostics,
            'updatedAt' => date(DATE_ATOM),
            'source' => 'lighthouse-local',
        ];
    }

    $data = admin_performance_default_data();
    $data['profile'] = $profile['id'];
    $data['updatedAt'] = date(DATE_ATOM);
    $data['pages'] = $results === [] ? array_map(static function (array $page): array {
        return [
            'label' => $page['label'],
            'url' => $page['url'],
            'lcp' => null,
            'inp' => null,
            'cls' => null,
            'tbt' => null,
            'diagnostics' => [],
            'updatedAt' => null,
            'source' => 'local',
        ];
    }, $pages) : $results;
    $data['summary'] = admin_performance_compute_summary($results);
    $data['status'] = $errors === [] ? 'success' : ($results === [] ? 'error' : 'partial');
    $data['message'] = $errors === [] ? 'Mesure locale terminee.' : implode(' ', $errors);
    admin_performance_save($data);

    return $data;
}
