<?php

declare(strict_types=1);

$spriteFile = APP_ROOT . '/assets/img/icons.svg';
$backupDirectory = APP_ROOT . '/admin/data/icon-backups';
$notice = null;
$error = null;

function admin_icons_load_document(string $file): DOMDocument
{
    if (!class_exists(DOMDocument::class)) {
        throw new RuntimeException('L extension PHP DOM est necessaire pour gerer le sprite.');
    }

    if (!is_file($file) || !is_readable($file)) {
        throw new RuntimeException('Le fichier assets/img/icons.svg est introuvable ou illisible.');
    }

    $previous = libxml_use_internal_errors(true);
    $document = new DOMDocument('1.0', 'UTF-8');
    $loaded = $document->load($file, LIBXML_NONET | LIBXML_NOBLANKS);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);

    if (!$loaded || !$document->documentElement || strtolower($document->documentElement->localName) !== 'svg') {
        throw new RuntimeException('Le fichier icons.svg n est pas un SVG valide.');
    }

    return $document;
}

function admin_icons_find_symbol(DOMDocument $document, string $id): ?DOMElement
{
    foreach ($document->getElementsByTagName('symbol') as $symbol) {
        if ($symbol instanceof DOMElement && $symbol->getAttribute('id') === $id) {
            return $symbol;
        }
    }

    return null;
}

function admin_icons_validate_id(string $id): string
{
    $id = trim($id);

    if (!preg_match('/^[A-Za-z][A-Za-z0-9_-]*$/', $id)) {
        throw new RuntimeException('L identifiant doit commencer par une lettre et contenir uniquement lettres, chiffres, tirets ou underscores.');
    }

    return $id;
}

function admin_icons_backup(string $spriteFile, string $backupDirectory): void
{
    if (!is_dir($backupDirectory) && !mkdir($backupDirectory, 0775, true) && !is_dir($backupDirectory)) {
        throw new RuntimeException('Impossible de creer le dossier de sauvegarde.');
    }

    $hash = hash_file('sha256', $spriteFile) ?: uniqid('', true);
    $backup = sprintf('%s/icons-%s-%s.svg', $backupDirectory, date('Ymd-His'), substr($hash, 0, 8));

    if (!copy($spriteFile, $backup)) {
        throw new RuntimeException('Impossible de sauvegarder icons.svg avant modification.');
    }
}

function admin_icons_save(DOMDocument $document, string $spriteFile): void
{
    $document->formatOutput = true;
    $markup = $document->saveXML($document->documentElement);

    if ($markup === false || file_put_contents($spriteFile, $markup . PHP_EOL, LOCK_EX) === false) {
        throw new RuntimeException('Impossible d enregistrer icons.svg.');
    }
}

