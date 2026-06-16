<?php
declare(strict_types=1);

require_once STARTER_ROOT . '/method.php';

$uploadsDir = WEB_UPLOADS_ROOT;
$assetsDir = WEB_ASSETS_ROOT;
$sizesFile = STARTER_ROOT . '/image-sizes.json';
$manifestFile = dirname(__DIR__) . '/.image-sizes-generated.json';
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
$convertExtensions = ['jpg', 'jpeg', 'png'];

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

if (!function_exists('generate')) {
    function generate(bool $force = false): array
    {
        global $uploadsDir, $sizesFile, $manifestFile, $allowedExtensions, $convertExtensions;

        $result = [
            'created' => 0,
            'skipped' => 0,
            'deleted' => 0,
            'errors' => [],
        ];

        if (!class_exists('Imagick')) {
            $result['errors'][] = 'Imagick n est pas disponible sur ce PHP.';
            return $result;
        }

        $config = image_config($sizesFile);
        $sizes = $config['sizes'];
        $defaults = $config['defaults'];
        $manifest = image_manifest($manifestFile);
        $previousSizes = array_keys($manifest['sizes'] ?? []);
        $removedSizes = array_diff($previousSizes, array_keys($sizes));

        ensure_directory($uploadsDir);
        delete_removed_sizes($uploadsDir, $allowedExtensions, $manifest, $removedSizes, $result);

        $generatedBySource = $manifest['generated'] ?? [];
        remove_size_entries($generatedBySource, $removedSizes);
        $generatedFiles = generated_files($generatedBySource);

        foreach (upload_images($uploadsDir, $allowedExtensions, $generatedFiles, array_merge($previousSizes, array_keys($sizes))) as $source) {
            $relativeSource = relative_path($uploadsDir, $source);
            $generatedBySource[$relativeSource] = $generatedBySource[$relativeSource] ?? [];
            $extension = strtolower(pathinfo($source, PATHINFO_EXTENSION));

            if (in_array($extension, $convertExtensions, true)) {
                $target = image_webp_path($source);
                $relativeTarget = relative_path($uploadsDir, $target);

                if (!$force && generated_image_is_fresh($source, $target)) {
                    $generatedBySource[$relativeSource]['full'] = $relativeTarget;
                    $result['skipped']++;
                } elseif (crop_image($source, $target, $defaults)) {
                    $generatedBySource[$relativeSource]['full'] = $relativeTarget;
                    $result['created']++;
                } else {
                    $result['errors'][] = 'Impossible de generer ' . $relativeTarget;
                }
            }

            foreach ($sizes as $name => $size) {
                $target = image_variant_path($source, $name);
                $relativeTarget = relative_path($uploadsDir, $target);
                delete_legacy_variant($source, $name, $result);

                if (!$force && generated_image_is_fresh($source, $target)) {
                    $generatedBySource[$relativeSource][$name] = $relativeTarget;
                    $result['skipped']++;
                    continue;
                }

                if (crop_image($source, $target, $size)) {
                    $generatedBySource[$relativeSource][$name] = $relativeTarget;
                    $result['created']++;
                } else {
                    $result['errors'][] = 'Impossible de generer ' . $relativeTarget;
                }
            }
        }

        $manifest = [
            'sizes' => $sizes,
            'generated' => remove_missing_sources($uploadsDir, $generatedBySource, $result),
            'updatedAt' => date(DATE_ATOM),
        ];

        save_image_manifest($manifestFile, $manifest);

        return $result;
    }
}

if (!function_exists('clean')) {
    function clean(): array
    {
        global $uploadsDir, $sizesFile, $manifestFile, $allowedExtensions;

        $result = [
            'created' => 0,
            'skipped' => 0,
            'deleted' => 0,
            'errors' => [],
        ];

        $sizes = image_sizes($sizesFile);
        $manifest = image_manifest($manifestFile);
        $generatedBySource = remove_missing_sources($uploadsDir, $manifest['generated'] ?? [], $result);

        $knownSizes = array_unique(array_merge(array_keys($manifest['sizes'] ?? []), array_keys($sizes)));
        $sourceFiles = upload_images($uploadsDir, $allowedExtensions, generated_files($generatedBySource), $knownSizes);
        $sourceLookup = array_fill_keys(array_map(fn ($file) => relative_path($uploadsDir, $file), $sourceFiles), true);

        foreach (upload_images($uploadsDir, $allowedExtensions, [], []) as $file) {
            $relative = relative_path($uploadsDir, $file);
            if (isset($sourceLookup[$relative])) {
                continue;
            }

            if (strtolower(pathinfo($file, PATHINFO_EXTENSION)) !== 'webp' && is_variant_name($file, $knownSizes)) {
                delete_file($file, $result);
                continue;
            }

            $source = original_from_variant($uploadsDir, $file, $knownSizes);
            if ($source !== null && !is_file($source)) {
                delete_file($file, $result);
            }
        }

        $manifest['sizes'] = $sizes;
        $manifest['generated'] = $generatedBySource;
        $manifest['updatedAt'] = date(DATE_ATOM);
        save_image_manifest($manifestFile, $manifest);

        return $result;
    }
}

