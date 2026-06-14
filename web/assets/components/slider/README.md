# Slider

## Intent

Afficher une liste de cartes ou de contenus dans un carrousel horizontal.

Le composant gere :

- le scroll horizontal et le drag a la souris
- le scroll snap
- les boutons precedent et suivant
- la navigation au clavier
- une pagination optionnelle
- une timeline optionnelle
- les annonces accessibles de la diapositive active

## Appel PHP

Appel simple avec le composant `card-news` par defaut :

```php
component::slider($items);
```

Avec un autre composant de carte :

```php
component::slider($items, "solution");
```

Avec des options :

```php
component::slider($items, "solution", [
    "classes" => "slider-featured",
    "navigation" => true,
    "pagination" => false,
    "timeline" => true,
    "aria_label" => "Nos solutions",
]);
```

Le troisieme argument accepte soit une chaine de classes, soit un tableau
d'options.

## Data Contract

Chaque element de `$items` est transmis au composant de carte selectionne :

```php
$items = [
    [
        "title" => "Premier contenu",
        "text" => "Description du contenu",
    ],
    [
        "title" => "Deuxieme contenu",
        "text" => "Description du contenu",
    ],
];

component::slider($items, "solution", [
    "timeline" => true,
]);
```

Pour afficher directement du HTML sans composant de carte, utiliser `null` :

```php
component::slider([
    "<article>Premier contenu</article>",
    "<article>Deuxieme contenu</article>",
], null);
```

Le HTML direct passe par `wp_kses_post()`.

## Options

| Option | Type | Defaut | Impact |
|---|---|---|---|
| `classes` | string ou array | `""` | Ajoute des classes a `.slider` |
| `navigation` | bool | `true` | Affiche les boutons precedent et suivant |
| `pagination` | bool | `true` | Affiche les boutons de pagination |
| `timeline` | bool | `false` | Affiche la progression horizontale |
| `aria_label` | string | `"Carrousel"` | Nomme la region accessible |
| `prev_label` | string | `"Diapositive precedente"` | Libelle du bouton precedent |
| `next_label` | string | `"Diapositive suivante"` | Libelle du bouton suivant |
| `pagination_label` | string | `"Navigation du carrousel"` | Nomme la pagination |

L'ancien appel positionnel reste disponible :

```php
component::slider(
    $items,
    "solution",
    "slider-featured",
    true,
    false,
    "Nos solutions",
    "Solution precedente",
    "Solution suivante",
    "Navigation des solutions",
    true
);
```

Le dernier argument active la timeline. Pour les configurations longues,
preferer le tableau d'options.

## Initialisation JavaScript

Le composant PHP genere le markup, mais le slider doit etre initialise dans le
module JavaScript de la strate :

```js
import Slider from "@components/slider";

export default el => {
    const slider = new Slider(el.querySelector(".slider"));
    slider?.add?.();

    return () => {
        slider?.remove?.();
    };
};
```

Les imports suivants sont acceptes par le builder :

```js
import Slider from "@components/slider";
import Slider from "@components/slider/";
```

## API JavaScript

| Methode ou callback | Role |
|---|---|
| `add()` | Initialise le slider et ses evenements |
| `remove()` | Detruit les evenements et desactive le slider |
| `slideTo(index)` | Deplace le slider vers un index |
| `onchange(index, maxIndex, progress)` | Appele pendant le scroll |
| `onnext(index)` | Appele apres une navigation suivante |
| `onprev(index)` | Appele apres une navigation precedente |

Exemple :

```js
const slider = new Slider(sliderEl);

slider.onchange = (index, maxIndex, progress) => {
    console.log({ index, maxIndex, progress });
};

slider.add();
```

`progress` est compris entre `0` et `100`.

## Configuration CSS

Le nombre d'elements visibles et leur largeur sont pilotes avec des variables
CSS :

```css
.my-strate {
    .slider {
        --nb: 3;
        --offset: 4rem;
    }
}
```

| Variable | Defaut | Role |
|---|---|---|
| `--nb` | `1` | Nombre d'elements visibles |
| `--offset` | `0px` | Espace retire a chaque element pour laisser voir le suivant |
| `--gap` | variable globale | Espacement entre les elements |
| `--left` | calcule en JS | Distance entre le slider et le bord gauche du viewport |
| `--right` | calcule en JS | Distance entre le slider et le bord droit du viewport |

Le wrapper occupe toutes les colonnes de la grille du slider :

```css
.slider-wrapper {
    grid-column: 1 / -1;
}
```

Pour utiliser des lignes nommees comme `fluid` ou `full-end`, les parents du
slider doivent eux-memes exposer la grille ou un `subgrid`.

## Timeline

Activation :

```php
component::slider($items, "solution", [
    "timeline" => true,
]);
```

Structure rendue :

```html
<div class="slider-timeline" aria-hidden="true" data-slider-timeline>
    <span class="slider-timeline-progress"></span>
</div>
```

Le JavaScript met automatiquement a jour `--slider-progress` entre `0` et `1`.
La progression est animee avec `transform: scaleX()`.

Exemple de personnalisation :

```css
.slider-timeline {
    height: 2px;
    color: #fff;
    background-color: rgb(255 255 255 / .2);
}
```

La timeline est masquee lorsque le contenu ne depasse pas la largeur du
slider.

## HTML Contract

Structure principale :

```html
<div class="slider" role="region" aria-label="Carrousel">
    <div class="slider-navigation">...</div>
    <div class="slider-wrapper">
        <ul class="slider-content" role="list">
            <li class="item">...</li>
        </ul>
    </div>
    <nav class="slider-pagination">...</nav>
    <div class="slider-timeline">...</div>
    <div class="sr-only" aria-live="polite"></div>
</div>
```

| Classe | Role |
|---|---|
| `.slider` | Racine et grille du composant |
| `.slider-wrapper` | Zone de mesure de la largeur |
| `.slider-content` | Conteneur horizontal scrollable |
| `.item` | Element du carrousel |
| `.slider-navigation` | Boutons precedent et suivant |
| `.slider-pagination` | Navigation par diapositives |
| `.slider-timeline` | Rail de progression |
| `.slider-timeline-progress` | Barre de progression |
| `.disable` | Ajoute lorsque le contenu n'est pas scrollable |
| `.inactive` | Ajoute lorsque l'instance JS est retiree |

## Accessibilite

- la racine utilise `role="region"` et un `aria-label`
- le contenu est une liste semantique
- les fleches gauche et droite permettent de naviguer au clavier
- les boutons exposes utilisent `aria-disabled`
- la diapositive active est annoncee dans une zone `aria-live`
- la timeline est decorative et utilise `aria-hidden="true"`
- `prefers-reduced-motion` desactive l'animation de navigation

## Dependencies

| Type | Fichier |
|---|---|
| PHP | `slider.php` |
| JavaScript | `slider.js` |
| CSS | `slider.css` |
| Cartes | `assets/cards/<card>/<card>.php` |

