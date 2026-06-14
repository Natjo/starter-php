# Icon

## Intent

Afficher une icone SVG issue du sprite `assets/img/icons.svg`.

Le composant sert a integrer :
- logos simples
- pictos inline
- icones decoratives
- icones accessibles avec libelle

Ne pas utiliser ce composant pour :
- une image bitmap
- une illustration complexe
- une icone externe hors du sprite
- un composant interactif a lui seul

## Figma

Nom recommande du composant Figma :

```txt
Component / Icon
```

Structure Figma attendue :

```txt
Icon
  Glyph
```

Variantes possibles :

| Variante | Usage | PHP |
|---|---|---|
| Decorative | Icone purement visuelle | Sans `label` |
| Accessible | Icone porteuse de sens | Avec `label` |

## Data Contract

Structure attendue par le composant :

```php
[
    "name" => "logo",
    "width" => 24,
    "height" => 24,
    "classes" => null,
    "label" => null,
    "attributes" => null,
]
```

Appel PHP court :

```php
component::icon("logo", 24, 24, "my-class", "Logo");
```

Appel avec tableau d'options :

```php
component::icon([
    "name" => "logo",
    "width" => 24,
    "height" => 24,
    "classes" => "my-class",
    "label" => "Logo",
    "attributes" => [
        "aria-hidden" => "true",
    ],
]);
```

## Fields Mapping

| Figma layer | ACF field | PHP key | Type | Required | Notes |
|---|---|---|---|---:|---|
| Icon | icon | - | group/component | Oui | Groupe racine |
| Glyph name | name | name | text | Oui | Doit correspondre a un `id` dans `icons.svg` |
| Width | width | width | number | Non | Defaut `24` |
| Height | height | height | number | Non | Defaut `24` |
| Extra classes | classes | classes | text/array | Non | Ajoute des classes CSS |
| Accessible label | label | label | text | Non | Si present, active `role="img"` |

## Options

| Option | Type | Default | Impact |
|---|---|---|---|
| `name` | string | `""` | Selectionne le symbole du sprite |
| `width` | int | `24` | Largeur de l'icone |
| `height` | int | `24` | Hauteur de l'icone |
| `classes` | string\|array\|null | `null` | Ajoute des classes au SVG |
| `label` | string\|null | `null` | Rend l'icone accessible |
| `attributes` | array\|string\|null | `null` | Ajoute des attributs au SVG |
| `url` | string | `THEME_ASSETS` | Base URL du sprite |

## Rendering Rules

| Cas | Comportement |
|---|---|
| `name` vide | Le composant ne rend rien |
| `width` ou `height` invalide | Fallback a une valeur >= `1` |
| `label` vide | `aria-hidden="true"` |
| `label` present | `role="img"` + `aria-label` |
| `classes` present | Ajout a `icon` et `icon-{name}` |

## HTML Contract

Structure rendue :

```html
<svg class="icon icon-logo" width="24" height="24" aria-hidden="true" focusable="false">
    <use href="/assets/img/icons.svg#logo"></use>
</svg>
```

Classes attendues :

| Classe | Role |
|---|---|
| `.icon` | Classe racine commune |
| `.icon-{name}` | Ciblage specifique par symbole |

Attributs rendus :

| Attribut | Role |
|---|---|
| `width` | Taille horizontale |
| `height` | Taille verticale |
| `focusable="false"` | Evite le focus parasite |
| `aria-hidden="true"` | Cas decoratif |
| `role="img"` | Cas accessible |
| `aria-label` | Libelle de l'icone |

## Accessibility Contract

Regles a conserver :

- une icone decorative doit rester `aria-hidden="true"`
- une icone informative doit recevoir un `label`
- l'icone ne doit pas etre focusable au clavier seule
- le sens ne doit pas reposer uniquement sur l'icone si elle est decorative

## Content Rules

| Champ | Regle |
|---|---|
| `name` | Doit exister dans `assets/img/icons.svg` |
| `label` | Court et descriptif |
| `classes` | Pour le style, pas pour la logique metier |

Le composant ne valide pas l'existence reelle du symbole dans le sprite.

## Integration Example

Exemple decoratif :

```php
component::icon("arrow-down", 14, 13);
```

Exemple accessible :

```php
component::icon([
    "name" => "logo",
    "width" => 122,
    "height" => 25,
    "label" => "Natjo",
]);
```

Exemple avec classes :

```php
component::icon("linkedin", 44, 44, "social-icon");
```

## Dependencies

| Type | Fichier |
|---|---|
| PHP | `icon.php` |
| Sprite SVG | `assets/img/icons.svg` |
| Build output | `web/assets/img/icons.svg` |

## AI Integration Notes

Use this component when the design references a symbol already present in the
SVG sprite.

Do not use this component for raster images, standalone illustrations, or
icons that are not part of `icons.svg`.

Expected generated PHP:

```php
component::icon([
    "name" => $name,
    "width" => $width,
    "height" => $height,
    "label" => $label,
]);
```