if (!function_exists('image_sizes')) {
    function image_sizes(string $file): array
    {
        return image_config($file)['sizes'];
    }
}

if (!function_exists('image_defaults')) {
    function image_defaults(string $file): array
    {
        return image_config($file)['defaults'];
    }
}

if (!function_exists('image_config')) {
    function image_config(string $file): array
    {
        $json = [];
        if (!is_file($file)) {
            $defaults = default_image_options();
        } else {
            $json = json_decode((string) file_get_contents($file), true);
            $json = is_array($json) ? $json : [];
            $defaults = image_options(is_array($json['_defaults'] ?? null) ? $json['_defaults'] : []);
        }

        $registeredSizes = function_exists('starter_registered_image_sizes')
            ? starter_registered_image_sizes()
            : [];
        $sizes = [];
        foreach ($registeredSizes as $name => $config) {
            $values = is_array($config) ? $config : [];

            $width = (int) ($values['width'] ?? 0);
            $height = (int) ($values['height'] ?? 0);

            if (!is_string($name) || !preg_match('/^[A-Za-z0-9_-]+$/', $name) || $width <= 0 || $height <= 0) {
                continue;
            }

            $sizes[$name] = array_replace($defaults, image_options($values), [
                'width' => $width,
                'height' => $height,
            ]);
        }

        return ['defaults' => $defaults, 'sizes' => $sizes];
    }
}

if (!function_exists('default_image_options')) {
    function default_image_options(): array
    {
        return [
            'fit' => 'cover',
            'position' => 'center',
            'quality' => 85,
            'filter' => 'lanczos',
            'sharpen' => 0.2,
        ];
    }
}

if (!function_exists('image_options')) {
    function image_options(array $values): array
    {
        $fit = (string) ($values['fit'] ?? 'cover');
        $position = (string) ($values['position'] ?? 'center');
        $quality = (int) ($values['quality'] ?? 85);
        $sharpen = (float) ($values['sharpen'] ?? 0.2);

        return [
            'fit' => in_array($fit, ['cover', 'contain'], true) ? $fit : 'cover',
            'position' => in_array($position, array_keys(image_gravity_map()), true) ? $position : 'center',
            'quality' => max(1, min(100, $quality)),
            'filter' => 'lanczos',
            'sharpen' => max(0, min(10, $sharpen)),
        ];
    }
}

