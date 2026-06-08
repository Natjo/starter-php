# Notification

Affiche un message temporaire empilé en haut à droite de la page.

```php
component::notification([
    "title" => "Enregistrement terminé",
    "message" => "Les modifications ont bien été enregistrées.",
    "type" => "success",
    "duration" => 5000,
]);
```

## Arguments

- `message` ou `text` : contenu obligatoire.
- `title` : titre facultatif.
- `type` : `info`, `success`, `warning` ou `error`.
- `duration` : fermeture automatique en millisecondes, `0` pour la désactiver.
- `dismissible` : affiche ou masque le bouton de fermeture.
- `classes` et `attributes` : classes et attributs HTML supplémentaires.
