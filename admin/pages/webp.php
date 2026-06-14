<?php
declare(strict_types=1);

function admin_webp_filters(): array
{
    $filters = [
        'lanczos' => [
            'label' => 'Lanczos',
            'keyword' => 'Photo detaillee',
            'description' => 'Tres precis et detaille. Recommande pour les photographies et les images riches en textures.',
            'imagick' => 'FILTER_LANCZOS',
        ],
        'catrom' => [
            'label' => 'Catmull-Rom',
            'keyword' => 'Illustration ou interface avec contours nets',
            'description' => 'Accentue les contours et donne un rendu plus net. Utile pour les interfaces, illustrations et visuels graphiques.',
            'imagick' => 'FILTER_CATROM',
        ],
        'mitchell' => [
            'label' => 'Mitchell',
            'keyword' => 'Image mixte',
            'description' => 'Compromis doux entre nettete et absence d artefacts. Adapte aux images mixtes contenant photo, texte et illustration.',
            'imagick' => 'FILTER_MITCHELL',
        ],
        'box' => [
            'label' => 'Box',
            'keyword' => 'Petite reduction rapide',
            'description' => 'Moyenne simple des pixels voisins. Rapide, mais moins precise. Utile pour de petites reductions sans exigence de detail.',
            'imagick' => 'FILTER_BOX',
        ],
        'hermite' => [
            'label' => 'Hermite',
            'keyword' => 'Aplats et portraits doux',
            'description' => 'Produit un rendu doux avec peu d artefacts. Convient aux portraits, aplats et images qui ne doivent pas etre trop accentuees.',
            'imagick' => 'FILTER_HERMITE',
        ],
        'gaussian' => [
            'label' => 'Gaussian',
            'keyword' => 'Image bruitee ou fortement reduite',
            'description' => 'Lisse fortement les transitions et limite le crénelage. Utile avant une forte reduction ou sur une image bruitee.',
            'imagick' => 'FILTER_GAUSSIAN',
        ],
        'cubic' => [
            'label' => 'Cubic',
            'keyword' => 'Image polyvalente',
            'description' => 'Interpolation bicubique polyvalente. Bon choix general pour les photos, illustrations et redimensionnements moderes.',
            'imagick' => 'FILTER_CUBIC',
        ],
        'robidoux' => [
            'label' => 'Robidoux',
            'keyword' => 'Image naturelle sans halos',
            'description' => 'Filtre equilibre d ImageMagick, concu pour limiter halos et artefacts. Utile comme alternative naturelle a Mitchell.',
            'imagick' => 'FILTER_ROBIDOUX',
        ],
        'robidouxsharp' => [
            'label' => 'RobidouxSharp',
            'keyword' => 'Visuel graphique aux contours francs',
            'description' => 'Version plus nette de Robidoux. Recommandee pour les visuels graphiques et les images qui doivent conserver des contours francs.',
            'imagick' => 'FILTER_ROBIDOUXSHARP',
        ],
        'triangle' => [
            'label' => 'Triangle',
            'keyword' => 'Image simple et peu detaillee',
            'description' => 'Reechantillonnage simple et leger, avec un rendu plus doux. Utile pour une conversion rapide ou une image peu detaillee.',
            'imagick' => 'FILTER_TRIANGLE',
        ],
        'point' => [
            'label' => 'Point',
            'keyword' => 'Pixel art ou tres petite image',
            'description' => 'Conserve des pixels francs sans lissage. A utiliser pour le pixel art, les petites icones ou les images volontairement crantees.',
            'imagick' => 'FILTER_POINT',
        ],
    ];

    if (!class_exists('Imagick')) {
        return $filters;
    }

    return array_filter(
        $filters,
        static fn(array $config): bool => defined('Imagick::' . $config['imagick'])
    );
}

function admin_webp_error(string $message, int $status = 422): never
{
    http_response_code($status);

    if (str_contains((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => $message], JSON_UNESCAPED_SLASHES);
        exit;
    }

    throw new RuntimeException($message);
}

function admin_webp_imagick_filter(string $filter): int
{
    if (!class_exists('Imagick')) {
        return 0;
    }

    $filters = admin_webp_filters();
    $constant = 'Imagick::' . ($filters[$filter]['imagick'] ?? 'FILTER_LANCZOS');

    return defined($constant) ? (int) constant($constant) : Imagick::FILTER_LANCZOS;
}

function admin_webp_gd_filter(string $filter): int
{
    return match ($filter) {
        'point' => IMG_NEAREST_NEIGHBOUR,
        'box', 'triangle' => IMG_BILINEAR_FIXED,
        'catrom', 'robidouxsharp' => IMG_BICUBIC_FIXED,
        default => IMG_BICUBIC,
    };
}

