<?php
$args = isset($args) && is_array($args) ? $args : [];
$text = $args["text"] ?? "";
$classes = !empty($args["classes"]) ? " " . htmlspecialchars((string) $args["classes"], ENT_QUOTES, "UTF-8") : "";
$attributes = !empty($args["attributes"]) ? (string) $args["attributes"] : "";

if ($text === "") return;
?>

<div class="text<?= $classes ?>"<?= $attributes !== "" ? " " . $attributes : "" ?>>
    <?= $text ?>
</div>
