<?php

/** @var array $args */
$video_url = $params[0] ?? null;
if (empty($video_url)) return;
$args = normalize_args([
    "url" => $video_url,
    "title" => $params[1] ?? "Lecteur vidéo",
    "poster" => $params[2] ?? null,
    "play_label" => $params[7] ?? "Lire la vidéo",
    "autoplay" => $params[3] ?? false,
    "loop" => $params[4] ?? false,
    "classes" => $params[5] ?? null,
    "attributes" => $params[6] ?? null,
]);
$text = static fn(mixed $value): string => is_scalar($value) ? trim((string) $value) : "";

// $args["url"] accepte :
// - une string (URL ou ID média WordPress) → un seul <source>
// - un tableau d'URLs / IDs → plusieurs <source> avec MIME auto-détecté
//   (utile pour servir un .webm + .mp4 en fallback).
$url_input = $args["url"] ?? "";
$title = $text($args["title"] ?? "");
$poster = $text($args["poster"] ?? "");
$autoplay = !empty($args["autoplay"]);
$loop = !empty($args["loop"]);
$classes = component::classes("video", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);

$is_safe_media_url = function (mixed $url) use ($text): bool {
    $url = $text($url);
    if ($url === "" || str_contains($url, "\0")) return false;

    if (str_starts_with($url, "/")) {
        return !preg_match('#(^|/)\.\.(/|$)#', $url);
    }

    $scheme = parse_url($url, PHP_URL_SCHEME);
    return in_array(strtolower((string) $scheme), ["http", "https"], true);
};

$urls = is_array($url_input) ? $url_input : [$url_input];
$urls = array_values(array_filter(array_map(function (mixed $u) use ($is_safe_media_url, $text): string {
    $u = $text($u);
    if ($u === "") return "";
    if (is_numeric($u)) {
        if (!function_exists('wp_get_attachment_url')) return "";
        $resolved = wp_get_attachment_url((int) $u);
        if ($resolved) return $resolved;
    }
    return $is_safe_media_url($u) ? $u : "";
}, $urls)));

if (empty($urls)) return;
$url = $urls[0];

if ($poster !== "" && is_numeric($poster)) {
    $resolved_poster = function_exists('wp_get_attachment_url') ? wp_get_attachment_url((int) $poster) : "";
    $poster = $resolved_poster ? $resolved_poster : "";
}
$poster = $poster !== "" && $is_safe_media_url($poster) ? $poster : "";

$mime_map = [
    "mp4"  => "video/mp4",
    "m4v"  => "video/mp4",
    "webm" => "video/webm",
    "ogv"  => "video/ogg",
    "ogg"  => "video/ogg",
    "mov"  => "video/quicktime",
];
$detect_mime = function ($u) use ($mime_map) {
    $path = parse_url($u, PHP_URL_PATH);
    $ext = strtolower((string) pathinfo((string) $path, PATHINFO_EXTENSION));
    return $mime_map[$ext] ?? "video/mp4";
};

$is_youtube = (bool) preg_match('#(?:youtube\.com|youtu\.be)#i', $url);
$is_vimeo = (bool) preg_match('#vimeo\.com#i', $url);
$type = $is_youtube ? "youtube" : ($is_vimeo ? "vimeo" : "video");

// URL d'embed (idle vs autoplay) pour YouTube / Vimeo.
$idle_src = "";
$autoplay_src = "";
if ($is_youtube) {
    $vid = youtube_id_from_url($url);
    if ($vid === "") return;
    $base = ['rel' => 0];
    if ($loop) { $base['loop'] = 1; $base['playlist'] = $vid; }
    $idle_src = 'https://www.youtube.com/embed/' . rawurlencode($vid) . '?' . http_build_query($base);
    $autoplay_src = 'https://www.youtube.com/embed/' . rawurlencode($vid) . '?' . http_build_query($base + ['autoplay' => 1, 'mute' => 1]);
} elseif ($is_vimeo) {
    $vid = "";
    if (preg_match('#vimeo\.com/(?:video/)?(\d+)#', $url, $m)) $vid = $m[1];
    if ($vid === "") return;
    $base = [];
    if ($loop) $base['loop'] = 1;
    $idle_src = 'https://player.vimeo.com/video/' . rawurlencode($vid) . ($base ? '?' . http_build_query($base) : '');
    $autoplay_src = 'https://player.vimeo.com/video/' . rawurlencode($vid) . '?' . http_build_query($base + ['autoplay' => 1, 'muted' => 1]);
}

$has_facade = $poster !== "";
$iframe_src = $has_facade && !$autoplay ? "" : ($autoplay ? $autoplay_src : $idle_src);
$iframe_data_src = $has_facade && !$autoplay ? ' data-src="' . esc_attr($idle_src) . '"' : '';

$play_label = $text($args["play_label"] ?? "");
$play_label = $title !== "" && $play_label !== "" ? sprintf("%s : %s", $play_label, $title) : $play_label;
// Quand la facade est visible, l'iframe / <video> est retiré de l'ordre de tabulation
// (le bouton .poster prend le focus). Le JS retire `tabindex="-1"` au click.
$hide_tab = $has_facade ? ' tabindex="-1"' : '';
?>

    <div class="<?= $classes ?>" data-type="<?= esc_attr($type) ?>" data-autoplay="<?= $autoplay ? "true" : "false" ?>"<?= ($is_youtube || $is_vimeo) ? ' data-autoplay-src="' . esc_attr($autoplay_src) . '"' : '' ?><?= $attributes ?>>
    <?php if ($is_youtube || $is_vimeo) : ?>
        <iframe
            <?= $iframe_src !== "" ? 'src="' . esc_url($iframe_src) . '"' : "" ?>
            <?= $iframe_data_src ?>
            title="<?= esc_attr($title) ?>"
            loading="lazy"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen<?= $hide_tab ?>></iframe>

    <?php else :
        $flags = ['controls', 'preload="metadata"'];
        if ($autoplay) {
            $flags[] = 'autoplay';
            $flags[] = 'muted';
            $flags[] = 'playsinline';
        }
        if ($loop) $flags[] = 'loop';
    ?>
        <video
            <?= $poster !== "" ? 'poster="' . esc_url($poster) . '"' : "" ?>
            <?= $title !== "" ? 'title="' . esc_attr($title) . '"' : "" ?>
            <?= implode(' ', $flags) ?><?= $hide_tab ?>>
            <?php foreach ($urls as $u) : ?>
                <source src="<?= esc_url($u) ?>" type="<?= esc_attr($detect_mime($u)) ?>">
            <?php endforeach ?>
        </video>
    <?php endif ?>

    <?php if ($has_facade) : ?>
        <button type="button" class="poster" aria-label="<?= esc_attr($play_label) ?>">
            <?php component::image([
                "image" => $poster,
                "alt" => "",
                "lazy" => true,
                "decoding" => "async",
            ]); ?>
            <div class="play" aria-hidden="true">
                <?php component::icon("play", 60, 60) ?>
            </div>
        </button>
    <?php endif ?>
</div>
