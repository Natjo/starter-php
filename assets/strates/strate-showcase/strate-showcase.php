<?php
$args = normalize_args($args ?? null);
$cols = isset($args["items"]) && is_array($args["items"]) ? array_values($args["items"]) : [];
?>

<section <?= options("strate strate-showcase", $args) ?> data-module="strates/strate-showcase">

    <div class="sticky">
        <div class="strate-header">
            <?php component::eyebrow($args, "hasicon"); ?>
            <?php component::title($args, 2, "title-2"); ?>
            <?php component::text($args); ?>
        </div>
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
                            <?php component::image($item["image"],null,"strate-showcase__img"); ?>
                            
                        <?php endif ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>

</section>