# Newsletter

Formulaire d'inscription AJAX utilisant les champs et la validation du starter.
Sans endpoint, la requête est simulée côté navigateur.

```php
component::newsletter([
    "title" => "Recevez nos actualités",
    "text" => "<p>Une sélection envoyée une fois par mois.</p>",
    "consent_label" => "J'accepte de recevoir la newsletter.",
]);
```

## Arguments

- `title`, `text` : contenu introductif.
- `email_label`, `email_placeholder` : personnalisation du champ e-mail.
- `submit_label` : texte du bouton.
- `consent_label` : ajoute une case de consentement obligatoire.
- `success_message`, `error_message` : messages après soumission.
- `endpoint` : URL AJAX facultative. Vide, la réponse est simulée.
- `classes`, `attributes` : personnalisation du conteneur.
