<?php
$args = normalize_args($args ?? null);

$title = isset($args["title"]) && is_scalar($args["title"])
    ? trim((string) $args["title"])
    : "Inscrivez-vous à notre newsletter";
$text = isset($args["text"]) && is_scalar($args["text"])
    ? trim((string) $args["text"])
    : "";
$email_label = isset($args["email_label"]) && is_scalar($args["email_label"])
    ? trim((string) $args["email_label"])
    : "Adresse e-mail";
$email_placeholder = isset($args["email_placeholder"]) && is_scalar($args["email_placeholder"])
    ? trim((string) $args["email_placeholder"])
    : "vous@exemple.fr";
$submit_label = isset($args["submit_label"]) && is_scalar($args["submit_label"])
    ? trim((string) $args["submit_label"])
    : "S'inscrire";
$success_message = isset($args["success_message"]) && is_scalar($args["success_message"])
    ? trim((string) $args["success_message"])
    : "Votre inscription a bien été prise en compte.";
$error_message = isset($args["error_message"]) && is_scalar($args["error_message"])
    ? trim((string) $args["error_message"])
    : "L'inscription a échoué. Veuillez réessayer.";
$consent_label = isset($args["consent_label"]) && is_scalar($args["consent_label"])
    ? trim((string) $args["consent_label"])
    : "";
$endpoint = isset($args["endpoint"]) && is_scalar($args["endpoint"])
    ? trim((string) $args["endpoint"])
    : "";
$classes = component::classes("newsletter", $args["classes"] ?? "");
$attributes = component::attributes($args["attributes"] ?? []);

static $newsletter_count = 0;
$newsletter_count++;
$uid = "newsletter-" . $newsletter_count;
?>

<section
    class="<?= $classes ?>"
    <?= $title !== ""
        ? 'aria-labelledby="' . esc_attr($uid . "-title") . '"'
        : 'aria-label="Inscription à la newsletter"' ?>
    data-module="components/newsletter"
    data-context="@visible true"<?= $attributes ?>>
    <div class="newsletter-content">
        <?php if ($title !== "") : ?>
            <h2 class="newsletter-title" id="<?= esc_attr($uid . "-title") ?>"><?= esc_html($title) ?></h2>
        <?php endif; ?>

        <?php if ($text !== "") : ?>
            <div class="newsletter-text"><?= wp_kses_post($text) ?></div>
        <?php endif; ?>
    </div>

    <form
        class="newsletter-form form"
        action="<?= $endpoint !== "" ? esc_url($endpoint) : "#" ?>"
        method="post"
        data-newsletter-form
        data-endpoint="<?= esc_url($endpoint) ?>"
        data-success-message="<?= esc_attr($success_message) ?>"
        data-error-message="<?= esc_attr($error_message) ?>">
        <div class="newsletter-fields">
            <?php form([
                "type" => "email",
                "label" => $email_label,
                "name" => "email",
                "placeholder" => $email_placeholder,
                "autocomplete" => "email",
                "required" => true,
                "mandatory" => "Veuillez renseigner votre adresse e-mail.",
                "typemismatch" => "Veuillez renseigner une adresse e-mail valide.",
            ]); ?>

            <?php component::btn([
                "name" => $submit_label,
                "classes" => "newsletter-submit",
                "attributes" => [
                    "type" => "submit",
                    "data-newsletter-submit" => true,
                ],
            ]); ?>
        </div>

        <?php if ($consent_label !== "") : ?>
            <?php form([
                "type" => "checkbox",
                "label" => $consent_label,
                "name" => "consent",
                "required" => true,
                "mandatory" => "Votre consentement est nécessaire pour vous inscrire.",
                "classes" => "newsletter-consent",
            ]); ?>
        <?php endif; ?>

        <div class="newsletter-status" role="status" aria-live="polite" data-newsletter-status hidden></div>
    </form>
</section>
