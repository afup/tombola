# Tombola AFUP

Ce dépôt permet de réaliser des tirages au sort en live, sur la base d'inscriptions Github.

## Principe général

Le principe général est le suivant:

* Les visiteurs désirant participer se rendent sur la home
* Un login Github leur est proposé s'ils n'ont pas de session en cours
* Après le login, ils sont redirigés vers /tombola
  * Leur participation est enregistrée en BDD
  * Sur la page /tombola, une connexion websocket est ouverte pour envoyer les principales infos issues de github (nom, prénom, avatar et nickname)
* Si l'utilisateur est reconnu comme admin il est redirigé vers /admin
  * Toutes les participations déjà enregistrées à la date du jour sont affichées
  * Une connexion websocket est ouverte pour afficher les nouvelles participations en live
  * Un bouton permet d'effectuer un tirage au sort en JS parmi les utilisateurs enregistrés
  
## Développer en local

### Dans tous les cas

Il y aura besoin de récupérer des  `GITHUB_CLIENT_ID` et `GITHUB_CLIENT_SECRET` en créant un Oauth App Github.
Voir plus bas sur comment la créer.

### Installation via docker

Dépendances:

* docker (version 1.9 minimum)
* docker-compose (version 1.9 minimum)
* make

Lancer un `make docker-up`, attendre que les services soient lancés puis effectuer un `make init` dans une autre fenêtre.

La configuration des `GITHUB_CLIENT_ID` et `GITHUB_CLIENT_SECRET`, ainsi que les ports utilisés pourront être modifiés dans le fichier `docker-compose.override.yml`. 

### Installation manuelle

Dépendances:

* PHP 7.0+
* MySQL
* Composer

Tester:

* **Installer les dépendances:** composer install
* **Créer et importer la BDD:** Les fichiers à importer sont versionnés dans sql/.
* **Lancer le webserver de PHP:** dans le répertoire public: `GITHUB_CLIENT_ID=[ClientId] GITHUB_CLIENT_SECRET="[ClientSecret]" MYSQL_HOST=[IP Mysql] MYSQL_LOGIN=[user mysql] MYSQL_PASSWORD=[Pwd mysql] MYSQL_PORT=[Port Mysql] MYSQL_DATABASE=[Nom de la bdd] php -S 127.0.0.1:8080`
* **Lancer le serveur de websocket:** à la racine du projet: `php server.php`


## Créer une Oauth App Github

* sur connecter avec son compte sur [Github](https://github.com)
* dans le menu, cliquer sur `Your Profile` (ou aller directement [ici](https://github.com/settings/profil))
* cliquer sur `Developer settings`, puis `New Oauth App`.
* Dans `Application name`, donner un nom à l'application (par exemple `Tombola AFUP dev`)
* Dans `Homepage URL̀`, mettre l'adresse locale de l'application (par exemple `http://localhost:9275/`)
* Il n'y a pas besoin de renseigner `Application description`.
* Dans `Authorization callback URL`, indiquer l'adresse de la route `/callback` locale, par exemple `http://localhost:9275/callback`.
* Cliquer sur `Register Application`.

Le "Client ID" et "Client Secret" seront alors affichés, qu'il faudra respectivement utiliser en tant que variables d'environnement `GITHUB_CLIENT_ID` et `GITHUB_CLIENT_SECRET`.
