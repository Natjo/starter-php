# Button

## Intent

Afficher une action sous la forme d'un bouton ou d'un lien visuellement
identique.

Utiliser ce composant pour :

- declencher une action JavaScript ou une soumission de formulaire
- naviguer vers une page avec l'apparence d'un bouton
- afficher un CTA avec une variante CSS
- associer une icone du sprite a un libelle

Ne pas utiliser ce composant pour :

- une navigation textuelle simple
- une action sans libelle visible
- un faux lien reposant uniquement sur JavaScript
- un bouton contenant du HTML riche

## Figma

Nom recommande du composant Figma :

```txt
Component / Button
```

Structure Figma attendue :

```txt
Button
  Icon (optional)
  Label
```

Variantes possibles :

| Variante | Usage | PHP |
|---|---|---|
| Default | Action principale standard | Sans classe supplementaire |
| Primary | Variante utilisant la couleur principale | `"classes" => "btn-1"` |
| Link | Navigation vers une URL | Avec `link` ou `url` |
| Button | Action locale ou formulaire | Avec `name`, sans URL |
| Disabled | Action indisponible | Attribut `disabled` ou `aria-disabled` |
| With icon | Action accompagnee d'une icone | Avec `icon` |

## Data Contract

Bouton d'action :

```php
[
    "name" => "Ouvrir",
    "classes" => "btn-1",
    "icon" => ["arrow-down", 14, 14],
    "attributes" => [
        "type" => "button",
        "data-dialog-id" => "dialog-contact",
    ],
]
```

Lien avec apparence de bouton :

```php
[
    "link" => [
        "url" => "/contact/",
        "title" => "Nous contacter",
        "target" => "",
    ],
    "classes" => "btn-1",
    "icon" => ["arrow-down", 14, 14],
    "attributes" => [],
]
```

Le composant accepte aussi les groupes `button` et `cta`, utiles quand les
donnees proviennent directement d'un groupe ACF :

```php
[
    "cta" => [
        "link" => [
            "url" => "/contact/",
            "title" => "Nous contacter",
            "target" => "",
        ],
    ],
]
```

## Signatures PHP

Appel court avec un bouton :

```php
component::btn("Ouvrir", "btn-1", ["arrow-down", 14, 14], [
    "type" => "button",
]);
```

Appel avec un tableau d'options :

```php
component::btn([
    "name" => "Ouvrir",
    "classes" => "btn-1",
]);
```

Appel avec une URL simplifiee :

```php
component::btn([
    "url" => "/contact/",
    "title" => "Nous contacter",
    "target" => "_blank",
]);
```

## Fields Mapping

| Figma layer | ACF field | PHP key | Type | Required | Notes |
|---|---|---|---|---:|---|
| Button | button ou cta | `button` ou `cta` | group | Non | Groupe racine optionnel |
| Label | name | `name` | text | Oui pour un bouton | Libelle visible du `<button>` |
| Link | link | `link` | link | Oui pour un lien | Attend `url`, `title`, `target` |
| Link URL | link.url | `link.url` ou `url` | url | Oui pour un lien | URL de destination |
| Link label | link.title | `link.title` ou `title` | text | Oui pour un lien | Libelle visible du `<a>` |
| Link target | link.target | `link.target` ou `target` | text/select | Non | Par exemple `_blank` |
| Icon | icon | `icon` | array/component | Non | Arguments transmis a `component::icon()` |
| Variant | variant | `classes` | text/select | Non | Par exemple `btn-1` |
| Attributes | attributes | `attributes` | array/string | Non | Attributs HTML supplementaires |

## Options

| Option | Type | Default | Impact |
|---|---|---|---|
| `name` | string | `""` | Libelle du bouton natif |
| `link` | array | `null` | Rend un `<a>` si `url` et `title` sont valides |
| `url` | string | `""` | Forme simplifiee de `link.url` |
| `title` | string | `name` | Forme simplifiee de `link.title` |
| `target` | string | `""` | Attribut `target` du lien |
| `button` | array | `null` | Payload prioritaire provenant d'un groupe |
| `cta` | array | `null` | Payload alternatif provenant d'un groupe |
| `classes` | string/array | `""` | Ajoute des classes a `.btn` |
| `icon` | array | `null` | Arguments positionnels de `component::icon()` |
| `attributes` | array/string | `[]` | Ajoute des attributs au bouton ou au lien |

## Rendering Rules

