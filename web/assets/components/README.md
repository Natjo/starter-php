# Components Specs

Ce dossier contient les composants PHP du starter.

Chaque composant peut avoir son propre `README.md`. Le but n'est pas de faire
une documentation longue, mais une fiche de specs exploitable pour
l'integration.

Ces specs doivent aider a mapper :
- un composant Figma
- une structure ACF ou data
- un appel PHP
- un rendu HTML/CSS/JS

Elles doivent aussi permettre, plus tard, a une integration assistee par IA de
choisir le bon composant et de generer les pages avec moins d'ambiguite.

## Objectif Des README

Un README de composant doit repondre rapidement a ces questions :

- Quand utiliser ce composant ?
- Quand ne pas l'utiliser ?
- Quel est le nom attendu dans Figma ?
- Quelles donnees le composant attend-il ?
- Comment mapper Figma vers ACF puis PHP ?
- Quelles variantes existent ?
- Quelles regles de rendu ne doivent pas etre cassees ?
- Quelles contraintes d'accessibilite sont obligatoires ?
- Quel exemple PHP l'integrateur doit produire ?

## Format Recommande

Chaque README de composant doit suivre ce format autant que possible.

```md
# Component Name

## Intent

Role du composant.

Quand l'utiliser.
Quand ne pas l'utiliser.

## Figma

Nom exact ou recommande du composant Figma.

Structure attendue dans Figma.

Variantes Figma et correspondance PHP.

## Data Contract

Structure PHP attendue.

## Fields Mapping

| Figma layer | ACF field | PHP key | Type | Required | Notes |
|---|---|---|---|---:|---|

## Options

| Option | Type | Default | Impact |
|---|---|---|---|

## Rendering Rules

Regles de rendu.
Cas limites.
Fallbacks.
Elements ignores.

## HTML Contract

Structure HTML attendue.
Classes importantes.
Attributs requis.
Data attributes utilises par le JS.

## Accessibility Contract

Regles d'accessibilite a conserver.
Attributs ARIA requis.
Role des elements interactifs.

## Content Rules

Contraintes editoriales.
HTML autorise.
HTML filtre.
Longueur recommandee.

## Integration Example

Exemple PHP proche d'un retour ACF ou d'une structure data.

## Dependencies

Fichiers PHP, CSS, JS et hydratation.

## AI Integration Notes

Instructions courtes pour aider une IA a choisir ou eviter ce composant.
```

## Convention De Mapping

La section `Fields Mapping` est la plus importante pour l'integration.

Elle doit faire le pont entre les noms vus dans Figma, les champs ACF et les
cles PHP attendues par le composant.

Exemple :

```md
| Figma layer | ACF field | PHP key | Type | Required | Notes |
|---|---|---|---|---:|---|
| Title | title | title | text | Oui | Titre affiche |
| Text | text | text | wysiwyg | Non | Contenu enrichi |
| CTA | cta | cta | link | Non | Lien principal |
```

## Convention Figma

Les noms Figma doivent rester proches de l'arborescence du starter.

Exemples :

```txt
Component / Accordion
Component / Button
Common / Header Nav
Hero / Homepage
Strate / Accordion
Card / News
```

Quand un composant a des variantes, les documenter dans un tableau :

```md
| Variante Figma | Usage | PHP |
|---|---|---|
| Default | Affichage standard | `"variant" => "default"` |
| Compact | Affichage reduit | `"classes" => "compact"` |
```

## Convention Data

Les composants doivent privilegier des structures simples :

```php
[
    "title" => "",
    "text" => "",
    "items" => [],
    "image" => "",
    "link" => [],
]
```

Les README doivent indiquer les alias acceptes seulement s'ils existent dans le
composant.

## AI Integration Notes

Cette section doit etre courte et directive.

Bon exemple :

```md
Use this component when the Figma frame contains repeated collapsible rows,
FAQ blocks, or question/answer content.

Do not use this component for tabs, sliders, menus, nav anchors, or long
editorial sections.
```

Mauvais exemple :

```md
This component is flexible and can be used in many cases.
```

L'objectif est de reduire les erreurs de choix de composant.

## Checklist D'un README Utile

Un README est considere utile s'il contient :

- le nom Figma attendu
- la structure data exacte
- le mapping Figma / ACF / PHP
- au moins un exemple PHP realiste
- les regles de rendu
- les contraintes d'accessibilite
- les dependances CSS/JS
- une note claire pour l'integration IA

## Exemple De Reference

Le composant `accordion` sert de premiere reference pour ce format :

```txt
assets/components/accordion/README.md
```
