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

    <svg class="bg" xmlns="http://www.w3.org/2000/svg" width="1306" height="960" viewBox="0 0 1306 960" fill="none">
       
            <path d="M934 495.058C902.29 500.425 868.141 514.572 831.552 537.501C794.476 560.43 760.082 593.116 728.372 635.558C696.662 677.513 676.417 719.468 667.635 761.423L636.901 761.423C627.144 719.956 609.094 680.196 582.75 642.144C555.918 603.604 523.72 571.406 486.156 545.55C447.616 519.207 409.564 502.376 372 495.058L372 464.324C396.392 459.933 421.516 451.152 447.372 437.98C472.74 424.808 497.133 407.978 520.549 387.488C543.478 366.511 564.212 343.094 582.75 317.238C610.069 278.698 628.12 239.426 636.901 199.423L667.635 199.423C673.002 226.254 683.978 254.062 700.565 282.845C717.152 311.14 736.91 337.484 759.839 361.876C782.28 386.268 805.94 406.27 830.82 421.881C867.409 444.81 901.802 458.958 934 464.324L934 495.058Z" fill="white" fill-opacity="0.26" style="fill:white;fill-opacity:0.26;" />
        
       
    </svg>
</section>