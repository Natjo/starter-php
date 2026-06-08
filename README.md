# Starter kit

## Documentation

- [Gestion du CSS](README-CSS.md)

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
