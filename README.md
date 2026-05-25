# Starter kit

## CSS/JS – principe de build & chargement

Ce starterkit met en place un chargement CSS optimisé, basé sur une **collecte des styles utilisés** pendant le rendu PHP.

### Collecte des styles (PHP)

- Les pages utilisent `get_tpl()` en fin de template (`web/wp-content/themes/.../inc/tpl.php`).
- `header.php` démarre un buffer (`ob_start()`), puis la page est ré-inclus dans le template via `get_tpl()`.
- Les helpers `hero()` / `strate()` / `component::card()` appellent `addStyle($name, $folder)` pour déclarer les CSS nécessaires.

### CSS critique inline (head)

Dans `front/methods.php` → `styles()` :

- `assets/styles.css` (issu de `assets/app.css`) est **inliné** dans `<style id="critical-css">`.
- Le CSS du **hero de la page** (dossier `assets/heros/...`) est ajouté dans **la même balise** (pas de requête réseau).

### Bundles CSS (components/cards/strates)

Pour éviter trop de requêtes, on génère des bundles pilotés par des fichiers *entry* :

- `assets/components.css`
- `assets/cards.css`
- `assets/strates.css`
- `assets/modules.css`

Le chargement se fait **uniquement si** le groupe est demandé par `addStyle()`, avec `preload` puis `stylesheet`, dans l’ordre :
**modules → components → cards → strates**.

### Manifest bundles (bundle vs on-demand)

Le builder génère `assets/css-bundles.json` dans le thème compilé.

Ce manifest liste, par dossier (`modules/components/cards/strates`), quels fichiers `assets/<folder>/<name>/<name>.css` sont inclus dans le bundle.

Règle côté PHP (`addStyle()`):

- si un fichier est listé dans le manifest → on charge **le bundle** (`<folder>.css`)
- sinon → on charge **le fichier on-demand** `assets/<folder>/<name>/<name>.css`

### Builder (Node)

Le script `builder.js` :

- compile les `@import` de `assets/app.css` → `assets/styles.css`
- compile chaque CSS individuel (postcss + cssnano)
- compile les bundles `modules.css/components.css/cards.css/strates.css` en résolvant leurs `@import`
- écrit le manifest `assets/css-bundles.json` (utilisé par `addStyle()`)

## Configuration

### Configuration

#### 1 - Editer le fichier .env

Dupliquer et renommer .docker/.env-sample => **.docker/.env** **/!\ ne pas renommer autrement**

Renseigner les différentes variables.

```
COMPOSE_PROJECT_NAME # le nom de 'la stack docker-compose'
APP_NAME # le nom du projet sans caractères accentués ni espaces
DOMAIN # le domaine
WP_THEME_NAME # le nom du theme
```

Attention les variables commencant par WP_ sont utilisées en tant que variables d'environnement par le container worpress : ne pas supprimer ni renommer.


#### 2 - Editer le fichier .docker-compose

Dupliquer et renommer .docker/.docker-compose-sample.yml => **.docker/.docker-compose.yml** **/!\ ne pas renommer autrement**

Affiner la stack docker : bdd / composer / platform amd 64 ....

#### 3 - Ajouter le ServerName dans son propre hosts

```
127.0.0.1   ServerName.code
```

ou utiliser le script :
```
.docker/scripts/hosts-file-setup.sh
```

#### 4 - Générer les certificats

utiliser le script :
```
.docker/scripts/cert-create.sh
```

et truster les certificats pour Chrome et Safari
```
.docker/scripts/cert-trust.sh
```

### Lancement

```
.docker/run.sh
```


Le dossier du theme sera renommé conformément à la config dans .docker/.env
**Attention à modifier le .gitignore en conséquence pour versionner le dossier du theme**


Editer la feuille CSS **web/wp-content/themes/[default ou ${WP_THEME_NAME}]/style.css** : changer l'entête

```
/*
Theme Name: Mon Theme
Author: Lonsdale Dev Team
Author URI: https://www.lonsdale.fr/
Version: 1.0
Text Domain: default
*/
```

## Wordpress Administration
```
Dans apparence séléctionner le nouveau theme
```


### 4 - installation de la preprod

Se connecter en ssh au serveur bearstech:
```
ssh user@lonsdale-preprod.ovh.bearstech.com
```

**génerer cle ssh:**
ssh-keygen
cat ~/.ssh/id_rsa.pub 
copier la cle dans:
Settings > Repository > Deploy Keys

**Vider le dossier root:**
```
cd root
rm -rf web
git clone [le repo du projet] .
```

**Attention** checkout preprod

### 5 - base bearstech
Si besoin d'importer la base sur la préprod, voici comment obtenir les infos de connexions

Lire le fichier .my.cnf pour récupérer le password
```
cd [le repo du projet]/
ls -la
cat .my.cnf
```

host: 127.0.0.1  
user: [le user du projet]  
password: my.cnf password  
  
shh host: lonsdale-preprod.ovh.bearstech.com  
ssh user: [le user du projet]  
ssh key: user key id_rsa  



