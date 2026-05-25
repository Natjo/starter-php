<?php
$args = isset($args) && is_array($args) ? $args : [];
$title = !empty($args["title"]) ? (string) $args["title"] : "";
$level = !empty($args["hx"]) ? (int) $args["hx"] : (!empty($args["level"]) ? (int) $args["level"] : 2);
$level = max(1, min(6, $level));
$hx = "h" . $level;
$classes = !empty($args["classes"]) ? " " . htmlspecialchars((string) $args["classes"], ENT_QUOTES, "UTF-8") : "";
$attributes = !empty($args["attributes"]) ? (string) $args["attributes"] : "";

if ($title === "") {
    return;
}
?>

<<?= $hx; ?> class="title<?= htmlspecialchars($classes, ENT_QUOTES, 'UTF-8'); ?>" <?= $attributes ?>><?= htmlspecialchars($title, ENT_QUOTES, 'UTF-8'); ?></<?= $hx; ?>>
