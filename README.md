# Tombola afup
Ce repo permet de réaliser des tirages au sort en live, sur la base d'inscriptions github.

## Principe général
Le principe général est le suivant:

* Les visiteurs désirant participer se rendent sur la home
* Un login github leur est proposé s'ils n'ont pas de session en cours
* Après le login, ils sont redirigés vers /tombola
  * Leur participation est enregistrée en BDD
  * Sur la page /tombola, une connexion websocket est ouverte pour envoyer les principales infos issues de github (nom, prénom, avatar et nickname)
* Si l'utilisateur est reconnu comme admin il est redirigé vers /admin
  * Toutes les participations déjà enregistrées à la date du jour sont affichées
  * Une connexion websocket est ouverte pour afficher les nouvelles participations en live
  * Un bouton permet d'effectuer un tirage au sort en JS parmi les utilisateurs enregistrés
  
## Développer en local

Nécessaire:

* PHP 7.0+
* Mysql
* Composer

Tester:

* **Installer les dépendances:** composer install
* **Créer et importer la BDD:** Les fichiers à importer sont versionnés dans sql/.
* **Lancer le webserver de PHP:** dans le répertoire public: `GITHUB_CLIENT_ID=[ClientId] GITHUB_CLIENT_SECRET="[ClientSecret]" MYSQL_HOST=[IP Mysql] MYSQL_LOGIN=[user mysql] MYSQL_PASSWORD=[Pwd mysql] MYSQL_PORT=[Port Mysql] MYSQL_DATABASE=[Nom de la bdd] php -S 127.0.0.1:8080`
* **Lancer le serveur de websocket:** à la racine du projet: `php server.php`

