<?php
$args = isset($args) && is_array($args) ? $args : [];
$name = !empty($args["name"]) ? (string) $args["name"] : "";
$link = !empty($args["link"]) && is_array($args["link"]) ? $args["link"] : null;
$target = !empty($link["target"]) ? ' target="' . htmlspecialchars((string) $link["target"], ENT_QUOTES, "UTF-8") . '"' : '';
$classes = !empty($args["classes"]) ? " " . htmlspecialchars((string) $args["classes"], ENT_QUOTES, "UTF-8") : "";
$attributes = !empty($args["attributes"]) ? " " . (string) $args["attributes"] : "";
$icon = !empty($args["icon"]) && is_array($args["icon"]) ? $args["icon"] : null;
$iconHtml = "";

if ($icon && method_exists("component", "icon")) {
    ob_start();
    component::icon(...$icon);
    $iconHtml = ob_get_clean();
}
?>

<?php if ($link) :
    $url = htmlspecialchars((string) ($link["url"] ?? "#"), ENT_QUOTES, "UTF-8");
    $title = htmlspecialchars((string) ($link["title"] ?? ""), ENT_QUOTES, "UTF-8");
?>
    <a href="<?= $url ?>" class="btn<?= $classes ?>"<?= $attributes . $target ?>><?= $iconHtml ?><span><?= $title ?></span></a>
<?php elseif ($name !== "") : ?>
    <button class="btn<?= $classes ?>"<?= $attributes ?>><?= $iconHtml ?><span><?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?></span></button>
<?php endif; ?>
