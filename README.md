Site-Web-catalogue-de-mangas---Langage-PHP-SQL-et-HTML-CSS

Application web permettant de consulter un catalogue de mangas, de gérer un espace utilisateur et de suivre sa progression de lecture.
Projet réalisé dans un cadre académique.

1. Présentation générale

Ce projet est un site web dynamique développé en PHP et qui utilise une base de données phpMyAdmin à laquelle le site accède via des requêtes SQL.
Il propose un catalogue de mangas, un système d’authentification, un espace utilisateur et un espace administrateur pour la gestion du contenu et des comptes.

Le site est conçu pour être exécuté en local.

2. Fonctionnalités
   
Fonctionnalités ouvertes à tous :

-Accueil avec présentation du site et barre de recherche

-Consultation du catalogue de mangas

-Recherche par titre, genre, type ou auteur

-Accès aux fiches mangas détaillées

-Mode clair / sombre stocké en session

Espace utilisateur :

-Création de compte

-Connexion et déconnexion

-Gestion du catalogue personnel

-Ajout d’un manga depuis le catalogue

-Mise à jour du chapitre en cours

-Suppression d’un manga du catalogue

Espace administrateur :

-Gestion des utilisateurs (promotion, rétrogradation, suppression)

-Ajout d’un manga avec informations détaillées

-Association d’auteurs, genres, types, statuts et éditeurs

-Ajout d’images (jusqu’à quatre par manga)

Sécurité : 

-Requêtes SQL préparées pour limiter les injections

-Protection contre les failles XSS via htmlspecialchars

-Vérification des droits d’accès selon le rôle (utilisateur / administrateur)

3. Structure du projet
index.php                // Page d’entrée, redirige vers la page d'accueil
Accueil.php              // Page d’accueil
Catalogue.php            // Catalogue global
FicheManga.php           // Page détaillée pour chaque manga
CreationCompte.php       // Inscription
Connexion.php            // Connexion
Deconnexion.php          // Déconnexion
EspaceUtilisateur.php    // Gestion du catalogue personnel et interface admin
stylefinal.css           // Feuille de style principale

fonction/                
  fonctions.php          // Fonctions PHP (requêtes SQL, utilitaires)

parametrage/             
  param.php              // Paramètres de connexion à la base de données 

images/                  // Images utilisées par le site

document/                
  punkrecordsBDD.sql     // Script complet de la base de données phpMyAdmin (tables + données)

4. Installation en local
Prérequis :

-Serveur Apache (WAMP, XAMPP, ou autre)

-phpMyAdmin

Étapes :

-Télécharger le projet dans le dossier du serveur local, par exemple :
htdocs/catalogue-mangas/

-Importer la base de données :

-Ouvrir phpMyAdmin

-Créer la base punkrecords

-Importer le fichier punkrecordsBDD.sql


5. Utilisation

Lancer le site :
http://localhost/catalogue-mangas/index.php

Administrateur

-Identifiant : admin

-Mot de passe : secureAdminPass

Utilisateur

-Identifiant : user2

-Mot de passe : pass45678

6. Compétences développées

-Développement d’applications web dynamiques en PHP

-Conception et exploitation d’une base de données phpMyAdmin

-Gestion des utilisateurs, sessions et rôles

-Sécurisation d’un site (XSS, injections SQL)

-Structuration d’un projet web complet (PHP, HTML/CSS, SQL)

-Organisation d’assets (images, contenu statique)
