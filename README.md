Voici les infos à savoir concernant **MovieMate**

### Authentification

2 roles peuvent être attribués aux comptes.

- Utilisateur.ice :
Correspond à une données vide dans la colonne `role` de la table `user`
Ils et elles peuvent se connecter, visionner les pages détaillées des films, donner des avis eventuellement accompagnés de commentaires.
Ils et elles peuvent aussi se déconnecter ou modifier les informations de leur compte (pseudo, mot de passe et image de profil).

# Compte utilisateur.ice déjà crée :
Nom d'utilisateur : User.jpg
Mot de passe : progServ123_

- Administrateur.ice :
Correspond à une données "admin" dans la colonne `role` de la table `user`
En plus des fonctions utilisteur.ice, ils et elles peuvent accèder à une page admin leur permettant de supprimer ou ajouter des films, supprimer des utilisateur.ice.s ou modifier leur rôle et supprimer des commentaires. Dans les pages détaillées des films, ils et elles peuvent supprimer directement les commentaires.

# Compte Administrateur.ice déjà crée :
Nom d'utilisateur : MovieMate
Mot de passe : progServ123_

- Les utilisteur.ice.s non connectée.s
Ne peuvent pas accèder aux fonctions susmentionnées. Ils et elles ne peuvent notamment pas accèder aux pages détailées des films et commenter ces derniers.

### FontAwesome

Afin de rendre plus attrayant les différentes pages, des pictogramme ont été utilisés. Ces dernier proviennent de fontawesome.com.

Pour que ces derniers soient affichés, une connexion internet est nécessaire lors de l'utilisation de notre programme. Dans le cas contraire, les pictogrammes ne seront pas affichés.

De plus, les pages pictogrammes ne peuvent être chargés que 10'000x par mois (nous utilisons l'offre sans frais). En cas de dépassement, les pictogrammes ne seront pas affichés.

### Base de données

La base de données .sqlite a été développée sur DB Browser for SQlite.

Certains comptes, films, ratings et l'intégralité des genres ont été manuellement incerées dans cette dernière. De ce fait, certains contenus peuvent ne pas respecter certaines validations de notre programme.

### Nécessaire lors de l'utilisation de notre programme (en phase de développement)

- Être connecté à internet. (nécessaire pour le chargement des pictogrammes de fontawesome)
- Démarrer MailHog via le terminal et accèder à localhost:8025 (nécessaire lors de la création de comptes et de la vérification par mail).
- Démarrer MAMP/WAMP/XAMP (PHP, SGBD, Serveur Web);
- `Adapter le port (actuellement 8888) dans le fichier config/base_url.php`

### Cahier des charges

La majorité des points mentionnés dans la section "3. Besoins et fonctionnalités" ont été respectés mise à part :
- la page regroupant tous les films ne comporte pas de réels filtres mais un "<select>" permettant de modifier l'ordre d'afficage (ORDER BY)
- l'âge des utilisateurs n'est finalement pas demandé car, non pertinent.
- les admins ne peuvent pas modifier les informations des films (ils doivent être supprimés puis ajouter à nouveau) (manque de temps).

# points bonus
- Multi-linguisme : pas intégrés par manque de temps.
- Déploiement : pas intégrés par manque de temps.
- Commen

### Divers
- le poids des images de films ne peuvent pas excéder 2 MB.
- 
