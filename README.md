# Contacts_manager_api
api_contacts_manager
un API REST avec MySQL, PHP et Slim Framework

#Méthodes HTTP
Une API RESTful bien conçue devrait prendre en charge les méthodes HTTP les plus utilisées (GET, POST, PUT et DELETE).
Il existe d'autres méthodes HTTP comme OPTIONS, HEAD mais celles-ci sont utilisées le plus souvent. 
Chaque méthode doit être utilisée en fonction du type d'opération que vous effectuez.

GET Pour récupérer une ressource.
POST Pour créer une nouvelle ressource.
PUT Pour mettre à jour les ressources existantes.
SUPPRIMER Pour supprimer une ressource.

#Slim PHP Framework Micro

Au lieu de commencer à développer un nouveau cadre REST à partir de zéro, il vaut mieux aller avec 
un cadre déjà éprouvé. Puis je suis tombé sur le cadre Slim et l'ai sélectionné pour les raisons suivantes.

1. Il est très léger, propre et un débutant peut facilement comprendre le cadre.
2. Prend en charge toutes les méthodes HTTP GET, POST, PUT et DELETE qui sont nécessaires pour une API REST.
3. Plus important encore, il fournit une architecture de couche intermédiaire qui 
sera utile pour filtrer les requêtes. Dans notre cas, nous pouvons l'utiliser pour vérifier la clé API (token / jeton).

#Installation de WAMP Server (Apache, PHP et MySQL)

WAMP vous permet d'installer Apache, PHP et MySQL avec un seul programme d'installation,
 ce qui réduit le fardeau d'installation et de configuration séparée. Vous pouvez également utiliser XAMP, LAMP (sous Linux) et MAMP 
(sur MAC). WAMP vous fournit également phpmyadmin pour interagir facilement avec la base de données MySQL.

#Installation de l'extension client REST avancée de Chrome pour les tests

L'extension de client REST avancée de Chrome offre un moyen simple de tester l'API REST.
 Il fournit beaucoup d'options comme l'ajout des en-têtes de demande, 
 l'ajout de paramètres de demande, la modification de la méthode HTTP tout 
 en frappant une url. Installez l'extension client REST avancée dans le navigateur chrome. Une fois que vous l'avez installé,
 vous pouvez le trouver dans Chrome Apps ou une icône dans le coin supérieur droit.
 
 #API REST pour le gestionnaire de contacts

Pour démontrer l'API REST, je considère un exemple d'application Contatcs Manager avec des fonctionnalités très minimes.
1. Opérations liées aux utilisateurs comme l'enregistrement et le login
2. Opérations liées à la tâche comme la création, la lecture, la mise à jour et la suppression de contact.
 Tous les appels d'API liés aux contacts doivent inclure une clé API dans le champ d'en-tête Autorisation.

Voici la liste des appels API que nous allons construire dans ce didacticiel. 
Vous pouvez remarquer que le même point d'extrémité url est utilisé pour plusieurs appels d'api, 
mais la différence est le type de méthode HTTP que nous utilisons pour frapper l'url.
 Supposons que si nous frappons / contacts avec la méthode POST, un nouveau contact sera créé.
 De plus, si nous frappons / contactons avec la méthode GET, tous les contatcs seront répertoriés.
 
 
 
 # API Url Structure
URL	                                       Method		                        Description
/register	                                POST	(name, email, password)	    inscription utilisateur 
/login	                                   POST  (email, password)	         login
/contact/Ajout	                           POST	                            creer nouveau contact
/contacts	                                 GET		                           lister tous les contacts
/contacts/:id	                             GET	                            lister un seul  contacts
/contact/Modifier/:id	                     PUT	                            Modifier un contact
/contact/Supprimer/:id	                   DELETE Supprimer 	un contact


