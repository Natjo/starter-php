<?php
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = isset($_SERVER['HTTP_HOST']) && is_scalar($_SERVER['HTTP_HOST']) ? (string) $_SERVER['HTTP_HOST'] : '';
$base_url = $host !== '' ? $scheme . '://' . $host : '';

$items = [
    [
        'name' => 'Accueil',
        'url' => '/',
    ],
    [
        'name' => 'Page courante',
        'url' => null,
    ],
];

$schema_items = [];
foreach ($items as $index => $item) {
    $url = !empty($item['url']) && is_string($item['url'])
        ? $base_url . $item['url']
        : ($base_url . ($_SERVER['REQUEST_URI'] ?? '/'));

    $schema_items[] = [
        '@type' => 'ListItem',
        'position' => $index + 1,
        'name' => $item['name'],
        'item' => $url,
    ];
}

$schema = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => $schema_items,
];
?>

<nav class="breadcrumb" aria-label="Fil d'Ariane">
    <ol>
        <?php foreach ($items as $index => $item) :
            $is_current = $index === array_key_last($items);
        ?>
            <li>
                <?php if ($is_current) : ?>
                    <span aria-current="page"><?= esc_html($item['name']) ?></span>
                <?php else : ?>
                    <a href="<?= esc_url((string) $item['url']) ?>"><?= esc_html($item['name']) ?></a>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>

<script type="application/ld+json">
<?= json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>
</script>