function admin_webp_convert(
    string $source,
    string $target,
    int $quality,
    string $filter,
    float $sharpenRadius,
    float $sharpenSigma
): bool
{
    if (class_exists('Imagick')) {
        try {
            $image = new Imagick($source);
            $image->setIteratorIndex(0);
            $image->autoOrient();
            $image->setImageColorspace(Imagick::COLORSPACE_SRGB);
            $image->resizeImage(
                $image->getImageWidth(),
                $image->getImageHeight(),
                admin_webp_imagick_filter($filter),
                1
            );
            if ($sharpenSigma > 0) {
                $image->sharpenImage($sharpenRadius, $sharpenSigma);
            }
            $image->setImageFormat('webp');
            $image->stripImage();
            $image->setImageCompressionQuality($quality);
            $success = $image->writeImage($target);
            $image->clear();
            $image->destroy();

            return $success;
        } catch (Throwable) {
            return false;
        }
    }

    if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
        return false;
    }

    $contents = file_get_contents($source);
    $image = is_string($contents) ? imagecreatefromstring($contents) : false;
    if (!$image instanceof GdImage) {
        return false;
    }

    $filtered = imagescale($image, imagesx($image), imagesy($image), admin_webp_gd_filter($filter));
    if ($filtered instanceof GdImage) {
        $image = $filtered;
    }

    if ($sharpenSigma > 0 && function_exists('imageconvolution')) {
        $strength = min(4, $sharpenSigma);
        $edge = -$strength;
        $center = 1 + (4 * $strength);
        imageconvolution($image, [
            [0, $edge, 0],
            [$edge, $center, $edge],
            [0, $edge, 0],
        ], 1, 0);
    }

    imagealphablending($image, true);
    imagesavealpha($image, true);
    $success = imagewebp($image, $target, $quality);

    return $success;
}

$error = '';
$quality = max(1, min(100, (int) ($_POST['quality'] ?? 85)));
$sharpenRadius = max(0, min(10, (float) ($_POST['sharpen_radius'] ?? 0)));
$sharpenSigma = max(0, min(5, (float) ($_POST['sharpen_sigma'] ?? 0)));
$filter = (string) ($_POST['filter'] ?? 'lanczos');
$filters = admin_webp_filters();
$filter = isset($filters[$filter]) ? $filter : 'lanczos';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    try {
        $upload = is_array($_FILES['image'] ?? null) ? $_FILES['image'] : [];
        $uploadError = (int) ($upload['error'] ?? UPLOAD_ERR_NO_FILE);
        $source = is_string($upload['tmp_name'] ?? null) ? $upload['tmp_name'] : '';
        $originalName = is_string($upload['name'] ?? null) ? $upload['name'] : 'image';
        $size = (int) ($upload['size'] ?? 0);

        if ($uploadError !== UPLOAD_ERR_OK || $source === '' || !is_uploaded_file($source)) {
            admin_webp_error('Selectionne une image valide.');
        }

        if ($size <= 0 || $size > 25 * 1024 * 1024) {
            admin_webp_error('L image doit peser moins de 25 Mo.');
        }

        $imageInfo = @getimagesize($source);
        $mime = is_array($imageInfo) ? (string) ($imageInfo['mime'] ?? '') : '';
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            admin_webp_error('Formats acceptes : JPEG, PNG et WebP.');
        }

        $target = tempnam(sys_get_temp_dir(), 'admin-webp-');
        if (!is_string($target)) {
            admin_webp_error('Impossible de creer le fichier temporaire.', 500);
        }

        if (!admin_webp_convert($source, $target, $quality, $filter, $sharpenRadius, $sharpenSigma)) {
            @unlink($target);
            admin_webp_error('La conversion WebP a echoue.', 500);
        }

        $filename = pathinfo($originalName, PATHINFO_FILENAME);
        $filename = preg_replace('/[^A-Za-z0-9_-]+/', '-', $filename) ?: 'image';
        $filename = trim($filename, '-') . '.webp';
        $length = filesize($target);
        $isPreview = (string) ($_POST['mode'] ?? '') === 'preview';

        header('Content-Type: image/webp');
        header('Content-Disposition: ' . ($isPreview ? 'inline' : 'attachment') . '; filename="' . $filename . '"');
        header('X-WebP-Quality: ' . $quality);
        header('X-WebP-Filter: ' . $filter);
        header('X-WebP-Sharpen-Radius: ' . $sharpenRadius);
        header('X-WebP-Sharpen-Sigma: ' . $sharpenSigma);
        if ($length !== false) {
            header('Content-Length: ' . $length);
        }
        header('Cache-Control: no-store');
        readfile($target);
        @unlink($target);
        exit;
    } catch (RuntimeException $exception) {
        $error = $exception->getMessage();
    }
}

