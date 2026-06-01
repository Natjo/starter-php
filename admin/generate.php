<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$uploadsDir = $root . '/dist/uploads';
$sizesFile = $root . '/image-sizes.json';
$manifestFile = __DIR__ . '/.image-sizes-generated.json';
$allowedExtensions = ['jpg', 'jpeg', 'png', 'webp'];
$convertExtensions = ['jpg', 'jpeg', 'png'];

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
        $result['errors'][] = 'Imagick n’est pas disponible sur ce PHP.';
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

            if (!$force && is_file($target)) {
                $generatedBySource[$relativeSource]['full'] = $relativeTarget;
                $result['skipped']++;
            } elseif (crop_image($source, $target, $defaults)) {
                $generatedBySource[$relativeSource]['full'] = $relativeTarget;
                $result['created']++;
            } else {
                $result['errors'][] = 'Impossible de générer ' . $relativeTarget;
            }
        }

        foreach ($sizes as $name => $size) {
            $target = image_variant_path($source, $name);
            $relativeTarget = relative_path($uploadsDir, $target);
            delete_legacy_variant($source, $name, $result);

            if (!$force && is_file($target)) {
                $generatedBySource[$relativeSource][$name] = $relativeTarget;
                $result['skipped']++;
                continue;
            }

            if (crop_image($source, $target, $size)) {
                $generatedBySource[$relativeSource][$name] = $relativeTarget;
                $result['created']++;
            } else {
                $result['errors'][] = 'Impossible de générer ' . $relativeTarget;
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

function image_sizes(string $file): array
{
    return image_config($file)['sizes'];
}

function image_defaults(string $file): array
{
    return image_config($file)['defaults'];
}

function image_config(string $file): array
{
    if (!is_file($file)) {
        return ['defaults' => default_image_options(), 'sizes' => []];
    }

    $json = json_decode((string) file_get_contents($file), true);
    if (!is_array($json)) {
        return ['defaults' => default_image_options(), 'sizes' => []];
    }

    $defaults = image_options(is_array($json['_defaults'] ?? null) ? $json['_defaults'] : []);
    $sizes = [];
    foreach ($json as $name => $config) {
        if ($name === '_defaults') {
            continue;
        }

        $values = is_array($config) && array_is_list($config)
            ? ['width' => $config[0] ?? 0, 'height' => $config[1] ?? 0]
            : (is_array($config) ? $config : []);

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

function save_image_settings(string $file, array $post): void
{
    $settings = [
        '_defaults' => image_options([
            'quality' => $post['default_quality'] ?? 85,
            'sharpen' => 0.2,
        ]),
    ];

    $names = is_array($post['name'] ?? null) ? $post['name'] : [];
    $widths = is_array($post['width'] ?? null) ? $post['width'] : [];
    $heights = is_array($post['height'] ?? null) ? $post['height'] : [];
    $fits = is_array($post['fit'] ?? null) ? $post['fit'] : [];
    $positions = is_array($post['position'] ?? null) ? $post['position'] : [];
    $sharpens = is_array($post['sharpen'] ?? null) ? $post['sharpen'] : [];

    foreach ($names as $index => $name) {
        $name = preg_replace('/[^A-Za-z0-9_-]/', '', (string) $name);
        $width = (int) ($widths[$index] ?? 0);
        $height = (int) ($heights[$index] ?? 0);

        if ($name === '' || $width <= 0 || $height <= 0) {
            continue;
        }

        $options = image_options([
            'fit' => $fits[$index] ?? 'cover',
            'position' => $positions[$index] ?? 'center',
            'quality' => $settings['_defaults']['quality'],
            'sharpen' => $sharpens[$index] ?? $settings['_defaults']['sharpen'],
        ]);

        $settings[$name] = [
            'width' => $width,
            'height' => $height,
            'fit' => $options['fit'],
            'position' => $options['position'],
            'sharpen' => $options['sharpen'],
        ];
    }

    file_put_contents($file, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function image_manifest(string $file): array
{
    if (!is_file($file)) {
        return ['sizes' => [], 'generated' => []];
    }

    $json = json_decode((string) file_get_contents($file), true);

    return is_array($json) ? $json : ['sizes' => [], 'generated' => []];
}

function save_image_manifest(string $file, array $manifest): void
{
    file_put_contents($file, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function ensure_directory(string $directory): void
{
    if (!is_dir($directory)) {
        mkdir($directory, 0775, true);
    }
}

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

    foreach (($manifest['generated'] ?? []) as $source => $sizes) {
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

function image_variant_path(string $source, string $sizeName): string
{
    $info = pathinfo($source);

    return $info['dirname'] . '/' . $info['filename'] . '-' . $sizeName . '.webp';
}

function delete_legacy_variant(string $source, string $sizeName, array &$result): void
{
    $info = pathinfo($source);
    $extension = $info['extension'] ?? '';

    if ($extension === '' || strtolower($extension) === 'webp') {
        return;
    }

    delete_file($info['dirname'] . '/' . $info['filename'] . '-' . $sizeName . '.' . $extension, $result);
}

function image_webp_path(string $source): string
{
    $info = pathinfo($source);

    return $info['dirname'] . '/' . $info['filename'] . '.webp';
}

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

function delete_file(string $file, array &$result): void
{
    if (is_file($file) && unlink($file)) {
        $result['deleted']++;
    }
}

function relative_path(string $root, string $file): string
{
    return ltrim(str_replace('\\', '/', substr($file, strlen(rtrim($root, '/')))), '/');
}

$result = null;
$message = '';
$action = $_POST['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'save_settings') {
        save_image_settings($sizesFile, $_POST);
        $message = 'Réglages enregistrés.';
    } else {
        $result = match ($action) {
            'regenerate' => generate(true),
            'clean' => clean(),
            default => generate(false),
        };
    }
}

$config = image_config($sizesFile);
$defaults = $config['defaults'];
$sizes = $config['sizes'];
$sizes[''] = array_replace($defaults, ['width' => '', 'height' => '']);
?>

<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Generate images</title>
</head>
<body>
    <main>
        <h1>Images</h1>

        <?php if ($message !== '') : ?>
            <p><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ($result !== null) : ?>
            <p>
                Créées: <?= (int) $result['created'] ?>,
                ignorées: <?= (int) $result['skipped'] ?>,
                supprimées: <?= (int) $result['deleted'] ?>
            </p>

            <?php if (!empty($result['errors'])) : ?>
                <ul>
                    <?php foreach ($result['errors'] as $error) : ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>

        <form method="post">
            <button type="submit" name="action" value="generate">Generate</button>
            <button type="submit" name="action" value="regenerate">Regenerate</button>
            <button type="submit" name="action" value="clean">Clean</button>
        </form>

        <h2>Settings</h2>

        <form method="post">
            <fieldset>
                <legend>Defaults</legend>

                <label>
                    WebP quality
                    <input type="number" name="default_quality" min="1" max="100" value="<?= (int) $defaults['quality'] ?>">
                </label>

                <label>
                    Filter
                    <input type="text" value="lanczos" readonly>
                </label>
            </fieldset>

            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Width</th>
                        <th>Height</th>
                        <th>Fit</th>
                        <th>Position</th>
                        <th>Sharpen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sizes as $name => $size) : ?>
                        <tr>
                            <td><input name="name[]" value="<?= htmlspecialchars((string) $name, ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td><input type="number" name="width[]" min="1" value="<?= htmlspecialchars((string) $size['width'], ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td><input type="number" name="height[]" min="1" value="<?= htmlspecialchars((string) $size['height'], ENT_QUOTES, 'UTF-8') ?>"></td>
                            <td>
                                <select name="fit[]">
                                    <?php foreach (['cover', 'contain'] as $fit) : ?>
                                        <option value="<?= $fit ?>"<?= $size['fit'] === $fit ? ' selected' : '' ?>><?= $fit ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td>
                                <select name="position[]">
                                    <?php foreach (array_keys(image_gravity_map()) as $position) : ?>
                                        <option value="<?= htmlspecialchars($position, ENT_QUOTES, 'UTF-8') ?>"<?= $size['position'] === $position ? ' selected' : '' ?>><?= htmlspecialchars($position, ENT_QUOTES, 'UTF-8') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" name="sharpen[]" min="0" max="10" step="0.1" value="<?= htmlspecialchars((string) $size['sharpen'], ENT_QUOTES, 'UTF-8') ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" name="action" value="save_settings">Save settings</button>
        </form>
    </main>
</body>
</html>
