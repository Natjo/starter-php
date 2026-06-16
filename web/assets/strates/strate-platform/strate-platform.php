<?php
$args = normalize_args($args ?? null);

?>

<section <?= options("strate strate-platform", $args) ?> data-module="strates/strate-platform" data-context="@visible true">
    <div class="sticky">
        <div class="strate-content">
            <div class="strate-header">
                <?php component::eyebrow($args, "hasicon"); ?>
                <?php component::title($args, 2, "title-2"); ?>
            </div>
            <?php component::text($args); ?>

            <ul class="list-platforms">
                <?php foreach ($args["items"] ?? [] as $item) : ?>
                    <li class="item">
                        <?php component::icon($item["icon"], 20, 20); ?> <span><?= $item["title"]; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>

            <?php component::btn($args, "btn-1 cta", ["blank", 16, 16]); ?>
        </div>

        <div class="platforms">
            <h3><span>Quick start</span><?php component::icon("arrow-down", 16, 16); ?></h3> 
            <?php foreach ($args["platforms"] ?? [] as $index => $platform) : ?>
                <div class="platform">
                    <div class="number">
                        <?= $index + 1; ?>
                    </div>

                    <div class="platform-content">
                        <div class="platform-content-inner">
                            <?php component::image($platform["image"]); ?>
                        </div>
                    </div>

                    <div class="label">
                        <?= $platform["title"]; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>