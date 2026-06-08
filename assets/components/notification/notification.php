<?php
$args = normalize_args($args ?? null);

$message = $args["message"] ?? $args["text"] ?? "";
$message = is_scalar($message) ? trim((string) $message) : "";

if ($message === "") return;

$title = isset($args["title"]) && is_scalar($args["title"])
    ? trim((string) $args["title"])
    : "";

$allowed_types = ["info", "success", "warning", "error"];
$type = isset($args["type"]) && is_scalar($args["type"])
    ? strtolower(trim((string) $args["type"]))
    : "info";

if (!in_array($type, $allowed_types, true)) {
    $type = "info";
}

$duration = isset($args["duration"]) && is_numeric($args["duration"])
    ? max(0, (int) $args["duration"])
    : 5000;
$dismissible = !array_key_exists("dismissible", $args) || (bool) $args["dismissible"];
$role = $type === "error" ? "alert" : "status";
$live = $type === "error" ? "assertive" : "polite";
$classes = component::classes("notification", "notification-" . $type, $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
?>

<aside
    class="<?= $classes ?>"
    role="<?= esc_attr($role) ?>"
    aria-live="<?= esc_attr($live) ?>"
    aria-atomic="true"
    data-module="components/notification"
    data-duration="<?= esc_attr((string) $duration) ?>"<?= $attributes ?>>
    <span class="notification-indicator" aria-hidden="true"></span>

    <div class="notification-content">
        <?php if ($title !== "") : ?>
            <strong class="notification-title"><?= esc_html($title) ?></strong>
        <?php endif; ?>

        <p class="notification-message"><?= esc_html($message) ?></p>
    </div>

    <?php if ($dismissible) : ?>
        <button class="notification-close" type="button" aria-label="Fermer la notification">
            <span aria-hidden="true">&times;</span>
        </button>
    <?php endif; ?>
</aside>
