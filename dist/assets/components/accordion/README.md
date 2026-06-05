# Accordion

## Intent

Afficher une liste de contenus repliables.

Le composant sert principalement a integrer :
- FAQ
- questions / reponses
- blocs d'informations secondaires
- contenus courts que l'utilisateur peut ouvrir a la demande

Ne pas utiliser ce composant pour :
- une navigation par onglets
- un slider
- une ancre de navigation
- un contenu editorial long qui doit rester visible directement

## Figma

Nom recommande du composant Figma :

```txt
Component / Accordion
```

Structure Figma attendue :

```txt
Accordion
  Item
    Title
    Text
  Item
    Title
    Text
```

Variantes possibles :

| Variante | Usage | PHP |
|---|---|---|
| Default | Un seul panneau ouvert | `"multiple" => false` |
| Multiple | Plusieurs panneaux ouverts | `"multiple" => true` |

## Data Contract

Structure attendue par le composant :

```php
[
    "items" => [
        [
            "title" => "Titre du panneau",
            "text" => "Contenu du panneau",
        ],
    ],
    "multiple" => false,
]
```

Appel PHP :

```php
component::accordion($items, $multiple = false, $classes = null, $attributes = null);
```

Appel avec tableau d'options :

```php
component::accordion([
    "items" => $items,
    "multiple" => false,
    "classes" => null,
    "attributes" => null,
]);
```

## Fields Mapping

| Figma layer | ACF field | PHP key | Type | Required | Notes |
|---|---|---|---|---:|---|
| Accordion | accordion | - | group/component | Oui | Groupe contenant le composant |
| Item list | items | items | repeater | Oui | Liste des panneaux |
| Item / Title | items.title | title | text | Oui | Texte du bouton |
| Item / Text | items.text | text | wysiwyg/string | Oui | Contenu du panneau |
| Variant / Multiple | multiple | multiple | true_false | Non | Autorise plusieurs panneaux ouverts |

## Options

| Option | Type | Default | Impact |
|---|---|---|---|
| `multiple` | bool | `false` | Change le comportement d'ouverture |
| `classes` | string\|array\|null | `null` | Ajoute des classes au conteneur |
| `attributes` | string\|array\|null | `null` | Ajoute des attributs au conteneur |

## Rendering Rules

| Cas | Comportement |
|---|---|
| Aucun item valide | Le composant ne rend rien |
| Item sans `title` | L'item est ignore |
| Item sans `text` | L'item est ignore |
| `multiple = false` | Un seul panneau peut etre ouvert |
| `multiple = true` | Plusieurs panneaux peuvent etre ouverts |
| Premier affichage en mode simple | Le premier panneau est ouvert |
| Premier affichage en mode multiple | Tous les panneaux sont ouverts |

## HTML Contract

Structure rendue :

```html
<div class="accordion" data-module="components/accordion" data-multiple="false">
    <div class="details">
        <h3 id="accordion-id-summary-0">
            <button class="summary" type="button" aria-expanded="true" aria-controls="accordion-id-panel-0">
                <span>Title</span>
                <span class="accordion-caret" aria-hidden="true"></span>
            </button>
        </h3>

        <div id="accordion-id-panel-0" class="details-content" aria-labelledby="accordion-id-summary-0" aria-hidden="false">
            <div class="text rte">
                Content
            </div>
        </div>
    </div>
</div>
```

Classes attendues par le CSS/JS :

| Classe | Role |
|---|---|
| `.accordion` | Conteneur du composant |
| `.details` | Item |
| `.summary` | Bouton d'ouverture |
| `.accordion-caret` | Indicateur visuel |
| `.details-content` | Panneau |
| `.text.rte` | Contenu enrichi |

Attributs attendus :

| Attribut | Role |
|---|---|
| `data-module="components/accordion"` | Hydratation JS |
| `data-context="@visible true"` | Chargement au moment utile |
| `data-multiple` | Active ou desactive le mode multiple |

## Accessibility Contract

Le composant doit toujours conserver :

| Element | Attributs |
|---|---|
| Bouton | `type="button"` |
| Bouton | `aria-expanded` |
| Bouton | `aria-controls` |
| Panneau | `aria-hidden` |
| Panneau | `aria-labelledby` |
| Icone visuelle | `aria-hidden="true"` |

Regles :
- le bouton doit rester un vrai `<button>`
- chaque bouton doit controler un seul panneau
- les ids doivent rester uniques dans la page
- l'etat ouvert/ferme doit rester synchronise entre `aria-expanded` et `aria-hidden`

## Content Rules

| Champ | Regle |
|---|---|
| `title` | Court, lisible comme un bouton |
| `text` | Peut contenir du HTML simple |
| `text` | Ne doit pas contenir de script ou iframe |
| `items` | Minimum 1 item valide |

Le contenu `text` passe par `wp_kses_post()`.

HTML autorise :
- paragraphes
- liens
- listes
- emphasis
- titres simples
- balises inline courantes

HTML filtre :
- `script`
- `style`
- `iframe`
- `object`
- `embed`

## Integration Example

Exemple proche ACF :

```php
$accordion = [
    "items" => [
        [
            "title" => "Livraison",
            "text" => "<p>Les delais dependent de la destination.</p>",
        ],
        [
            "title" => "Retours",
            "text" => "<p>Les retours sont possibles sous 14 jours.</p>",
        ],
    ],
    "multiple" => false,
];

component::accordion($accordion);
```

Exemple direct :

```php
component::accordion([
    [
        "title" => "Livraison",
        "text" => "Les delais dependent de la destination.",
    ],
    [
        "title" => "Retours",
        "text" => "Les retours sont possibles sous 14 jours.",
    ],
]);
```

## Dependencies

| Type | Fichier |
|---|---|
| PHP | `accordion.php` |
| CSS | `accordion.css` |
| JS | `accordion.js` |
| Hydration | `data-module="components/accordion"` |

## AI Integration Notes

Use this component when a Figma frame contains repeated collapsible rows,
FAQ blocks, or question/answer content.

Do not use this component for tabs, sliders, menus, nav anchors, or long
editorial sections.

Expected generated PHP:

```php
component::accordion([
    "items" => $items,
    "multiple" => $multiple,
]);
```

Expected item mapping:

```php
[
    "title" => $figmaItemTitle,
    "text" => $figmaItemText,
]
```
