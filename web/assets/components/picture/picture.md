# Component Picture

Le composant `picture` affiche une balise `<picture>` responsive. Il utilise `lsd_get_thumb()` pour recuperer l'image fallback, puis ajoute automatiquement une source WebP quand un fichier genere existe dans `web/uploads`.

## Appel

```php
component::picture($args, $sizes = "full", $classes = "", $lazy = true, $placeholder = false, $breakpoint = 768);
```

## Donnees acceptees

Image simple :

```php
component::picture(THEME_UPLOADS . "image2.jpg");
```

Avec une taille :

```php
component::picture(THEME_UPLOADS . "image2.jpg", "130x87");
```

Desktop et mobile :

```php
component::picture([
    "desktop" => THEME_UPLOADS . "desktop.jpg",
    "mobile" => THEME_UPLOADS . "mobile.jpg",
], ["desktop_size", "mobile_size"]);
```

Ou avec la cle `images` :

```php
component::picture([
    "images" => [
        "desktop" => THEME_UPLOADS . "desktop.jpg",
        "mobile" => THEME_UPLOADS . "mobile.jpg",
    ],
    "classes" => "picture-cover",
    "lazy" => true,
]);
```

## Chemins

Pour une image upload, utiliser `THEME_UPLOADS`.

```php
component::picture(THEME_UPLOADS . "image2.jpg", "130x87");
```

Le composant cherche alors :

```txt
web/uploads/image2.jpg
web/uploads/image2-130x87.webp
```

Pour une image d'asset du starter, utiliser `THEME_ASSETS`.

```php
component::picture(THEME_ASSETS . "img/63-1400x1024.jpg");
```

Une image d'upload ne doit pas etre appelee avec un simple nom de fichier.

## WebP

Le composant ne depend plus de `hasWebp()`.

Dans le starter, WebP est gere directement par `picture` :

```php
component::picture(THEME_UPLOADS . "image2.jpg", "130x87");
```

peut rendre :

```html
<picture>
    <source width="130" height="87" srcset="/web/uploads/image2-130x87.webp" type="image/webp">
    <img src="/web/uploads/image2.jpg" alt="" width="1400" height="1024" loading="lazy" fetchpriority="low">
</picture>
```

`lsd_get_thumb()` reste neutre : il retourne l'image fallback, pas le WebP. Le composant `picture` est responsable de trouver la source WebP.

## Sizes

`$sizes` peut etre :

```php
"full"
```

ou une taille declaree dans `starter/image-sizes.json` :

```php
"130x87"
```

ou un tableau pour desktop/mobile :

```php
["desktop_size", "mobile_size"]
```

Dans ce cas :

- index `0` = taille desktop
- index `1` = taille mobile

## WordPress

En WordPress, le composant reste compatible avec `lsd_get_thumb()`.

```php
component::picture([
    "desktop" => 123,
    "mobile" => 456,
], ["500x500", "1024x600"]);
```

La gestion WebP specifique au starter ne s'applique qu'aux URLs qui commencent par `THEME_UPLOADS`.
