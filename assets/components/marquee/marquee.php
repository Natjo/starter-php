<?php
$args = normalize_args($args ?? null);
$items = isset($args["items"]) && is_array($args["items"])
    ? $args["items"]
    : (isset($args["args"]) && is_array($args["args"]) ? $args["args"] : []);

if (empty($items)) return;

$direction = isset($args["direction"]) && $args["direction"] === "right" ? "right" : "left";
$speed = isset($args["speed"]) && is_numeric($args["speed"]) ? max(1, (float) $args["speed"]) : 50;
$resume_delay = isset($args["resume_delay"]) && is_numeric($args["resume_delay"])
    ? max(0, (int) $args["resume_delay"])
    : 120;
$card = isset($args["card"]) && is_scalar($args["card"]) ? trim((string) $args["card"]) : "";
$aria_label = isset($args["aria_label"]) && is_scalar($args["aria_label"])
    ? trim((string) $args["aria_label"])
    : "Contenu défilant";
$classes = component::classes("marquee", "marquee-" . $direction, $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);
?>

<div
    class="<?= $classes ?>"
    role="region"
    aria-label="<?= esc_attr($aria_label) ?>"
    data-module="components/marquee"
    data-context="@visible true"
    data-direction="<?= esc_attr($direction) ?>"
    data-speed="<?= esc_attr((string) $speed) ?>"
    data-resume-delay="<?= esc_attr((string) $resume_delay) ?>"<?= $attributes ?>>
    <div class="marquee-viewport" tabindex="0" data-marquee-viewport>
        <div class="marquee-track" data-marquee-track>
            <ul class="marquee-group" role="list" data-marquee-group>
                <?php foreach ($items as $item) :
                    $item_data = is_array($item) ? $item : ["text" => $item];
                    $content = $item_data["content"] ?? $item_data["text"] ?? $item_data["title"] ?? "";
                    $content = is_scalar($content) ? trim((string) $content) : "";
                    $url = isset($item_data["url"]) && is_scalar($item_data["url"])
                        ? trim((string) $item_data["url"])
                        : "";
                    $target = isset($item_data["target"]) && is_scalar($item_data["target"])
                        ? trim((string) $item_data["target"])
                        : "";

                    if ($card === "" && $content === "") continue;
                ?>
                    <li class="marquee-item">
                        <?php if ($card !== "") : ?>
                            <?php card($card, $item_data); ?>
                        <?php elseif ($url !== "") : ?>
                            <a
                                href="<?= esc_url($url) ?>"
                                <?= $target !== "" ? 'target="' . esc_attr($target) . '"' : "" ?>
                                <?= $target === "_blank" ? 'rel="noopener noreferrer"' : "" ?>>
                                <?= wp_kses_post($content) ?>
                            </a>
                        <?php else : ?>
                            <span><?= wp_kses_post($content) ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>
