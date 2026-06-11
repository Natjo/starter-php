<?php
$args = normalize_args($args ?? null);
$title = htmlspecialchars((string) ($args["title"] ?? ($link["title"] ?? "")), ENT_QUOTES, "UTF-8");
$text = htmlspecialchars((string) ($args["text"] ?? ""), ENT_QUOTES, "UTF-8");
$hx = $args["hx"] ?? 3;

?>

<div class="card-foundation">
    <?php component::title($args, 3, ".title-3"); ?>
    <?php component::text($args); ?>
</div>