function admin_icons_uploaded_document(array $upload): DOMDocument
{
    if (($upload['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Selectionnez un fichier SVG valide.');
    }

    if (($upload['size'] ?? 0) > 2 * 1024 * 1024) {
        throw new RuntimeException('Le fichier SVG ne doit pas depasser 2 Mo.');
    }

    $temporaryFile = (string) ($upload['tmp_name'] ?? '');
    if ($temporaryFile === '' || !is_uploaded_file($temporaryFile)) {
        throw new RuntimeException('Le fichier envoye n est pas valide.');
    }

    $previous = libxml_use_internal_errors(true);
    $document = new DOMDocument('1.0', 'UTF-8');
    $loaded = $document->load($temporaryFile, LIBXML_NONET | LIBXML_NOBLANKS);
    libxml_clear_errors();
    libxml_use_internal_errors($previous);
    $root = $document->documentElement;

    if (!$loaded || !$root || !in_array(strtolower($root->localName), ['svg', 'symbol'], true)) {
        throw new RuntimeException('Le fichier importe doit contenir un element svg ou symbol.');
    }

    return $document;
}

function admin_icons_view_box(DOMElement $root): string
{
    $viewBox = trim($root->getAttribute('viewBox'));
    if ($viewBox !== '') {
        return $viewBox;
    }

    $width = trim($root->getAttribute('width'));
    $height = trim($root->getAttribute('height'));
    if (preg_match('/^\d+(?:\.\d+)?$/', $width) && preg_match('/^\d+(?:\.\d+)?$/', $height)) {
        return "0 0 {$width} {$height}";
    }

    throw new RuntimeException('Le SVG doit avoir un viewBox, ou une largeur et une hauteur numeriques.');
}

function admin_icons_sanitize(DOMElement $root, string $iconId): void
{
    $forbidden = ['script', 'foreignobject', 'iframe', 'object', 'embed', 'style'];
    $elements = [$root];

    foreach ($root->getElementsByTagName('*') as $element) {
        if ($element instanceof DOMElement) {
            $elements[] = $element;
        }
    }

    foreach (array_reverse($elements) as $element) {
        if ($element !== $root && in_array(strtolower($element->localName), $forbidden, true)) {
            $element->parentNode?->removeChild($element);
        }
    }

    $idMap = [];
    foreach ($elements as $element) {
        if (!$element->parentNode && $element !== $root) {
            continue;
        }

        $oldId = $element->getAttribute('id');
        if ($oldId !== '') {
            $newId = $iconId . '__' . preg_replace('/[^A-Za-z0-9_-]+/', '-', $oldId);
            $idMap[$oldId] = $newId;
            $element->setAttribute('id', $newId);
        }
    }

    foreach ($elements as $element) {
        if (!$element->parentNode && $element !== $root) {
            continue;
        }

        $attributes = [];
        foreach ($element->attributes as $attribute) {
            $attributes[] = $attribute;
        }

        foreach ($attributes as $attribute) {
            $name = strtolower($attribute->name);
            $value = $attribute->value;

            if (str_starts_with($name, 'on') || $name === 'style') {
                $element->removeAttributeNode($attribute);
                continue;
            }

            if (in_array($name, ['href', 'xlink:href'], true) && $value !== '' && !str_starts_with($value, '#')) {
                $element->removeAttributeNode($attribute);
                continue;
            }

            if (preg_match_all('/url\(\s*([^)]+)\)/i', $value, $matches)) {
                foreach ($matches[1] as $reference) {
                    if (!str_starts_with(trim($reference, " \t\n\r\0\x0B'\""), '#')) {
                        $element->removeAttributeNode($attribute);
                        continue 2;
                    }
                }
            }

            foreach ($idMap as $oldId => $newId) {
                $value = str_replace('url(#' . $oldId . ')', 'url(#' . $newId . ')', $value);
                if ($value === '#' . $oldId) {
                    $value = '#' . $newId;
                }
            }

            $attribute->value = $value;
        }
    }
}

function admin_icons_add(DOMDocument $sprite, DOMDocument $uploaded, string $id): void
{
    if (admin_icons_find_symbol($sprite, $id)) {
        throw new RuntimeException("L icone {$id} existe deja.");
    }

    $source = $uploaded->documentElement;
    if (!$source) {
        throw new RuntimeException('Le SVG importe est vide.');
    }

    $viewBox = admin_icons_view_box($source);
    admin_icons_sanitize($source, $id);

    $namespace = $sprite->documentElement?->namespaceURI ?: 'http://www.w3.org/2000/svg';
    $symbol = $sprite->createElementNS($namespace, 'symbol');
    $symbol->setAttribute('id', $id);
    $symbol->setAttribute('viewBox', $viewBox);

    foreach (['fill', 'stroke', 'stroke-width', 'stroke-linecap', 'stroke-linejoin', 'fill-rule', 'clip-rule'] as $attribute) {
        if ($source->hasAttribute($attribute)) {
            $symbol->setAttribute($attribute, $source->getAttribute($attribute));
        }
    }

    foreach (iterator_to_array($source->childNodes) as $child) {
        $symbol->appendChild($sprite->importNode($child, true));
    }

    $sprite->documentElement?->appendChild($symbol);
}

try {
    $spriteDocument = admin_icons_load_document($spriteFile);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $action = (string) ($_POST['action'] ?? '');

        if ($action === 'add') {
            $iconId = admin_icons_validate_id((string) ($_POST['icon_id'] ?? ''));
            $uploadedDocument = admin_icons_uploaded_document($_FILES['icon_file'] ?? []);
            admin_icons_add($spriteDocument, $uploadedDocument, $iconId);
            admin_icons_backup($spriteFile, $backupDirectory);
            admin_icons_save($spriteDocument, $spriteFile);
            $notice = "L icone {$iconId} a ete ajoutee.";
        } elseif ($action === 'delete') {
            $iconId = admin_icons_validate_id((string) ($_POST['icon_id'] ?? ''));
            $symbol = admin_icons_find_symbol($spriteDocument, $iconId);

            if (!$symbol) {
                throw new RuntimeException("L icone {$iconId} est introuvable.");
            }

            admin_icons_backup($spriteFile, $backupDirectory);
            $symbol->parentNode?->removeChild($symbol);
            admin_icons_save($spriteDocument, $spriteFile);
            $notice = "L icone {$iconId} a ete supprimee.";
        }
    }
} catch (Throwable $exception) {
    $error = $exception->getMessage();
}

