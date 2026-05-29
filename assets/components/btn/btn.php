<?php
$args = component::args($args ?? null);
$name = !empty($args["name"]) ? (string) $args["name"] : "";
$link = !empty($args["link"]) && is_array($args["link"]) ? $args["link"] : null;
$target = !empty($link["target"]) ? ' target="' . htmlspecialchars((string) $link["target"], ENT_QUOTES, "UTF-8") . '"' : '';
$classes = component::classes("btn", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
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
    <a href="<?= $url ?>" class="<?= $classes ?>"<?= $attributes . $target ?>><?= $iconHtml ?><span><?= $title ?></span></a>
<?php elseif ($name !== "") : ?>
    <button class="<?= $classes ?>"<?= $attributes ?>><?= $iconHtml ?><span><?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?></span></button>
<?php endif; ?>
