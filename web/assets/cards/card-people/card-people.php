<?php
$args = normalize_args($args ?? null);
$name = (string) ($args["name"] ?? "");
$function = (string) ($args["function"] ?? "");
$from = (string) ($args["from"] ?? "");
$shares = $args["shares"] ?? [];
$shares = is_array($shares) ? $shares : [];
?>

<article class="card-people">

    <?php if ($name !== "") : ?>
        <div class="name"><?= esc_html($name) ?></div>
    <?php endif; ?>

    <div class="content">
        <?php if ($function !== "") : ?>
            <div class="function"><?= esc_html($function) ?></div>
        <?php endif; ?>

        <?php if ($from !== "") : ?>
            <div class="from"><?= esc_html($from) ?></div>
        <?php endif; ?>

        <?php if (!empty($shares)) : ?>
            <?php component::shares("", $shares); ?>
        <?php endif; ?>
    </div>

</article>
