<?php
$args = normalize_args($args ?? null);
$cols = isset($args["items"]) && is_array($args["items"]) ? array_values($args["items"]) : [];
?>

<section <?= options("strate strate-showcase", $args) ?> data-module="strates/strate-showcase" data-context="@visible true">

    <div class="sticky">
        <div class="strate-header">
            <?php component::eyebrow($args); ?>
            <?php component::title($args, 2, "title-2"); ?>
            <?php component::text($args); ?>
        </div>
        <svg class="bg" xmlns="http://www.w3.org/2000/svg" width="1306" height="960" viewBox="0 0 1306 960" fill="none">
            <path d="M934 495.058C902.29 500.425 868.141 514.572 831.552 537.501C794.476 560.43 760.082 593.116 728.372 635.558C696.662 677.513 676.417 719.468 667.635 761.423L636.901 761.423C627.144 719.956 609.094 680.196 582.75 642.144C555.918 603.604 523.72 571.406 486.156 545.55C447.616 519.207 409.564 502.376 372 495.058L372 464.324C396.392 459.933 421.516 451.152 447.372 437.98C472.74 424.808 497.133 407.978 520.549 387.488C543.478 366.511 564.212 343.094 582.75 317.238C610.069 278.698 628.12 239.426 636.901 199.423L667.635 199.423C673.002 226.254 683.978 254.062 700.565 282.845C717.152 311.14 736.91 337.484 759.839 361.876C782.28 386.268 805.94 406.27 830.82 421.881C867.409 444.81 901.802 458.958 934 464.324L934 495.058Z" fill="white" fill-opacity="0.26" style="fill:white;fill-opacity:0.26;" />
        </svg>
    </div>

    <div class="medias">
        <?php foreach ($cols as $col_index => $column_items) : ?>
            <?php if (!is_array($column_items)) $column_items = []; ?>
            <div class="col col-<?= $col_index ?>">
                <?php foreach ($column_items as $item) : ?>
                    <?php if (!is_array($item)) continue; ?>
                    <div class="item">
                        <?php if (!empty($item["isVideo"]) && !empty($item["video"])) : ?>
                            <video preload="metadata" autoplay muted playsinline loop>
                                <source src="<?= esc_url($item["video"]) ?>" type="video/mp4">
                            </video>
                        <?php elseif (!empty($item["image"])) : ?>
                            <img class="strate-showcase__img" src="<?= esc_url($item["image"]) ?>" alt="" loading="lazy" decoding="async">
                        <?php endif ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

</section>