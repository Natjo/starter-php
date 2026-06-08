# Marquee

Crée un défilement horizontal infini qui inverse son sens lorsque la page
change de direction de scroll. Le contenu peut être déplacé à la souris,
au tactile, au trackpad ou avec les flèches du clavier. Le module est hydraté
à l'approche du viewport et son animation est totalement arrêtée hors écran.

```php
component::marquee([
    "items" => ["Design", "Développement", "Accessibilité", "Performance"],
    "direction" => "left",
    "speed" => 50,
    "resume_delay" => 120,
]);
```

## Arguments

- `items` : textes ou tableaux avec `text`, `content`, `url` et `target`.
- `card` : nom d'une card à utiliser pour chaque élément.
- `direction` : sens initial `left` ou `right`.
- `speed` : vitesse en pixels par seconde.
- `resume_delay` : reprise automatique après interaction, en millisecondes (`120` par défaut).
  La valeur `0` arrête définitivement l'autoplay après le premier geste.
- `aria_label`, `classes` et `attributes` : personnalisation du conteneur.
