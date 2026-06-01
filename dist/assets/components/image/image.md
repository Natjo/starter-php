# Component Image

Le composant `image` affiche une balise `<img>`. Il est fait pour une image simple, sans sources responsive. Pour une image responsive, utiliser plutot `component::picture()`.

## Appel

```php
component::image($image, $size = "full", $classes = "", $lazy = true, $attributes = null);
```

## Donnees acceptees

Image directe :

```php
component::image(THEME_ASSETS . "img/63-1400x1024.jpg");
```

Avec options :

```php
component::image([
    "image" => THEME_ASSETS . "img/63-1400x1024.jpg",
    "alt" => "Description de l'image",
    "classes" => "image-cover",
    "lazy" => true,
]);
```

Alias acceptes pour l'image :

```php
[
    "image" => "...",
    "src" => "...",
    "url" => "...",
]
```

## Chemins starter

Pour une image d'asset du starter, utiliser `THEME_ASSETS`.

Exemple :

```php
component::image(THEME_ASSETS . "img/63-1400x1024.jpg");
```

Pour une image d'upload, utiliser `THEME_UPLOADS`.

Exemple :

```php
component::image(THEME_UPLOADS . "photo.jpg", "130x87");
```

cherche :

```txt
dist/uploads/photo.jpg
```

Une image d'upload ne doit pas etre appelee avec un simple nom de fichier.

## Sizes starter et WordPress

Dans WordPress, `$size` correspond a une taille declaree avec `add_image_size()`.

Dans le starter, les tailles sont declarees dans `image-sizes.json` a la racine du projet.

Exemple :

```json
{
    "_defaults": {
        "quality": 85,
        "filter": "lanczos",
        "sharpen": 0.2
    },
    "130x87": {
        "width": 130,
        "height": 87,
        "fit": "cover",
        "position": "center",
        "sharpen": 0.2
    },
    "card": {
        "width": 420,
        "height": 280,
        "fit": "cover",
        "position": "center",
        "sharpen": 0.2
    }
}
```

La page `admin/generate.php` genere les variantes avec Imagick directement dans `dist/uploads`, a cote de l'image originale. Les images `jpg` et `png` sont converties en `webp`, et les crops sont toujours generes en `webp`.

Exemple :

```txt
dist/uploads/photo.jpg
dist/uploads/photo.webp
dist/uploads/photo-130x87.webp
dist/uploads/photo-card.webp
```

Le bouton `Generate` ajoute seulement les tailles manquantes. Le bouton `Regenerate` regenere toutes les tailles. Le bouton `Clean` supprime les crops orphelins quand l'image originale n'existe plus. Le formulaire `Settings` permet de modifier les dimensions, le mode de crop, la position, le sharpen par format et la qualite WebP globale. Le filtre Imagick reste `lanczos`.

Le composant reproduit ensuite le comportement WordPress via le fallback `lsd_get_thumb()`.

Dans les deux environnements, le composant appelle :

```php
lsd_get_thumb($image, $size);
```

Exemple :

```php
component::image(THEME_UPLOADS . "photo.jpg", "card");
```

Le composant `image` ne bascule pas automatiquement vers le WebP. Il cherche d'abord :

```txt
dist/uploads/photo-card.jpg
```

Si ce fichier n'existe pas, il utilise :

```txt
dist/uploads/photo.jpg
```

Avec une taille en tableau :

```php
component::image(THEME_UPLOADS . "photo.jpg", [500, 500]);
```

Le composant cherche :

```txt
dist/uploads/photo-500x500.jpg
```

Les dimensions `width` et `height` sont lues avec `getimagesize()` quand le fichier est local. Elles ne sont pas deduites du nom du fichier.

## WordPress

Si l'image est numerique et que `lsd_get_thumb()` existe, le composant utilise cette fonction :

```php
component::image(123, "full");
```

Ou avec options :

```php
component::image([
    "image" => 123,
    "size" => "large",
    "alt" => "Texte alternatif personnalise",
]);
```

Si `alt` n'est pas fourni, le composant utilise l'alt retourne par `lsd_get_thumb()`.

Si `hasWebp()` existe, le composant utilise la version WebP retournee.

## Performance

Par defaut :

```php
"lazy" => true
```

Le composant rend :

```html
loading="lazy"
decoding="async"
fetchpriority="low"
```

Pour une image importante, par exemple une image LCP :

```php
component::image([
    "image" => "hero-1400x800.jpg",
    "alt" => "Description",
    "lazy" => false,
]);
```

Dans ce cas, le composant rend `fetchpriority="high"` par defaut.

La valeur peut etre forcee :

```php
component::image([
    "image" => "hero-1400x800.jpg",
    "fetchpriority" => "auto",
    "decoding" => "sync",
]);
```

Valeurs acceptees :

- `fetchpriority` : `high`, `low`, `auto`
- `decoding` : `async`, `sync`, `auto`

## Attributs

Les attributs libres passent par `component::attributes()` :

```php
component::image([
    "image" => "63-1400x1024.jpg",
    "alt" => "Description",
    "attributes" => [
        "data-demo" => "1",
    ],
]);
```

## Accessibilite

Le composant rend toujours un attribut `alt`.

Image informative :

```php
component::image([
    "image" => "portrait.jpg",
    "alt" => "Portrait de Marie",
]);
```

Image decorative :

```php
component::image([
    "image" => "decor.svg",
    "alt" => "",
]);
```
