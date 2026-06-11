<?php
$args = normalize_args($args ?? null);

?>

<section <?= options("strate strate-platform", $args) ?> data-module="strates/strate-platform" data-context="@visible true">
    <div class="sticky">
        <div class="strate-content">
            <?php component::eyebrow($args); ?>

            <?php component::title($args, 2, "title-2"); ?>

            <?php component::text($args); ?>

            <?php component::btn($args, "btn-1"); ?>

            <ul>

                <?php foreach ($args["items"] ?? [] as $item) : ?>
                    <li class="item">
                        <?php component::icon($item["icon"], 20, 20); ?> <span><?= $item["title"]; ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="platforms">
            <?php foreach ($args["platforms"] ?? [] as $index => $platform) : ?>
                <div class="platform">
                    <div class="number">
                        <?= $index + 1; ?>
                    </div>

                    <?php component::image($platform["image"]); ?>

                    <div class="label">
                        <?= $platform["title"]; ?>
                    </div>

                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>