| Cas | Comportement |
|---|---|
| `button` est un tableau non vide | Son contenu devient le payload principal |
| `cta` est un tableau non vide | Utilise si `button` est absent |
| `link` contient une URL et un titre | Rend un element `<a>` |
| `url` est fourni sans `link` | Construit automatiquement le tableau `link` |
| URL ou titre du lien vide | Le composant ne rend rien |
| Aucun lien et `name` vide | Le composant ne rend rien |
| Aucun attribut `type` | Le `<button>` recoit `type="button"` |
| Attribut `type` fourni | Le type explicite est conserve |
| `target="_blank"` sans `rel` | Ajoute `rel="noopener noreferrer"` |
| Attribut `rel` explicite | Le composant conserve la valeur fournie |
| `icon` est un tableau non vide | L'icone est rendue avant le libelle |
| Classes supplementaires | Elles sont ajoutees apres la classe `.btn` |

Le contenu de `name` et de `title` est echappe. Le composant n'accepte pas de
HTML riche dans son libelle.

## HTML Contract

Bouton rendu :

```html
<button type="button" class="btn btn-1">
    <span>Ouvrir</span>
</button>
```

Lien rendu :

```html
<a href="/contact/" class="btn btn-1">
    <span>Nous contacter</span>
</a>
```

Avec icone :

```html
<a href="/contact/" class="btn btn-1">
    <svg class="icon icon-arrow-down" aria-hidden="true">...</svg>
    <span>Nous contacter</span>
</a>
```

Classes attendues :

| Classe | Role |
|---|---|
| `.btn` | Classe racine commune aux liens et boutons |
| `.btn-1` | Variante utilisant `--color-1` |
| Classes personnalisees | Variantes de contexte ou hooks JavaScript |

## Accessibility Contract

- utiliser un `<a>` uniquement lorsqu'une URL existe
- utiliser un `<button>` pour une action locale ou une soumission
- toujours fournir un libelle visible et explicite
- conserver `type="button"` hors d'une soumission de formulaire
- fournir `type="submit"` explicitement pour soumettre un formulaire
- utiliser l'attribut natif `disabled` pour un vrai bouton indisponible
- un lien avec `aria-disabled="true"` doit aussi etre neutralise par la logique
  applicative si sa navigation doit etre bloquee
- une icone decorative ne doit pas remplacer le texte
- annoncer dans le libelle si le lien ouvre un site externe lorsque le
  contexte ne le rend pas evident

## Content Rules

| Champ | Regle |
|---|---|
| `name` | Verbe d'action court, par exemple "Telecharger" |
| `link.title` | Destination ou action clairement identifiable |
| `classes` | Reservees au style et aux hooks de comportement |
| `attributes` | Ne pas dupliquer `class`, `href` ou `target` |
| `icon` | Doit referencer un symbole existant dans `icons.svg` |

Eviter les libelles generiques comme "Cliquer ici", "En savoir plus" sans
contexte ou les CTA inutilement longs.

## Integration Examples

Bouton ouvrant une modale :

```php
component::btn([
    "name" => "Voir la video",
    "classes" => "btn-1 dialog-trigger",
    "icon" => ["video", 18, 18],
    "attributes" => [
        "type" => "button",
        "aria-haspopup" => "dialog",
        "aria-controls" => "video-dialog",
        "data-dialog-id" => "video-dialog",
    ],
]);
```

CTA issu d'ACF :

```php
component::btn([
    "cta" => get_field("cta"),
    "classes" => "btn-1",
]);
```

Bouton de soumission :

```php
component::btn([
    "name" => "Envoyer",
    "attributes" => [
        "type" => "submit",
    ],
]);
```

## Dependencies

| Type | Fichier |
|---|---|
| PHP | `btn.php` |
| CSS | `btn.css` |
| Icone optionnelle | `components/icon/icon.php` |
| Sprite optionnel | `assets/img/icons.svg` |
| JavaScript | Aucun |

## AI Integration Notes

Use this component for every action styled as a button.

Choose the semantic element from the intent:

- navigation requires `link` or `url` and renders `<a>`
- an interface action requires `name` without an URL and renders `<button>`
- form submission requires an explicit `attributes.type` set to `submit`

Prefer the structured `link` format when data comes from ACF. Use `button` or
`cta` only when the source data already contains one of these groups.

Expected generated PHP:

```php
component::btn([
    "link" => $cta["link"] ?? null,
    "name" => $cta["name"] ?? "",
    "classes" => $variant,
    "icon" => $icon,
    "attributes" => $attributes,
]);
```