$engine = class_exists('Imagick')
    ? 'Imagick'
    : (function_exists('imagewebp') ? 'GD' : 'Indisponible');

ob_start();
?>
<?php if ($error !== '') : ?>
    <div class="admin-notice is-error">
        <p><?= admin_escape($error) ?></p>
    </div>
<?php endif; ?>

<section class="admin-panel admin-webp-tool">
    <div class="admin-panel-heading">
        <div>
            <h2>Convertir une image</h2>
        </div>
    </div>

    <form class="admin-form" method="post" enctype="multipart/form-data" data-webp-form>
        <input
            class="admin-file-input-hidden"
            type="file"
            name="image"
            accept="image/jpeg,image/png,image/webp"
            required
            tabindex="-1"
            data-webp-input
        >

        <div class="admin-webp-layout">
            <div class="admin-webp-media">
                <div
                    class="admin-webp-preview"
                    role="button"
                    tabindex="0"
                    aria-label="Selectionner ou deposer une image"
                    data-webp-preview
                >
                    <img alt="" aria-hidden="true" data-webp-preview-image hidden>
                    <span class="admin-webp-preview-status" data-webp-preview-status hidden>Conversion...</span>
                </div>

                <ul class="admin-webp-file-meta" role="list" data-webp-file-meta hidden>
                    <li><span>Fichier</span><strong data-webp-meta-name></strong></li>
                    <li><span>Source</span><strong data-webp-meta-source></strong></li>
                    <li><span>WebP</span><strong data-webp-meta-webp>En attente</strong></li>
                    <li><span>Gain</span><strong data-webp-meta-gain>En attente</strong></li>
                </ul>
            </div>

            <div class="admin-webp-controls">
                <div class="admin-webp-settings">
                    <label class="admin-label">
                        Filtre
                        <select class="admin-select" name="filter" data-webp-filter>
                            <?php foreach ($filters as $value => $filterConfig) : ?>
                                <option
                                    value="<?= admin_escape($value) ?>"
                                    data-keyword="<?= admin_escape($filterConfig['keyword']) ?>"
                                    data-description="<?= admin_escape($filterConfig['description']) ?>"
                                    <?= $filter === $value ? 'selected' : '' ?>
                                >
                                    <?= admin_escape($filterConfig['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <span class="admin-field-help" data-webp-filter-description>
                            <strong><?= admin_escape($filters[$filter]['keyword']) ?></strong>
                            <span><?= admin_escape($filters[$filter]['description']) ?></span>
                        </span>
                    </label>

                    <label class="admin-label">
                        Qualite
                        <span class="admin-range-value"><output data-webp-quality-output><?= $quality ?></output>/100</span>
                        <input
                            class="admin-input admin-range"
                            type="range"
                            name="quality"
                            min="1"
                            max="100"
                            value="<?= $quality ?>"
                            data-webp-quality
                        >
                    </label>

                    <label class="admin-label">
                        Nettete radius
                        <span class="admin-range-value"><output data-webp-sharpen-radius-output><?= $sharpenRadius ?></output></span>
                        <input
                            class="admin-input admin-range"
                            type="range"
                            name="sharpen_radius"
                            min="0"
                            max="10"
                            step="0.1"
                            value="<?= $sharpenRadius ?>"
                            data-webp-sharpen-radius
                        >
                    </label>

                    <label class="admin-label">
                        Nettete sigma
                        <span class="admin-range-value"><output data-webp-sharpen-sigma-output><?= $sharpenSigma ?></output></span>
                        <input
                            class="admin-input admin-range"
                            type="range"
                            name="sharpen_sigma"
                            min="0"
                            max="5"
                            step="0.1"
                            value="<?= $sharpenSigma ?>"
                            data-webp-sharpen-sigma
                        >
                    </label>
                </div>

                <div class="admin-actions">
                    <button
                        class="admin-button"
                        type="submit"
                        <?= $engine === 'Indisponible' ? 'disabled aria-disabled="true"' : '' ?>
                    >
                        Telecharger en WebP
                    </button>
                </div>
            </div>
        </div>
    </form>
</section>
<?php

return [
    'title' => 'Admin - WebP',
    'heading' => 'WebP',
    'intro' => 'Convertir et telecharger ponctuellement une image au format WebP.',
    'content' => (string) ob_get_clean(),
];