$symbols = [];
$symbolGroups = [];
$singleSymbols = [];
$spriteDefinitions = '';

if (isset($spriteDocument)) {
    foreach ($spriteDocument->getElementsByTagName('symbol') as $symbol) {
        if (!$symbol instanceof DOMElement || $symbol->getAttribute('id') === '') {
            continue;
        }

        $symbols[] = [
            'id' => $symbol->getAttribute('id'),
            'viewBox' => $symbol->getAttribute('viewBox'),
            'size' => (static function (string $viewBox): array {
                $values = preg_split('/[\s,]+/', trim($viewBox), -1, PREG_SPLIT_NO_EMPTY);

                if (count($values) !== 4 || !is_numeric($values[2]) || !is_numeric($values[3])) {
                    return [
                        'label' => 'Taille inconnue',
                        'width' => PHP_FLOAT_MAX,
                        'height' => PHP_FLOAT_MAX,
                    ];
                }

                return [
                    'label' => sprintf('%sx%spx', $values[2], $values[3]),
                    'width' => (float) $values[2],
                    'height' => (float) $values[3],
                ];
            })($symbol->getAttribute('viewBox')),
        ];
        $spriteDefinitions .= $spriteDocument->saveXML($symbol);
    }

    foreach ($symbols as $icon) {
        $groupKey = $icon['size']['label'];

        if (!isset($symbolGroups[$groupKey])) {
            $symbolGroups[$groupKey] = [
                'label' => $icon['size']['label'],
                'width' => $icon['size']['width'],
                'height' => $icon['size']['height'],
                'icons' => [],
            ];
        }

        $symbolGroups[$groupKey]['icons'][] = $icon;
    }

    $symbolGroups = array_values($symbolGroups);
    usort($symbolGroups, static function (array $a, array $b): int {
        $widthComparison = $a['width'] <=> $b['width'];

        return $widthComparison !== 0
            ? $widthComparison
            : ($a['height'] <=> $b['height']);
    });

    foreach ($symbolGroups as &$group) {
        usort($group['icons'], static fn(array $a, array $b): int => strcasecmp($a['id'], $b['id']));
    }
    unset($group);

    foreach ($symbolGroups as $key => $group) {
        if (count($group['icons']) === 1) {
            $singleSymbols[] = $group['icons'][0];
            unset($symbolGroups[$key]);
        }
    }

    $symbolGroups = array_values($symbolGroups);
    usort($singleSymbols, static function (array $a, array $b): int {
        $widthComparison = $a['size']['width'] <=> $b['size']['width'];
        if ($widthComparison !== 0) {
            return $widthComparison;
        }

        $heightComparison = $a['size']['height'] <=> $b['size']['height'];

        return $heightComparison !== 0
            ? $heightComparison
            : strcasecmp($a['id'], $b['id']);
    });
}

ob_start();
?>

