<?php common('header-nav'); ?>

<main id="main">
    <?php
    hero("page", [
        "title" => "Form"
    ]);
    ?>

    <div class="strate">
        <h1>Contact</h1>

        <form class="form" method="post" action="/contact" data-module="form/form">
            <fieldset>

                <?php form([
                    "type" => "text",
                    "label" => "Nom",
                    "name" => "name",
                    "required" => true,
                    "mandatory" => "Le nom est obligatoire.",
                    "placeholder" => "Votre nom",
                    "autocomplete" => "name",
                    "minlength" => 2,
                    "typemismatch" => "Le nom est trop court.",
                    "hint" => "Minimum 2 caractères.",
                ]); ?>

                <?php form([
                    "type" => "email",
                    "label" => "E-mail",
                    "name" => "email",
                    "required" => true,
                    "mandatory" => "L'e-mail est obligatoire.",
                    "placeholder" => "vous@example.com",
                    "autocomplete" => "email",
                    "typemismatch" => "L'e-mail n'est pas valide.",
                ]); ?>

                <?php form([
                    "type" => "url",
                    "label" => "Site web",
                    "name" => "website",
                    "required" => true,
                    "placeholder" => "https://example.com",
                    "typemismatch" => "L'URL n'est pas valide.",
                ]); ?>

                <?php form([
                    "type" => "tel",
                    "label" => "Téléphone",
                    "name" => "phone",
                    "required" => true,
                    "placeholder" => "0600000000",
                    "autocomplete" => "tel",
                    "pattern" => "[0-9+ .-]{8,}",
                    "data_patternmismatch" => "Le numéro de téléphone n'est pas valide.",
                ]); ?>

                <?php form([
                    "type" => "password",
                    "label" => "Mot de passe",
                    "name" => "password",
                    "required" => true,
                    "mandatory" => "Le mot de passe est obligatoire.",
                    "placeholder" => "Mot de passe",
                    "autocomplete" => "new-password",
                ]); ?>
            </fieldset>

            <fieldset>
                <?php form([
                    "type" => "number",
                    "label" => "Nombre",
                    "name" => "people",
                    "required" => true,
                    "mandatory" => "Indiquez un nombre de personnes.",
                    "placeholder" => "2",
                    "min" => 1,
                    "max" => 10,
                    "typemismatch" => "Le nombre doit être compris entre 1 et 10.",
                ]); ?>

                <?php form([
                    "type" => "date",
                    "label" => "Date",
                    "name" => "date",
                    "required" => true,
                    "mandatory" => "La date est obligatoire.",
                    "autocomplete" => "off",
                ]); ?>
            </fieldset>

            <fieldset>
                <?php form([
                    "type" => "select",
                    "label" => "Select",
                    "name" => "subject",
                    "required" => true,
                    "mandatory" => "Choisissez un sujet.",
                    "options" => [
                        ["label" => "Choisir", "value" => "", "hidden" => true],
                        ["label" => "Demande d'information", "value" => "info"],
                        ["label" => "Devis", "value" => "quote"],
                        ["label" => "Support", "value" => "support"],
                        ["label" => "Option désactivée", "value" => "disabled", "disabled" => true],
                    ],
                ]); ?>

                <?php form([
                    "type" => "select-custom",
                    "label" => "Select custom",
                    "name" => "timeline",
                    "required" => true,
                    "mandatory" => "Choisissez un délai.",
                    "options" => [
                        ["label" => "Choisir", "value" => "", "hidden" => true],
                        ["label" => "Dès que possible", "value" => "asap"],
                        ["label" => "Sous 1 mois", "value" => "one-month"],
                        ["label" => "Sous 3 mois", "value" => "three-months"],
                    ],
                ]); ?>

                <?php form([
                    "type" => "select-custom-full",
                    "label" => "Select custom full",
                    "name" => "budget",
                    "required" => true,
                    "mandatory" => "Choisissez un budget.",
                    "placeholder" => "Choisir un budget",
                    "options" => [
                        ["label" => "Moins de 5 000 €", "value" => "under-5000"],
                        ["label" => "5 000 € à 10 000 €", "value" => "5000-10000"],
                        ["label" => "Plus de 10 000 €", "value" => "over-10000"],
                    ],
                ]); ?>

                <?php form([
                    "type" => "radios",
                    "label" => "Radios",
                    "name" => "contact_preference",
                    "required" => true,
                    "options" => [
                        ["label" => "E-mail", "value" => "email"],
                        ["label" => "Téléphone", "value" => "phone"],
                        ["label" => "Aucune préférence", "value" => "none"],
                    ],
                ]); ?>

                <?php form([
                    "type" => "checkboxes",
                    "label" => "Checkboxes",
                    "name" => "services",
                    "required" => true,
                    "mandatory" => "Sélectionnez au moins un service.",
                    "options" => [
                        ["label" => "Design", "value" => "design"],
                        ["label" => "Développement", "value" => "development"],
                        ["label" => "Maintenance", "value" => "maintenance"],
                    ],
                ]); ?>

                <?php form([
                    "type" => "checkbox",
                    "label" => "J'accepte les conditions",
                    "name" => "terms",
                    "required" => true,
                    "mandatory" => "Vous devez accepter les conditions.",
                ]); ?>
            </fieldset>

            <fieldset>
                <?php form([
                    "type" => "textarea",
                    "label" => "Message",
                    "name" => "message",
                    "required" => true,
                    "mandatory" => "Le message est obligatoire.",
                    "rows" => 6,
                    "hint" => "Décrivez votre demande en quelques lignes.",
                ]); ?>
            </fieldset>

            <div class="action">
                <button type="submit">Envoyer</button>
                <button type="reset">Réinitialiser</button>
            </div>
        </form>
    </div>
</main>

<?php common('footer'); ?>