if (!function_exists('save_image_settings')) {
    function save_image_settings(string $file, array $post): void
    {
        $settings = [
            '_defaults' => image_options([
                'quality' => $post['default_quality'] ?? 85,
                'sharpen' => 0.2,
            ]),
        ];

        file_put_contents($file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

if (!function_exists('image_manifest')) {
    function image_manifest(string $file): array
    {
        if (!is_file($file)) {
            return ['sizes' => [], 'generated' => []];
        }

        $json = json_decode((string) file_get_contents($file), true);

        return is_array($json) ? $json : ['sizes' => [], 'generated' => []];
    }
}

if (!function_exists('save_image_manifest')) {
    function save_image_manifest(string $file, array $manifest): void
    {
        file_put_contents($file, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}

if (!function_exists('generated_image_is_fresh')) {
    function generated_image_is_fresh(string $source, string $target): bool
    {
        if (!is_file($target)) {
            return false;
        }

        $sourceTime = filemtime($source);
        $targetTime = filemtime($target);

        return $sourceTime !== false && $targetTime !== false && $targetTime >= $sourceTime;
    }
}

if (!function_exists('ensure_directory')) {
    function ensure_directory(string $directory): void
    {
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }
}

if (!function_exists('upload_images')) {
    function upload_images(string $directory, array $extensions, array $generatedFiles, array $sizeNames): array
    {
        if (!is_dir($directory)) {
            return [];
        }

        $files = [];
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS));

        foreach ($iterator as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $path = $file->getPathname();
            $extension = strtolower($file->getExtension());
            $relative = relative_path($directory, $path);

            if (!in_array($extension, $extensions, true) || isset($generatedFiles[$relative])) {
                continue;
            }

            if (is_variant_name($path, $sizeNames)) {
                continue;
            }

            $files[] = $path;
        }

        return $files;
    }
}

if (!function_exists('generated_files')) {
    function generated_files(array $generatedBySource): array
    {
        $files = [];

        foreach ($generatedBySource as $sizes) {
            if (!is_array($sizes)) {
                continue;
            }

            foreach ($sizes as $file) {
                if (is_string($file) && $file !== '') {
                    $files[$file] = true;
                }
            }
        }

        return $files;
    }
}

if (!function_exists('delete_removed_sizes')) {
    function delete_removed_sizes(string $uploadsDir, array $extensions, array $manifest, array $removedSizes, array &$result): void
    {
        if ($removedSizes === []) {
            return;
        }

        foreach (upload_images($uploadsDir, $extensions, [], []) as $file) {
            if (is_variant_name($file, $removedSizes)) {
                delete_file($file, $result);
            }
        }

        foreach (($manifest['generated'] ?? []) as $sizes) {
            if (!is_array($sizes)) {
                continue;
            }

            foreach ($removedSizes as $size) {
                if (empty($sizes[$size]) || !is_string($sizes[$size])) {
                    continue;
                }

                delete_file($uploadsDir . '/' . $sizes[$size], $result);
            }
        }
    }
}

if (!function_exists('remove_size_entries')) {
    function remove_size_entries(array &$generatedBySource, array $removedSizes): void
    {
        if ($removedSizes === []) {
            return;
        }

        foreach ($generatedBySource as $source => $sizes) {
            if (!is_array($sizes)) {
                unset($generatedBySource[$source]);
                continue;
            }

            foreach ($removedSizes as $size) {
                unset($sizes[$size]);
            }

            $generatedBySource[$source] = $sizes;
        }
    }
}

if (!function_exists('remove_missing_sources')) {
    function remove_missing_sources(string $uploadsDir, array $generatedBySource, array &$result): array
    {
        foreach ($generatedBySource as $source => $sizes) {
            if (is_file($uploadsDir . '/' . $source)) {
                continue;
            }

            if (is_array($sizes)) {
                foreach ($sizes as $file) {
                    if (is_string($file)) {
                        delete_file($uploadsDir . '/' . $file, $result);
                    }
                }
            }

            unset($generatedBySource[$source]);
        }

        return $generatedBySource;
    }
}

if (!function_exists('image_variant_path')) {
    function image_variant_path(string $source, string $sizeName): string
    {
        $info = pathinfo($source);

        return $info['dirname'] . '/' . $info['filename'] . '-' . $sizeName . '.webp';
    }
}

if (!function_exists('delete_legacy_variant')) {
    function delete_legacy_variant(string $source, string $sizeName, array &$result): void
    {
        $info = pathinfo($source);
        $extension = $info['extension'] ?? '';

        if ($extension === '' || strtolower($extension) === 'webp') {
            return;
        }

        delete_file($info['dirname'] . '/' . $info['filename'] . '-' . $sizeName . '.' . $extension, $result);
    }
}

if (!function_exists('image_webp_path')) {
    function image_webp_path(string $source): string
    {
        $info = pathinfo($source);

        return $info['dirname'] . '/' . $info['filename'] . '.webp';
    }
}

if (!function_exists('is_variant_name')) {
    function is_variant_name(string $file, array $sizeNames): bool
    {
        $name = pathinfo($file, PATHINFO_FILENAME);

        foreach ($sizeNames as $sizeName) {
            if ($sizeName !== '' && str_ends_with($name, '-' . $sizeName)) {
                return true;
            }
        }

        return false;
    }
}

if (!function_exists('original_from_variant')) {
    function original_from_variant(string $uploadsDir, string $file, array $sizeNames): ?string
    {
        $info = pathinfo($file);

        foreach ($sizeNames as $sizeName) {
            $suffix = '-' . $sizeName;
            if (!str_ends_with($info['filename'], $suffix)) {
                continue;
            }

            return $info['dirname'] . '/' . substr($info['filename'], 0, -strlen($suffix)) . '.' . $info['extension'];
        }

        return null;
    }
}

if (!function_exists('image_filter_map')) {
    function image_filter_map(): array
    {
        if (!class_exists('Imagick')) {
            return array_fill_keys([
                'point',
                'box',
                'triangle',
                'hermite',
                'hanning',
                'hamming',
                'blackman',
                'gaussian',
                'quadratic',
                'cubic',
                'catrom',
                'mitchell',
                'lanczos',
            ], 0);
        }

        return [
            'point' => Imagick::FILTER_POINT,
            'box' => Imagick::FILTER_BOX,
            'triangle' => Imagick::FILTER_TRIANGLE,
            'hermite' => Imagick::FILTER_HERMITE,
            'hanning' => Imagick::FILTER_HANNING,
            'hamming' => Imagick::FILTER_HAMMING,
            'blackman' => Imagick::FILTER_BLACKMAN,
            'gaussian' => Imagick::FILTER_GAUSSIAN,
            'quadratic' => Imagick::FILTER_QUADRATIC,
            'cubic' => Imagick::FILTER_CUBIC,
            'catrom' => Imagick::FILTER_CATROM,
            'mitchell' => Imagick::FILTER_MITCHELL,
            'lanczos' => Imagick::FILTER_LANCZOS,
        ];
    }
}

if (!function_exists('image_gravity_map')) {
    function image_gravity_map(): array
    {
        if (!class_exists('Imagick')) {
            return array_fill_keys([
                'top-left',
                'top',
                'top-right',
                'left',
                'center',
                'right',
                'bottom-left',
                'bottom',
                'bottom-right',
            ], 0);
        }

        return [
            'top-left' => Imagick::GRAVITY_NORTHWEST,
            'top' => Imagick::GRAVITY_NORTH,
            'top-right' => Imagick::GRAVITY_NORTHEAST,
            'left' => Imagick::GRAVITY_WEST,
            'center' => Imagick::GRAVITY_CENTER,
            'right' => Imagick::GRAVITY_EAST,
            'bottom-left' => Imagick::GRAVITY_SOUTHWEST,
            'bottom' => Imagick::GRAVITY_SOUTH,
            'bottom-right' => Imagick::GRAVITY_SOUTHEAST,
        ];
    }
}

if (!function_exists('image_position_offset')) {
    function image_position_offset(string $position, int $sourceWidth, int $sourceHeight, int $targetWidth, int $targetHeight, bool $invert = false): array
    {
        $x = match ($position) {
            'top-right', 'right', 'bottom-right' => max(0, $sourceWidth - $targetWidth),
            'top', 'center', 'bottom' => max(0, (int) floor(($sourceWidth - $targetWidth) / 2)),
            default => 0,
        };

        $y = match ($position) {
            'bottom-left', 'bottom', 'bottom-right' => max(0, $sourceHeight - $targetHeight),
            'left', 'center', 'right' => max(0, (int) floor(($sourceHeight - $targetHeight) / 2)),
            default => 0,
        };

        return $invert ? [-$x, -$y] : [$x, $y];
    }
}

if (!function_exists('crop_image')) {
    function crop_image(string $source, string $target, ?array $size): bool
    {
        try {
            ensure_directory(dirname($target));

            $image = new Imagick($source);
            $image->autoOrient();
            $image->setImageColorspace(Imagick::COLORSPACE_SRGB);
            $options = array_replace(default_image_options(), $size ?? []);
            $filter = image_filter_map()[$options['filter']] ?? Imagick::FILTER_LANCZOS;

            if (!empty($size['width']) && !empty($size['height'])) {
                $width = (int) $size['width'];
                $height = (int) $size['height'];
                $sourceWidth = $image->getImageWidth();
                $sourceHeight = $image->getImageHeight();

                if (($size['fit'] ?? 'cover') === 'contain') {
                    $image->resizeImage($width, $height, $filter, 1, true);
                    [$x, $y] = image_position_offset(
                        $options['position'],
                        $width,
                        $height,
                        $image->getImageWidth(),
                        $image->getImageHeight(),
                        true
                    );
                    $image->extentImage($width, $height, $x, $y);
                } else {
                    $ratio = max($width / $sourceWidth, $height / $sourceHeight);
                    $image->resizeImage((int) ceil($sourceWidth * $ratio), (int) ceil($sourceHeight * $ratio), $filter, 1);
                    [$x, $y] = image_position_offset(
                        $options['position'],
                        $image->getImageWidth(),
                        $image->getImageHeight(),
                        $width,
                        $height
                    );
                    $image->cropImage($width, $height, $x, $y);
                    $image->setImagePage(0, 0, 0, 0);
                }
            }

            if ($options['sharpen'] > 0) {
                $image->sharpenImage(0, (float) $options['sharpen']);
            }

            $image->setImageFormat('webp');
            $image->stripImage();
            $image->setImageCompressionQuality((int) $options['quality']);
            $image->writeImage($target);
            $image->clear();
            $image->destroy();

            return true;
        } catch (Throwable) {
            return false;
        }
    }
}

if (!function_exists('delete_file')) {
    function delete_file(string $file, array &$result): void
    {
        if (is_file($file) && unlink($file)) {
            $result['deleted']++;
        }
    }
}

if (!function_exists('relative_path')) {
    function relative_path(string $root, string $file): string
    {
        return ltrim(str_replace('\\', '/', substr($file, strlen(rtrim($root, '/')))), '/');
    }
}

if (!function_exists('admin_webp_status')) {
    function admin_webp_status(string $uploadsDir, string $manifestFile, array $allowedExtensions): array
    {
        $manifest = image_manifest($manifestFile);
        $generatedBySource = is_array($manifest['generated'] ?? null) ? $manifest['generated'] : [];
        $generatedLookup = generated_files($generatedBySource);
        $knownSizes = array_keys(is_array($manifest['sizes'] ?? null) ? $manifest['sizes'] : []);
        $sources = upload_images($uploadsDir, $allowedExtensions, $generatedLookup, $knownSizes);
        $generated = 0;
        $missing = 0;
        $stale = 0;
        $bytes = 0;

        foreach ($generatedBySource as $relativeSource => $variants) {
            if (!is_array($variants)) {
                continue;
            }

            $source = rtrim($uploadsDir, '/') . '/' . ltrim((string) $relativeSource, '/');

            foreach ($variants as $relativeVariant) {
                if (!is_string($relativeVariant) || $relativeVariant === '') {
                    continue;
                }

                $target = rtrim($uploadsDir, '/') . '/' . ltrim($relativeVariant, '/');
                if (!is_file($target)) {
                    $missing++;
                    continue;
                }

                $generated++;
                $size = filesize($target);
                if ($size !== false) {
                    $bytes += $size;
                }

                if (is_file($source) && !generated_image_is_fresh($source, $target)) {
                    $stale++;
                }
            }
        }

        return [
            'imagick' => class_exists('Imagick'),
            'sources' => count($sources),
            'generated' => $generated,
            'missing' => $missing,
            'stale' => $stale,
            'bytes' => $bytes,
            'updated_at' => is_string($manifest['updatedAt'] ?? null) ? $manifest['updatedAt'] : '',
        ];
    }
}

$result = null;
$message = '';
$action = $_POST['action'] ?? '';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    if ($action === 'save_settings') {
        save_image_settings($sizesFile, $_POST);
        $message = 'Reglages enregistres.';
    } elseif (in_array($action, ['generate', 'regenerate', 'clean'], true)) {
        $result = match ($action) {
            'regenerate' => generate(true),
            'clean' => clean(),
            default => generate(false),
        };
    }
}

$config = image_config($sizesFile);
$defaults = $config['defaults'];
$webpStatus = admin_webp_status($uploadsDir, $manifestFile, $allowedExtensions);
$cssBundleManifest = admin_css_bundle_manifest($assetsDir);
$cssBundles = is_array($cssBundleManifest['bundles'] ?? null) ? $cssBundleManifest['bundles'] : [];
$assetGauges = [
    admin_asset_gauge($assetsDir, 'CSS critique', 'critical.css', 14 * 1024),
    admin_asset_gauge($assetsDir, 'Bundle common CSS', $cssBundles['common'] ?? 'common.css', 80 * 1024, isset($cssBundles['common'])),
    admin_asset_gauge($assetsDir, 'Bundle app JS', 'app.js', 120 * 1024),
];

ob_start();
?>
<?php if ($message !== '') : ?>
    <div class="admin-notice">
        <p><?= admin_escape($message) ?></p>
    </div>
<?php endif; ?>

<section class="admin-card-grid" aria-label="Etat des images WebP">
    <article class="admin-stat-card">
        <p class="admin-stat-label">Imagick</p>
        <p class="admin-stat-value"><?= $webpStatus['imagick'] ? 'Actif' : 'Indisponible' ?></p>
        <p class="admin-stat-description">Moteur PHP utilise pour les conversions.</p>
    </article>
    <article class="admin-stat-card">
        <p class="admin-stat-label">Sources</p>
        <p class="admin-stat-value"><?= (int) $webpStatus['sources'] ?></p>
        <p class="admin-stat-description">Images originales detectees dans les uploads.</p>
    </article>
    <article class="admin-stat-card">
        <p class="admin-stat-label">WebP generes</p>
        <p class="admin-stat-value"><?= (int) $webpStatus['generated'] ?></p>
        <p class="admin-stat-description"><?= admin_escape(admin_format_bytes((int) $webpStatus['bytes'])) ?> sur le disque.</p>
    </article>
    <article class="admin-stat-card">
        <p class="admin-stat-label">A traiter</p>
        <p class="admin-stat-value"><?= (int) $webpStatus['missing'] + (int) $webpStatus['stale'] ?></p>
        <p class="admin-stat-description"><?= (int) $webpStatus['missing'] ?> manquants, <?= (int) $webpStatus['stale'] ?> obsoletes.</p>
    </article>
</section>

<section class="admin-panel">
    <div class="admin-panel-heading">
        <div>
            <h2>Generation WebP</h2>
            <p class="admin-panel-meta">
                <?php if ($webpStatus['updated_at'] !== '') : ?>
                    Derniere operation : <?= admin_escape(date('d/m/Y H:i', strtotime($webpStatus['updated_at']))) ?>
                <?php else : ?>
                    Aucune generation enregistree.
                <?php endif; ?>
            </p>
        </div>
    </div>

    <?php if ($result !== null) : ?>
        <div class="admin-notice<?= !empty($result['errors']) ? ' is-error' : '' ?>">
            <p>
                Creees: <?= (int) $result['created'] ?>,
                ignorees: <?= (int) $result['skipped'] ?>,
                supprimees: <?= (int) $result['deleted'] ?>
            </p>

            <?php if (!empty($result['errors'])) : ?>
                <ul class="admin-errors">
                    <?php foreach ($result['errors'] as $error) : ?>
                        <li><?= admin_escape($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <form class="admin-form" method="post">
        <div class="admin-actions">
            <button class="admin-button" type="submit" name="action" value="generate">Generate</button>
            <button class="admin-button is-secondary" type="submit" name="action" value="regenerate">Regenerate</button>
            <button class="admin-button is-secondary" type="submit" name="action" value="clean">Clean</button>
        </div>
    </form>
</section>

<section class="admin-panel">
    <h2>Fonctionnement</h2>
    <div class="admin-card-grid">
        <article class="admin-card">
            <h3>Generate</h3>
            <p>Genere uniquement les fichiers absents ou plus anciens que leur source.</p>
        </article>
        <article class="admin-card">
            <h3>Regenerate</h3>
            <p>Recree toutes les variantes avec les reglages actuels.</p>
        </article>
        <article class="admin-card">
            <h3>Clean</h3>
            <p>Supprime les variantes dont la source ou le format configure n existe plus.</p>
        </article>
    </div>
</section>

<section class="admin-panel">
    <h2>Reglages</h2>

    <form class="admin-form" method="post">
        <fieldset class="admin-fieldset">
            <legend>Compression WebP</legend>

            <div class="admin-field-grid">
                <label class="admin-label">
                    <input class="admin-input" type="number" name="default_quality" min="1" max="100" value="<?= (int) $defaults['quality'] ?>">
                </label>
            </div>
        </fieldset>

        <div class="admin-actions">
            <button class="admin-button" type="submit" name="action" value="save_settings">Save settings</button>
        </div>
    </form>
</section>

<?php

return [
    'title' => 'Admin - Images',
    'heading' => 'Images',
    'intro' => 'Configuration, generation et nettoyage des variantes d images du projet.',
    'content' => (string) ob_get_clean(),
];