<section class="admin-icons">
    <header class="admin-icons-header">
        <p>Gerez les symboles de <code>assets/img/icons.svg</code>.</p>
        <span class="admin-icons-count"><?= count($symbols) ?> icone<?= count($symbols) > 1 ? 's' : '' ?></span>
    </header>

    <?php if ($notice): ?>
        <div class="admin-notice"><?= htmlspecialchars($notice) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="admin-notice is-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <svg class="admin-icons-definitions" aria-hidden="true">
        <?= $spriteDefinitions ?>
    </svg>

    <div class="admin-icons-layout">
        <div class="admin-icons-library">
            <div class="admin-icons-toolbar">
                <label class="admin-icons-search">
                    <span>Rechercher une icone</span>
                    <input type="search" placeholder="Ex. arrow, logo, chat..." data-icons-search>
                </label>
            </div>

            <div class="admin-icon-groups" data-icons-grid>
                <?php if ($singleSymbols !== []): ?>
                    <section class="admin-icon-group admin-icon-group-singles" data-icon-group>
                        <div class="admin-icons-grid">
                            <?php foreach ($singleSymbols as $icon): ?>
                                <article class="admin-icon-card" data-icon-card data-icon-id="<?= htmlspecialchars(strtolower($icon['id'])) ?>">
                                    <div class="admin-icon-preview">
                                        <svg aria-hidden="true" focusable="false">
                                            <use href="#<?= htmlspecialchars($icon['id']) ?>"></use>
                                        </svg>
                                    </div>
                                    <div class="admin-icon-meta">
                                        <code><?= htmlspecialchars($icon['id']) ?></code>
                                        <small>Size : <?= htmlspecialchars($icon['size']['label']) ?></small>
                                    </div>
                                    <div class="admin-icon-actions">
                                        <button type="button" class="admin-button is-secondary" data-copy-icon="<?= htmlspecialchars($icon['id']) ?>">Copier</button>
                                        <form method="post" data-icon-delete-form data-icon-name="<?= htmlspecialchars($icon['id']) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="icon_id" value="<?= htmlspecialchars($icon['id']) ?>">
                                            <button type="submit" class="admin-button is-danger">Supprimer</button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endif; ?>

                <?php foreach ($symbolGroups as $group): ?>
                    <section class="admin-icon-group" data-icon-group>
                        <h2 class="admin-icon-group-title">
                            Size : <?= htmlspecialchars($group['label']) ?>
                            <small><?= count($group['icons']) ?> icone<?= count($group['icons']) > 1 ? 's' : '' ?></small>
                        </h2>

                        <div class="admin-icons-grid">
                            <?php foreach ($group['icons'] as $icon): ?>
                                <article class="admin-icon-card" data-icon-card data-icon-id="<?= htmlspecialchars(strtolower($icon['id'])) ?>">
                                    <div class="admin-icon-preview">
                                        <svg aria-hidden="true" focusable="false">
                                            <use href="#<?= htmlspecialchars($icon['id']) ?>"></use>
                                        </svg>
                                    </div>
                                    <div class="admin-icon-meta">
                                        <code><?= htmlspecialchars($icon['id']) ?></code>
                                    </div>
                                    <div class="admin-icon-actions">
                                        <button type="button" class="admin-button is-secondary" data-copy-icon="<?= htmlspecialchars($icon['id']) ?>">Copier</button>
                                        <form method="post" data-icon-delete-form data-icon-name="<?= htmlspecialchars($icon['id']) ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="icon_id" value="<?= htmlspecialchars($icon['id']) ?>">
                                            <button type="submit" class="admin-button is-danger">Supprimer</button>
                                        </form>
                                    </div>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </section>
                <?php endforeach; ?>
            </div>

            <p class="admin-icons-empty" data-icons-empty hidden>Aucune icone ne correspond a cette recherche.</p>
        </div>

        <aside class="admin-icons-add">
            <p class="admin-eyebrow">Nouveau symbole</p>
            <h2>Ajouter une icone</h2>
            <p>Importez un SVG avec un <code>viewBox</code>. Les scripts sont retires et les identifiants internes sont prefixes automatiquement.</p>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add">

                <label>
                    <span>Identifiant</span>
                    <input type="text" name="icon_id" required pattern="[A-Za-z][A-Za-z0-9_-]*" placeholder="arrow-right">
                </label>

                <label>
                    <span>Fichier SVG</span>
                    <input type="file" name="icon_file" accept=".svg,image/svg+xml" required>
                </label>

                <button type="submit" class="admin-button">Ajouter au sprite</button>
            </form>

            <p class="admin-icons-backup">Une sauvegarde est creee dans <code>admin/data/icon-backups/</code> avant chaque modification.</p>
        </aside>
    </div>
</section>
<?php

return [
    'title' => 'Admin - Icons',
    'heading' => 'Icons',
    'intro' => 'Visualiser, ajouter et supprimer les symboles du sprite SVG.',
    'content' => (string) ob_get_clean(),
];
