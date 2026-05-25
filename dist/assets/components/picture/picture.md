# Component Picture

Le composant `picture` affiche une balise `<picture>` responsive. Il fonctionne dans ce starter en mode fichier local, et reste compatible WordPress quand `lsd_get_thumb()` existe.

## Appel

```php
component::picture($args, $sizes = "full", $classes = "", $lazy = true, $placeholder = false, $breakpoint = 768);
```

## Donnees acceptees

`$args` peut contenir une cle `images` :

```php
[
    "images" => [
        "desktop" => "63-1400x1024.jpg",
        "mobile" => "63-1400x1024.jpg",
    ],
]
```

Ou directement une image :

```php
component::picture("63-1400x1024.jpg");
```

## Chemins locaux

Si l'image est une string non absolue, le composant la cherche dans `dist/img` via :

```php
dist_asset_url("img/" . $image)
```

Exemple :

```php
component::picture([
    "images" => [
        "desktop" => "63-1400x1024.jpg",
    ],
]);
```


```php
component::picture([
    "images" => [
        "desktop" => THEME_ASSETS . "img/63-1400x1024.jpg",
    ],
]);
```

## WordPress

Si l'image est numerique et que `lsd_get_thumb()` existe, le composant utilise cette fonction :

```php
component::picture([
    "images" => [
        "desktop" => 123,
        "mobile" => 456,
    ],
], ["500_500", "1024_600"]);
```

`$sizes` peut etre :

```php
"full"
```

ou une taille defini sur wordpress via add_image_size:

```php
["500x500", "1024x600"]
```

Dans ce cas :

- index `0` = taille desktop
- index `1` = taille mobile

Si `hasWebp()` existe, le composant ajoute aussi une source WebP.



