# Lightdark

Ce composant gere le basculement `light` / `dark` du starter.

## Fichiers

- `lightdark.php` : markup du bouton
- `lightdark.js` : logique de bascule et synchro avec le navigateur
- `lightdark.css` : styles du bouton

## Priorite d'affichage

L'ordre de priorite est le suivant :

1. `data-lightdark="light"` ou `data-lightdark="dark"` sur `html`
2. la valeur sauvegardee en `localStorage`
3. la preference systeme du navigateur via `prefers-color-scheme`

Autrement dit :

- si le systeme `lightdark` est implemente et qu'un choix utilisateur existe, ce choix est prioritaire
- si `lightdark` n'est pas implemente, ou si aucun choix n'est force, c'est `prefers-color-scheme` du navigateur qui fait foi

## Anti-FOUC

Un script dans `starter/layout.php` lit `localStorage` tres tot dans le `head` et pose `data-lightdark` avant le chargement complet du CSS.

Cela evite un flash entre le mode clair et sombre quand un choix utilisateur a deja ete enregistre.

## Fonctionnement CSS

Dans `assets/styles/variables.css` :

- `:root[data-lightdark="light"]` force les variables light
- `:root[data-lightdark="dark"]` force les variables dark
- `@media (prefers-color-scheme: dark)` sert de fallback seulement si aucun mode n'est force

Le fallback systeme s'applique donc quand `html` n'a pas de `data-lightdark`.

## Utilisation

Pour afficher le bouton :

```php
<?php common("lightdark"); ?>
```

## Note pour la suite

Si plus tard vous ajoutez des themes de couleur, il vaut mieux garder `lightdark` pour la luminance (`light` / `dark`) et separer cela d'un futur systeme de palette ou de skin.
