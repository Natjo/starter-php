<?php
$args = isset($args) && is_array($args) ? $args : [];
$link = !empty($args["link"]) && is_array($args["link"]) ? $args["link"] : [];
$target = !empty($link["target"]) ? ' target="' . htmlspecialchars((string) $link["target"], ENT_QUOTES, "UTF-8") . '"' : '';
$classes = !empty($args["classes"]) ? htmlspecialchars((string) $args["classes"], ENT_QUOTES, "UTF-8") : "";
$iconHtml = "";

if (!empty($args["icon"]) && is_array($args["icon"]) && method_exists("component", "icon")) {
    $icon = $args["icon"];
    $name = (string) $icon[0];
    $width = isset($icon[1]) ? (float) $icon[1] : 20;
    $height = isset($icon[2]) ? (float) $icon[2] : 20;
    if ($width <= 0) $width = 20;
    if ($height <= 0) $height = 20;

    ob_start();
    component::icon($name, $width, $height);
    $iconHtml = (string) ob_get_clean();
}
$attributes = !empty($args["attributes"]) ? " " . (string) $args["attributes"] : "";

$url = htmlspecialchars((string) ($link["url"] ?? "#"), ENT_QUOTES, "UTF-8");
$title = htmlspecialchars((string) ($link["title"] ?? ""), ENT_QUOTES, "UTF-8");
?>

<?php if ($title !== "") : ?>
    <a href="<?= $url ?>" class="link <?= $classes ?>"<?= $attributes . $target ?>><span><?= $title ?></span><?= $iconHtml ?></a>
<?php endif; ?>
