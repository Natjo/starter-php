# Shares

## Intent

Afficher une liste de boutons de partage ou de contact social.

Utiliser ce composant pour :

- partager la page courante sur un reseau supporte
- copier l'URL courante
- afficher un lien social direct
- afficher un lien email direct

Ne pas utiliser ce composant pour :

- une navigation sociale principale de site
- une liste de liens riche avec texte visible
- des URLs non fiables ou des schemes autres que `http`, `https`, `mailto`

## Data Contract

Format simple, avec generation automatique des URLs de partage depuis la page
courante :

```php
[
    "shares" => ["linkedin", "email", "copy"],
]
```

Format associatif, avec URL explicite par type :

```php
[
    "shares" => [
        "linkedin" => "https://ltd.com",
        "email" => "email#email.com",
    ],
]
```

Format detaille, utile pour surcharger le libelle ou l'icone :

```php
[
    "shares" => [
        "linkedin" => [
            "url" => "https://ltd.com",
            "label" => "Voir le profil LinkedIn",
        ],
        "email" => [
            "email" => "contact@example.com",
            "label" => "Envoyer un email",
        ],
    ],
]
```

## Signatures PHP

Appel court avec liste :

```php
component::shares(["linkedin", "email", "copy"]);
```

Appel avec titre :

```php
component::shares("Partager", ["linkedin", "email"]);
```

Appel avec options :

```php
component::shares([
    "title" => "Partager",
    "url" => "https://example.com/article",
    "list" => ["linkedin", "email", "copy"],
    "classes" => "shares-inline",
]);
```

## Options

| Option | Type | Default | Impact |
|---|---|---|---|
| `title` | string | `""` | Ajoute un titre accessible via `aria-labelledby` |
| `url` | string | page courante | URL utilisee pour les partages generes |
| `list` | array | `[]` | Liste des services a afficher |
| `classes` | string/array | `""` | Ajoute des classes a `.shares` |
| `attributes` | array/string | `[]` | Ajoute des attributs HTML au `<nav>` |

## Services

| Service | Icone | URL generee sans override |
|---|---|---|
| `email` | `email` | `mailto:?body={url}` |
| `copy` | `copy` | URL courante ou option `url` |
| `facebook` | `facebook` | Share dialog Facebook |
| `linkedin` | `linkedin` | Share dialog LinkedIn |
| `x` | `x` | Share dialog X |
| `whatsapp` | `whatsapp` | Share WhatsApp |

Les services inconnus sont ignores.

## Rendering Rules

| Cas | Comportement |
|---|---|
| Liste vide | Le composant ne rend rien |
| URL invalide | L'entree est ignoree |
| Service inconnu | L'entree est ignoree |
| Format simple `["linkedin"]` | Genere une URL de partage pour la page courante |
| Format associatif `"linkedin" => "https://..."` | Utilise cette URL telle quelle |
| Format email `"email" => "email#domain.com"` | Transforme `#` en `@` puis genere `mailto:` |
| Format email `"email" => "mailto:..."` | Conserve le lien `mailto:` |
| `copy` | Copie l'URL via JavaScript avec feedback accessible |

## HTML Contract

Structure rendue :

```html
<nav class="shares" aria-label="Partager" data-module="components/shares">
    <ul>
        <li>
            <button type="button" data-type="linkedin" data-url="https://www.linkedin.com/...">
                <svg class="icon icon-linkedin" aria-hidden="true">...</svg>
                <span class="sr-only">Partager l'article sur LinkedIn</span>
            </button>
        </li>
    </ul>
</nav>
```

Classes attendues :

| Classe | Role |
|---|---|
| `.shares` | Racine du composant |
| `.title` | Titre optionnel |
| `.tip` | Feedback visuel du bouton copy |
| `.is-copied` | Etat JS apres copie reussie |
| `.is-copy-error` | Etat JS apres erreur de copie |

## Accessibility Contract

Regles a conserver :

- le composant est rendu dans un `<nav>`
- sans titre visible, il utilise `aria-label="Partager"`
- avec titre, il utilise `aria-labelledby`
- chaque bouton contient un libelle `.sr-only`
- le bouton `copy` expose un status `aria-live="polite"`

## Security Rules

Les URLs acceptees sont limitees a :

- `http`
- `https`
- `mailto`

Les autres schemes sont rejetes.

## Dependencies

| Type | Fichier |
|---|---|
| PHP | `shares.php` |
| JS | `shares.js` |
| CSS | `shares.css` |
| Icones | `assets/img/icons.svg` |

## Integration Example

Dans une carte people :

```php
[
    "name" => "Jane Doe",
    "function" => "AI Strategist",
    "shares" => [
        "linkedin" => "https://www.linkedin.com/in/jane-doe",
        "email" => "jane#doe.com",
    ],
]
```

## AI Integration Notes

Preferer le format simple pour un vrai partage de page :

```php
"shares" => ["linkedin", "email", "copy"]
```

Preferer le format associatif pour des liens directs de personne ou de marque :

```php
"shares" => [
    "linkedin" => "https://www.linkedin.com/company/example",
    "email" => "contact@example.com",
]
```
