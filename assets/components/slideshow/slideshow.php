<?php
$slides = $params[0] ?? null;
if (!is_array($slides) || count($slides) < 1) return;

$options = is_array($params[1] ?? null) ? $params[1] : [];
$interval = isset($options["interval"]) ? (int) $options["interval"] : 3000;
$duration = isset($options["duration"]) ? (int) $options["duration"] : 700;
$classes = component::classes("slideshow", $options["classes"] ?? "");
$attributes = component::attributes($options["attributes"] ?? []);
?>

<div
    class="<?= $classes ?>"
    data-module="components/slideshow"
    data-context="@visible true"
    data-interval="<?= esc_attr($interval) ?>"
    data-duration="<?= esc_attr($duration) ?>"<?= $attributes ?>>
    <?php foreach ($slides as $index => $image) :
        $picture_args = is_array($image) ? $image : ["images" => $image];
        $picture_args = [...$picture_args, "lazy" => false, "preload" => $index === 0];

        ob_start();
        component::picture($picture_args);
        $picture_html = ob_get_clean();

        // Slides après la 1ère : on diffère le chargement (src/srcset -> data-*)
        // pour ne charger l'image que juste avant sa transition (via le JS).
        if ($index !== 0) {
            $picture_html = str_replace(
                [' srcset="', ' src="'],
                [' data-srcset="', ' data-src="'],
                $picture_html
            );
        }
        ?>
        <div class="slideshow-slide"><?= $picture_html ?></div>
    <?php endforeach; ?>
</div>
