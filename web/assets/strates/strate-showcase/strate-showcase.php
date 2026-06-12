<?php
$args = normalize_args($args ?? null);

// Colonnes d'images : chaque colonne a sa propre vitesse de parallax.
// Les 2 premières colonnes vont à gauche, les 2 dernières à droite,
// laissant le centre libre pour le titre/texte sticky.
$columns = [
    ["speed" => 0.65, "images" => ["showcase-1.jpg", "showcase-5.jpg"]],
    ["speed" => 1.10, "images" => ["showcase-2.jpg", "showcase-6.jpg"]],
    ["speed" => 0.85, "images" => ["showcase-3.jpg", "showcase-7.jpg"]],
    ["speed" => 0.45, "images" => ["showcase-4.jpg", "showcase-8.jpg"]],
];

$render_side = static function (array $cols): void {
    foreach ($cols as $col) : ?>
        <div class="strate-showcase__col" data-speed="<?= esc_attr($col["speed"]) ?>">
            <?php foreach ($col["images"] as $image) : ?>
                <img class="strate-showcase__img" src="<?= esc_url(THEME_UPLOADS . $image) ?>" alt="" loading="lazy" decoding="async">
            <?php endforeach; ?>
        </div>
<?php endforeach;
};
?>

<section <?= options("strate strate-showcase", $args) ?> data-module="strates/strate-showcase" data-context="@visible true">

    <div class="strate-showcase__sticky">
        <div class="strate-content">
            <?php component::title($args, 2, "title-2"); ?>
            <?php component::text($args); ?>
        </div>

        <div class="strate-showcase__grid">
            <div class="strate-showcase__side strate-showcase__side--left">
                <?php $render_side(array_slice($columns, 0, 2)); ?>
            </div>

            <div class="strate-showcase__side strate-showcase__side--right">
                <?php $render_side(array_slice($columns, 2, 2)); ?>
            </div>
        </div>
    </div>

</section>