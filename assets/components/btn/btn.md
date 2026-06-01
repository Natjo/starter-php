# card-news

## Description
Composant utilisé pour afficher un article ou une actualité sous forme de carte.

## Figma
Component: `Card / News`

## Paramètres
| Nom | Type | Obligatoire | Description |
|---|---|---:|---|
| title | string | Oui | Titre de l’actualité |
| text | string | Non | Résumé court |
| image | string | Non | URL de l’image |
| url | string | Oui | Lien vers l’article |
| date | string | Non | Date affichée |
| category | string | Non | Catégorie de l’article |

## Options
| Variante | Description | css |
|---|---|---|
| default | Carte classique avec image ||
| compact | Carte sans image, plus dense |.compact|
| featured | Carte mise en avant |.featured|

## Exemple PHP
```php
$arg = [
    "title" => "",
    "text" => "",
    "image" =>"";
    "date" =>"";
    "category" =>"";
    "is_compact" => true,
    "is_featured" => false
];
 <?php component::card("news",$args) ?>
```

## CSS
.card-news
.card-news.compact
.card-news.featured

## JS
pas de js

## Responsive
conforme au figma

## Spécifications
| Key | Component | infos |
|---|---|---|
| title | component::title | H2 |
| text | component::text | 3 lignes max, ... si dépassement |
| image | component::picture | pas d'image mobile |
| date | component::date ||
| category | component::badge ||
