# Gestion du CSS

Ce document décrit le fonctionnement CSS du starter, depuis les sources dans
`assets/` jusqu'au HTML envoyé au navigateur.

## Vue d'ensemble

Le starter utilise quatre stratégies :

| Type | Sortie | Chargement |
|---|---|---|
| CSS critique global | `web/assets/critical.css` | Inline dans le `<head>` |
| CSS critique contextuel | CSS du hero utilisé | Inline dans le `<head>` |
| CSS partagé | `common.css` | `<link>` dans le `<head>` |
| CSS contextuel | Fichier CSS individuel | Selon le template utilisé |

Le builder génère aussi `web/assets/css-bundles.json`. Ce manifeste permet au
PHP de savoir si un fichier appartient à un bundle ou doit être chargé
individuellement.

Les URLs CSS reçoivent automatiquement une version basée sur le contenu :

```html
<link rel="stylesheet" href="/web/assets/common.css?v=441afed985">
```

Ce versionnage est actif uniquement après `npm run prod`. Le builder écrit son
mode et les hashes dans `web/assets/build.json`, puis PHP lit ce manifeste.
Après `npm run build` ou `npm run dev`, les URLs restent sans paramètre `?v`.

## Le fichier app.css

`assets/app.css` pilote le CSS critique et les bundles.

Syntaxe :

```css
@import "chemin/fichier.css" cible;
```

Cibles disponibles :

| Cible | Sortie | Usage |
|---|---|---|
| `critical` | `critical.css` | Styles nécessaires au premier rendu |
| `common` | `common.css` | Éléments globaux et partagés |

Exemple :

```css
@import "styles/reset.css" critical;
@import "styles/layout.css" critical;
@import "common/header-nav/header-nav.css" common;
@import "components/slider/slider.css" common;
```

Un chemin doit être relatif au dossier `assets/`. Il faut donc écrire
`strates/...` et non `strate/...`.

## CSS critique

Les imports `critical` sont compilés dans `web/assets/critical.css`, puis leur
contenu est injecté dans :

```html
<style id="critical-css">...</style>
```

Le CSS critique doit rester petit. Il contient principalement :

- le reset ;
- les variables ;
- le layout général ;
- les polices ;
- les classes utilitaires nécessaires au premier rendu ;
- les petits composants toujours visibles immédiatement.

Il ne faut pas y placer toutes les cards, strates ou variantes de page.

## CSS des heroes

Un hero est rendu avec :

```php
hero("homepage", $args);
```

Le helper détecte automatiquement :

```txt
assets/heros/hero-homepage/hero-homepage.css
```

Cette CSS est ajoutée au CSS critique uniquement sur les pages utilisant ce
hero. Un autre hero ne sera donc pas chargé inutilement.

Il ne faut pas importer les CSS de heroes dans `app.css`.

## CSS des strates

Une strate est rendue avec :

```php
strate("text", $args);
strate("quote", $args);
```

Par défaut :

1. La CSS de la première strate est ajoutée dans le `<head>`.
2. La CSS de chaque strate suivante est insérée avec un `<link>` juste avant
   son HTML.
3. Une même CSS n'est insérée qu'une seule fois sur la page.

Exemple de rendu :

```html
<link rel="stylesheet" href="/web/assets/strates/strate-quote/strate-quote.css?v=...">
<section class="strate strate-quote">
    ...
</section>
```

Cette stratégie évite de bloquer le premier rendu avec toutes les strates,
fonctionne sans JavaScript et évite le FOUC lors d'une restauration du scroll.
Ce n'est cependant pas du vrai lazy loading réseau : le navigateur chargera
la feuille dès qu'il rencontrera son `<link>` dans le HTML.

Il ne faut pas importer les CSS de strates dans `app.css` si ce comportement
contextuel est souhaité.

### Forcer une strate dans un bundle

Une strate utilisée sur presque toutes les pages peut être ajoutée à un
bundle :

```css
@import "strates/strate-text/strate-text.css" common;
```

Dans ce cas :

- ses règles sont intégrées dans `common.css` ;
- son fichier CSS individuel n'est plus généré ;
- aucun `<link>` individuel n'est ajouté avant la strate.

Il faut réserver ce choix aux strates réellement fréquentes, sinon
`common.css` grossit pour toutes les pages.

## CSS des composants, cards et common

Les templates PHP enregistrent automatiquement leur fichier CSS homonyme
lorsqu'ils sont utilisés :

```txt
assets/components/accordion/accordion.php
assets/components/accordion/accordion.css
```

Si la CSS est déclarée dans un bundle dans `app.css`, le bundle est chargé.
Sinon, le fichier CSS individuel est ajouté dans le `<head>`.

Exemple de composant bundlé :

```css
@import "components/slider/slider.css" common;
```

Exemple de composant contextuel : ne pas l'importer dans `app.css`. Sa CSS sera
compilée séparément et chargée uniquement lorsque son template PHP est rendu.

Pour les éléments `common` présents sur presque toutes les pages, comme le
header ou le footer, le bundle `common` est généralement le bon choix.

## Choisir la bonne stratégie

| Situation | Stratégie recommandée |
|---|---|
| Nécessaire au premier rendu de toutes les pages | `critical` |
| Hero propre à une page | Automatique via `hero()` |
| Élément global fréquent | Bundle `common` |
| Petit composant très fréquent | Bundle `common` |
| Composant occasionnel | CSS individuelle automatique |
| Première strate | Automatique dans le `<head>` |
| Strates suivantes | `<link>` automatique avant la strate |
| Strate présente presque partout | Bundle explicite, après mesure |

## Bonnes pratiques

- Garder `critical.css` aussi petit que possible.
- Ne pas placer une CSS dans un bundle uniquement pour réduire le nombre de
  requêtes.
- Laisser les CSS rares hors de `app.css`.
- Éviter d'importer un même fichier dans plusieurs cibles.
- Vérifier `web/assets/css-bundles.json` en cas de doute.
- Mesurer sur une page réelle avec cache désactivé avant de déplacer une CSS.
- Utiliser les variables de `assets/styles/variables.css` pour les couleurs,
  espacements et modes clair/sombre.

## Build

Build de développement :

```bash
npm run build
```

Mode watch :

```bash
npm run dev
```

Build de production avec minification :

```bash
npm run prod
```

Les fichiers générés se trouvent dans `web/assets/`. Il ne faut pas les
modifier directement : toute modification doit être faite dans `assets/`.

## Vérifications

Après un changement de stratégie CSS :

1. Lancer le build.
2. Vérifier `web/assets/css-bundles.json`.
3. Inspecter les `<style>` et `<link>` dans le HTML rendu.
4. Vérifier qu'une CSS n'est pas chargée deux fois.
5. Tester un rechargement en haut et en bas de page.
6. Contrôler le FOUC, le LCP et les requêtes bloquantes dans Lighthouse.
