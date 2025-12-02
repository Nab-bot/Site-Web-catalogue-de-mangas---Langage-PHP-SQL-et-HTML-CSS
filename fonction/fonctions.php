<?php
//fichier contenant les fonctions utilisées dans le site et qu'on appellera sur les différentes pages


//dans chaque page php demandant de se connecter à la bdd, je devrai donc require_once fonctions.php puis appeler la fonction connecterBD qui est codée dans fonctions.php




//fonction de connexion à la base de données


function connecterBD()
{
    //on appellera cette fonction dans toutes les suivantes 
// qui vont nécéssiter de se connecter à la BDD

    //variable locale contenant un tableau associatif des p
    include("parametrage/param.php"); //j’appelle le fichier des paramètres DANS la fonction de connexion à la BDD car param contient les valeurs de $host, $dbname et aussi pour $user et $password



    try {
        // Connexion à la base de données
        $connex = new PDO(
            //ci-dessous, j'ai remplacé les valeurs de host, dbname, user et password par les variables contenant ces valeurs dans le fichier param.php,que j'appelle plsu tôt dans ce fichier, je n'aurai donc besoin que de modifier param.php si j'ai besoin de changer de BDD ou que je change le mot de passe ou autre
            "mysql:host=$host;dbname=$dbname;charset=$encodage",
            $user,
            $password
        );  //y a pas de password donc chaîne vide dans param.php, mais je le mets tout de même  par précaution
    } catch (PDOException $e) {
        // Si une exception est levée
        die('Erreur : ' . $e->getMessage());
        //j'ai repris cette fonction avec le tout fonctionne à la fin du poly mais je veux pas que ça s'affiche sur la pagen, donc je mets echo une chaine vide
    } finally {
        //echo "Tout fonctionne !!!";
        echo "";
    }
    return $connex; //on retourne l'objet PDO créé par la fonction pour se connecter à la BDD ailleurs
}


//V2 fonction connexion à la BDD
/*
function connecterBD()
{
    $config = require("param.php"); // $config est une variable contenant un tableau associaztif contenant les infos de connexion à la BDD
    try {
        $connex = new PDO(
            //ci-dessous, j'ai remplacé les valeurs de host, dbname, user et password par les variables contenant ces valeurs dans le fichier param.php,que j'appelle plsu tôt dans ce fichier, je n'aurai donc besoin que de modifier param.php si j'ai besoin de changer de BDD ou que je change le mot de passe ou autre
            "mysql:host=" . $config["host"] . ";dbname=" . $config["dbname"] . ";charset=" . $config["encodage"],
            $config["user"],
            $config["password"]
        );
    } catch (PDOException $e) {
        // Si une exception est levée
        die('Erreur : ' . $e->getMessage());
        //j'ai repris cette fonction avec le "tout fonctionne" à la fin du poly mais je veux pas que ça s'affiche sur la page, donc je mets echo une chaine vide
    } finally {
        //echo "Tout fonctionne !!!";
        echo "";
    }
    return $connex; //on retourne l'objet PDO créé par la fonction pour se connecter à la BDD ailleurs

}

*/



//fonction de recherche

//déclarer la fonction (function nom, etc...) --> en cours, pas finie !
function Recherche($motCle)
{
    //le motclé en paramètre est le mot clé saisi par un utilisateur dans la abrre de recherche, c'est l'argument que cette fonction va traiter
    //d'abord appeler la fonction de connexion à la BDD, comme je le ferai dans chaque focntion le demandant !
    $connex = connecterBD(); //je me connnecte à la BDD

    //je met le paramètre de connexion par référence çar
    $infosManga = ""; //création variable qui va recueillir les infos --> je devrais peut-être ne faire un tableau ?

    //le mot clé passé en paramètre est encadré par les % pour la requete sql, et on se prémunit des injections de code avec htmlspecialchars !
    //$motCle = "%" . htmlspecialchars($motCle) . "%"; --> inutile ici finalement, c'est utile pour affichage html, pas ici

    //préparer le mot clé pour la requete sql--> permettra de prendre en compte même ce qui ne colle pas exactement à ce qui est tapé, donc Naruto shippuden par exemple au lieu de naruto tout court sera détecté
    $motCle = "%" . $motCle . "%";


    //ci-dessous la requete de recherche par mot clé (le mot clé passé en argument de cette fonction)
    //on fait les jointures aux tables référenteielles pour chercher les infos de ces tables (ex : auteur, genre, etc...)
    $requete = $connex->prepare("SELECT m.*,a.nom, a.prenom, g.libelle_genre, t.libelle_type_manga, i.url_image
                                FROM manga m
                                JOIN auteur a ON m.id_auteur = a.id_auteur
                                JOIN genre g ON m.id_genre = g.id_genre
                                JOIN type_manga t ON m.id_type_manga = t.id_type_manga
                                LEFT JOIN image_manga i ON m.id_manga = i.id_manga 
                                WHERE m.titre LIKE :motCle
                                OR g.libelle_genre LIKE :motCle
                                OR t.libelle_type_manga LIKE :motCle
                                OR a.nom LIKE :motCle
                                OR a.prenom LIKE :motCle
                                 ");

    //après l'avoir préparée, on exécute la requete !

    //on utilise un mlarqueur nominatif donc tableau associatif (page 113 cours) --> oin récupère toutes les données de la table manga (et les tables jointes avec les JOIN) dont le mot cle tapé par l'utilisateur correspond aux champs cités dans la requete, donc
    //le titre, le genre, le type du manga ainsi que les noms et prénom de l'auteur
    $requete->execute(array("motCle" => $motCle));


    //BOUCLE WHILE V1 : affiche tout direct, pb avec le simages car plusieurs
    //hile ($donnees = $requete->fetch()) {
    //  $infosManga .= "<br />" . $donnees["titre"] . " " . $donnees['synopsis'] . " " . $donnees['nom'] . " " . $donnees['prenom']." ".$donnees["libelle_genre"]." ".$donnees["libelle_type_manga"];

    //nom et prenom car ce sont les champs de la table auteur, c'est ça que je voulais, nom et prenom de l'zuteur du manga
    //page 48 du poly pour le .=


    //V2 Boucle while pour gérer les images

    //création tableau regroupant les infos de la fiche manga
    $mangas = [];

    //on récupère seulement l'id de chaque manga
    while ($donnees = $requete->fetch()) {
        $id = $donnees["id_manga"];

        //on stocke ici les infos du manga et pas les images seulement si l'id du manga n'est pas déjà stocké, pour éviter les doublons et donc d'afficher autant de fois les infos du manga (auteur, titre , synopsis) que le nombre d'images
        if (isset($mangas[$id]) == false) { //si le manga n'est pas déjà dans le tableau, alors on affecte chaque champ
            $mangas[$id] = array(
                "titre" => $donnees["titre"],
                "synopsis" => $donnees["synopsis"],
                "nom" => $donnees["nom"],
                "prenom" => $donnees["prenom"],
                "genre" => $donnees["libelle_genre"],
                "type" => $donnees["libelle_type_manga"],
                "images" => array() // on prépare un tableau vide pour les images
            );
        }
        //si la requete sql trouve une url image, il l'ajoute au champ images du tableau des mangas, pour l'id spécifié, donc pour One Piece par exmeple, il va prendre chacune de ses infos, puis créer le tableau des images, qui est un champ du tableau manga et dans ce champ, dés qu'on trouve une image, on la stocke, si rien, on ne stocke rien
        if ($donnees["url_image"] != null) {
            $mangas[$id]["images"][] = $donnees["url_image"];
        }



    }
    return $mangas;
}



//fonction permettant d'obtenir tout le catalogue des mangas du site
function CatalogueManga()
{
    $connex = connecterBD();//on se connecte tout d'abord à la BDD

    //ci-dessous la requete permettant de récupérer l'id du manga, son titre et l'url de l'image à afficher dans le catalogue
    $requete = $connex->prepare("SELECT m.id_manga, m.titre, i.url_image
                                 FROM manga m
                                 LEFT JOIN image_manga i ON m.id_manga = i.id_manga
                                 GROUP BY m.id_manga");
    $requete->execute();


    //création d'un tableau de mangas, ceux qui seront affichés sur le catalogue
    $mangas = array();

    //on récupère le résultat de la requete sql et on range le tout dans un tableau de manga, chaque ayant 3 champs ici, son id, son titre et son image
    while ($donnees = $requete->fetch()) {
        $mangas[] = array(
            "id" => $donnees["id_manga"],
            "titre" => $donnees["titre"],
            "image" => $donnees["url_image"]
        );
    }
    //la focntion retourne le tableau complet
    return $mangas;
}

//fonction de connexion ci-dessous, elle permet de vérifier si un utilisateur existe dansd la BDD, 
// si oui, il se connecte, sinon la connexion échoue car compte inexistant
function ConnexionUtilisateur($login, $mdp)//la fonction attend un login et un mot de passe en arguments pour se conencter
{
    $connex = connecterBD();//se conencter à la BDD

    $requete = $connex->prepare("SELECT * FROM utilisateur WHERE identifiant = :login AND mot_de_passe = :mdp"); //requete sql pour chercher un utilisateru avec l'identifiant ET le mot de passe saisis
    $requete->execute(array("login" => $login, "mdp" => $mdp));//injecter les valeurs saisies par l'user dans la requete sql
    $utilisateur = $requete->fetch();

    //si un utilisateur a été trouvé
    if ($utilisateur != false) {
        session_start(); // lancer la session si pas encore démarrée


        //stocker les infos en session
        $_SESSION["num_utilisateur"] = $utilisateur["num_utilisateur"]; //on pourra utiliser le num utilisateur dans d'autres requetes si besoin
        $_SESSION["identifiant"] = $utilisateur["identifiant"]; //permettra d'afficher le nom de l'user par exemple (voir si d'autres usages)
        $_SESSION["type_utilisateur"] = $utilisateur["id_type_utilisateur"];//on saura avec ça si c'est un admin ou pas (admin c'est 1 et visiteur lambda c'est 2)

        //renvoie un booléen, soit true si la connexion marche
        return true;
    } else {
        //false si elle marche pas (car login et mot de passe pas trouvés dans la BDD)
        return false;
    }

}

//fonction de création des utilisateurs (par défaut c'est un non admin)
function CreerUtilisateur($login, $mdp, $mail)//La fonction attend donc le login, le mot de passe et le mail
{
    $connex = connecterBD(); // Connexion à la BDD

    // Vérifie si un utilisateur avec ce login ou ce mail existe déjà
    //on précise qu'on veut les lignes où le login OU le mail est déjà pris --> on pourra 
    // empecher d'avoir deux comptes avec le même identifiant ou mail --> garanti unicité du compte
    $verif = $connex->prepare("SELECT * FROM utilisateur WHERE identifiant = :login OR email = :email");

    //on injecte alors les valeurs saisies par l'utilisateur dans cette requete
    $verif->execute(array("login" => $login, "email" => $mail));

    //$deja vaudra true si la requete trouve un utilisateur, false sinon
    //si deja vaut true, deja contiendra alors un tableau assoiciatif avec les colonnes de la ligne trouvée
    $deja = $verif->fetch();

    //si un compte utilisateur déjà trouvé, alors la fonction renvoie un false, sinon on insert un nouvel utilsiateur et onb renvoie
    if ($deja != false) {
        return false; // Login ou mail déjà utilisé, on renvoie un false
    }

    // Insère le nouvel utilisateur, avec type = 2 (utilisateur simple, pas admin donc)
    // login : c'est l'identifiant
    //on prépare l'insert ce nouvel utilisateur avec ses infos, donc l'identifoant, le mot de passe, son mail et le type d'utilisateur, qui sera par défaut 2, donc utilisaeur simple)
    $insertion = $connex->prepare("INSERT INTO utilisateur (identifiant, mot_de_passe, email, id_type_utilisateur)
                                   VALUES (:login, :mdp, :email, 2)");
    //on exécute alors la requete avec les infos saisies pour créer le nuveau compte (login, mdp et mail) 
    $insertion->execute(array(
        "login" => $login,
        "mdp" => $mdp,
        "email" => $mail
    ));

    return true; //return un true, la création du compte a marché !
}


//---------------------------------Gestion du catalogue perso:----------------------------

//Ajouter un manga au catalogue perso : 

function ajouterAuCatalogue($idManga, $numUtilisateur)
{ //la fonction prend l'identifiant du manga ainsi que celui de l'utilisateur en argument


    $connex = connecterBD();//on commence par se connecter à la BDD

    // On prépare la requete pour vérifier s'il est déjà dans le catalogue perso
    $requete = $connex->prepare("SELECT * FROM catalogue_utilisateur WHERE num_utilisateur = :user AND id_manga = :manga");

    //dans ma requete, je mets les numéro d'utilisateur et de manga qui ont été mis en paramètres de la fonction
    $requete->execute(array("user" => $numUtilisateur, "manga" => $idManga));
    $dejaPerso = $requete->fetch(); //on stocke le résultat de la requete dans $dejaPerso








    //test du cas ou le manga est déjà trouvé dans le catalogue perso
    if ($dejaPerso !== false) { //ici on veut savoir si une ligne est trouvée
        return false; // Déjà présent dans le catalogue alors la fonction renvoie false, on n'ajoute rien et le return false arrête la focntion là
    }

    // Si la variable $dejaPerso vaut false, alors la manga n'est pas déjà dans le catalogue perso, on peut donc l'ajouter au catalogue

    //requete d'insertion du manga dans le catalogue perso de l'utilisateur
    $insert = $connex->prepare("INSERT INTO catalogue_utilisateur (num_utilisateur, id_manga) VALUES (:user, :manga)");

    //dans la requete on passe les paramètres de la foncbtion, donc l'identifiant de l'utilisateur et celui du manga
    $insert->execute(array("user" => $numUtilisateur, "manga" => $idManga));

    //enfin, on renvoie true car l'ajout au catalogue a bien été fait ! (je vais sans doute créer une condition dans l'ezspace utilisateur pour exploiter le résultat renvoyé par la fonction)
    return true;
}


//Supprimer un manga du catalogue perso : 

function SupprimerMangaPerso($id_manga, $num_utilisateur)
//la fonction prend en paramètres l'id du manga et le numéro d'utilisateur
{
    $connex = connecterBD(); //on se connecte à la BDD (Base De Données)

    //on prépare la requete sql permettant de supprimer un manga donné dans la table lecture pour un utilisateur donné
    $requete = $connex->prepare("DELETE FROM catalogue_utilisateur WHERE id_manga = :id AND num_utilisateur = :num");

    //on exécute la requete avec le contenu des variables passées en paramètres, qui permettent di'dentifier respectivement le manga et le lecteur du manga
    $requete->execute(array("id" => $id_manga, "num" => $num_utilisateur));

    //enfin, une fois l'action effectuée sur la BDD, on peut fermer la connexion
    $connex = null;
}

//Mettre à jour le chapitre lu sur un manga du catalogue perso d'un utilisateur (on aura une liste déroulante je pense et le dernier item sera le dernier chapitre paru) : 

function mettreAJourChapitreLu($id_manga, $id_chapitre, $num_utilisateur)
//la fonction prend en arguments l'id du manga, celui du chapitre et le numéro de l'utilisateur
{
    $connex = connecterBD();//connexion à la BDD

    // Requete pour Vérifie si une entrée existe déjà dans la table lecture pour un utilisateur donné concernant un manga donné
    $req = $connex->prepare("SELECT * FROM lecture WHERE num_utilisateur = :num AND id_manga = :manga");

    //on insère les numéros d'utilisateur et id de manga dans la requete 
    $req->execute(array("num" => $num_utilisateur, "manga" => $id_manga));

    $ligne = $req->fetch(); // fetch récupère la ligne ou false si rien

    //contrôle du résultat de la requete                                                                                                                                                                                                                                                       
    if ($ligne) {
        // S’il y a déjà une entrée, on prépare la requete pour mettre à jour avec update
        $maj = $connex->prepare("UPDATE lecture SET id_chapitre = :chap, date_lecture = NOW() WHERE num_utilisateur = :num AND id_manga = :manga");
    } else {
        // Sinon, la requete pour insérer une nouvelle lecture (statut 1 car on estime alors que l'utilisateur démarre sa lecture, donc elle sera en cours et pas finie)
        $maj = $connex->prepare("INSERT INTO lecture (num_utilisateur, id_manga, id_chapitre, id_statut_lecture, date_lecture) VALUES (:num, :manga, :chap, 1, NOW())");
    }

    // On exécute la requête (dans les 2 cas on aura un tableau conbteant le num utilisateur, l'id du manga et le numéro de chapitre))
    $maj->execute(array("num" => $num_utilisateur, "manga" => $id_manga, "chap" => $id_chapitre));

    $connex = null; // fermeture de la connexion
}


//fonction permettant d'avoir la liste des chapitres

function getChapitresPourManga($id_manga)
//la fonction prend en argument le manga, on veut la liste des chapitres du manga ne question
{
    $connex = connecterBD();//connexion à la BDD

    //préparer la requete permettant  d'obtenir le numéro de chapitre par ordre croissant
    $req = $connex->prepare("SELECT id_chapitre, numero_chapitre FROM chapitre WHERE id_manga = :id ORDER BY numero_chapitre ASC");

    //exécuter la requete avec l'id du manga en paramètre
    $req->execute(array("id" => $id_manga));

    //créer le tableau des chapitres du manga qui va stocker les résultats récupérés grâce à la requete
    $chapitres = array();

    //boucle de récupération de chaque chapitre pour le manga passé en paramètre
    while ($ligne = $req->fetch()) { // tant qu’il y a une ligne à lire, la requete continue de les chercher
        $chapitres[] = $ligne; // on ajoute la ligne (qui est un chapitre) au tableau des chapîtres du manga dont l'id est passé en paramètre
    }

    //déconnexion de la BDD
    $connex = null;

    //retour de la fonction, renvoie le tableau des chapitres pour un manga donné, celui passé en paramètre
    return $chapitres;
}



//fonction de changement de mot de passe


function changerMotDePasse($num_utilisateur, $ancien, $nouveau)
//fonction prenant en paramètre le numéro d'utilisateur, l'ancien mot de passe et le nouveau saisi par l'utilisateur
{
    $connex = connecterBD();//connexion à la BDD

    // Requete pour récupérer le mot de passe actuel d'un utlisateur donné
    $req = $connex->prepare("SELECT mot_de_passe FROM utilisateur WHERE num_utilisateur = :id");

    //exécution de la requete pour l'id qui correspond au numéro d'utilisateur
    $req->execute(array("id" => $num_utilisateur));

    //on récupère le résultat de la reqeute dans une variable, donc le moty de passe actuel
    $motPasseActuel = $req->fetch();

    //contrôle que l'ancien mot de passe saisi est bon, sécurité évitant que quelqu'un change le mot de passe d'un utilisateur sans connaitre l'ancien
    if ($motPasseActuel && $motPasseActuel["mot_de_passe"] == $ancien) { // si le mot de passe correspond bien à l'ancien

        //on prépare la requete de mise à jour de mot de passe
        $majMotPasse = $connex->prepare("UPDATE utilisateur SET mot_de_passe = :new WHERE num_utilisateur = :id");

        //on exécute la requete avec les bonnes infos, donc le numério d'utilisateur et le nouveau mot de passe
        $majMotPasse->execute(array("new" => $nouveau, "id" => $num_utilisateur));

        //on se déconnecte de la BDD
        $connex = null;

        //renvoie true pour confirmer que le changement a réussi (il faudra prévoir un message de confirmation)
        return true;
    }

    //déconnexion de la BDD aussi, mais cas où la mise à jour a échoué car le mot de passe saisi ne correspond pas à l'ancien
    $connex = null;

    //renvoie false ppour dire que ça a échoué, idem prévoir un message
    return false;
}



//----------------------Gestion des utilisateurs, ce sont des fonctions d'admin :--------------- 

//Passer un visiteur en administrateur

function promouvoirUtilisateur($num_utilisateur)
{
    //la fonction prend en paramètre le numéro de l'utilisateur

    $connex = connecterBD();//connexion à la BDD

    //préparation requete de pour passer le type d'utilisateur à admin, qui est le type 1
    $req = $connex->prepare("UPDATE utilisateur SET id_type_utilisateur = 1 WHERE num_utilisateur = :id");

    //exécuter la requete avec le bon id utilisateur, celui passé en paramètre de la fonction et vérifier si ça a marché !
    $PromotionOK = $req->execute(array("id" => $num_utilisateur));

    //déconnexion à la BDD
    $connex = null;

    //renvoyer le résultat de la requete --> true si c'est passé et false sinon
    return $PromotionOK;
}

//passer un admin en visiteur (même logique avec requete différente)

function retrograderUtilisateur($num_utilisateur)
{
    $connex = connecterBD(); //connexion à la BDD

    //préparer reqeute pour passer un admin au statut d'utilisateur
    $req = $connex->prepare("UPDATE utilisateur SET id_type_utilisateur = 2 WHERE num_utilisateur = :id");

    //exécuter la requete avec le bon numéro d'utilisateur, celui passé en paramètre
    $RetroOK = $req->execute(array("id" => $num_utilisateur));

    //déconnexion de la BDD
    $connex = null;

    //retourner true si la rétrogradation a fonctionné et false sinon
    return $RetroOK;
}


//supprimer un compte (utilisateur ou admin, on part du principe qu'un admin peut agir sur un autre, ce qui inclut la suppression de son compte)

function supprimerUtilisateur($num_utilisateur)
//prend en argument le numéro de l'utilisateur
{
    $connex = connecterBD(); //connexion à la BDD

    //préparer la requete de suppression d'un utilisateur qu'il soit admin ou visiteur
    $req = $connex->prepare("DELETE FROM utilisateur WHERE num_utilisateur = :id");

    //exécuter la requete avec el numéro d'utilisateur passé en paramètre
    $SuppressionOk = $req->execute(array("id" => $num_utilisateur));

    //déconnexion de la BDD
    $connex = null;

    //renvoyer true si la suppresion à marché et false sinon
    return $SuppressionOk;
}



//fonction pour obtenir la liste des utilisateur

function getTousUtilisateurs($user_exclu)
//on exclut le numéro de l'utilisateur qui appelle la fonction, il veut voir les autres utilisateurs
{
    $connex = connecterBD(); // Connexion à la BDD

    // Préparation de la requête : on exclut l'utilisateur connecté et on récupère les autres
    $req = $connex->prepare("SELECT * FROM utilisateur WHERE num_utilisateur != :id");

    //exécutuion de la requete avec le bon numéro d'utilisateur, celui passé en paramètre
    $req->execute(array("id" => $user_exclu));

    // Création d’un tableau pour stocker les utilisateurs
    $utilisateurs = array();

    // On ajoute chaque utilisateur ligne par ligne dans le tableau qui va tous les contenir
    while ($ligne = $req->fetch()) {
        $utilisateurs[] = $ligne;
    }

    $connex = null; // Déconnexion de la BDD

    return $utilisateurs; // On renvoie le tableau rempli
}




//------------------Gestion Catalogue du site global-------------------------

//ajouter un manga au catalogue du site

// Fonction permettant à un administrateur d'ajouter un manga au catalogue global du site
function ajouterManga($titre, $synopsis, $date_debut, $date_fin, $nb_chapitres, $nb_volumes, $id_statut, $id_editeur, $id_auteur, $id_genre, $id_type, $liste_images = [])
// Tous les paramètres nécessaires sont passés à la fonction : titre, synopsis, dates, stats, et les id des tables référentielles + un tableau optionnel pour les images
//La fonction renvoie un booléen permettant de savoir si l'ajout au catalogue a marché ou pas
{
    $connex = connecterBD(); // connexion à la BDD

    // Préparation de la requête pour vérifier si un manga portant le même titre et le même auteur existe déjà
    $verifDejaPresent = $connex->prepare("SELECT * FROM manga WHERE titre = :titre AND id_auteur = :auteur");

    // On exécute cette requête en injectant les valeurs de titre et auteur passées en paramètre dans la fonction
    $verifDejaPresent->execute(array("titre" => $titre, "auteur" => $id_auteur));


    $dejaPresent = $verifDejaPresent->fetch(); // On tente de récupérer une ligne correspondante, ou false si rien n'est trouvé

    // Si une ligne a été trouvée, ça veut dire que le manga est déjà dans le catalogue
    if ($dejaPresent !== false) {
        $connex = null; // Déconnexion de la BDD
        return false;   // On retourne false pour signaler que l'ajout n'a pas été fait (car le manga était déjà dans le catalogue) et on arrête la fonction avec le return, sinon on passe ce if et on procède bien à l'ajout
    }

    // Préparation de la requête d'insertion du nouveau manga dans la table manga
    $ajoutManga = $connex->prepare("INSERT INTO manga (titre, synopsis, date_debut_publication, date_fin_publication,
                                                  nb_chapitres_parus, nb_volumes_parus,
                                                  id_statut_manga, id_editeur, id_auteur, id_genre, id_type_manga)
                               VALUES (:titre, :synopsis, :debut, :fin, :chapitres, :volumes,
                                       :statut, :editeur, :auteur, :genre, :type)");

    // Exécution de la requête avec toutes les infos nécessaires
    $AjoutMangaOk = $ajoutManga->execute(array(
        "titre" => $titre,
        "synopsis" => $synopsis,
        "debut" => $date_debut,
        "fin" => $date_fin,
        "chapitres" => $nb_chapitres,
        "volumes" => $nb_volumes,
        "statut" => $id_statut,
        "editeur" => $id_editeur,
        "auteur" => $id_auteur,
        "genre" => $id_genre,
        "type" => $id_type
    ));

    // Si l'ajout a échoué, on arrête ici et on renvoie false
    if ($AjoutMangaOk === false) {
        $connex = null;//déconnexion de la BDD
        return false;//renvoyer false car ajout du manga échoué !
    }

    // On récupère l'id du manga qu'on vient d'insérer pour insérer les images désormais 
    $recupNewManga = $connex->prepare("SELECT id_manga FROM manga WHERE titre = :titre AND id_auteur = :auteur ORDER BY id_manga DESC");

    //on exécute la requete avec les titres et auteurs du manga qu'on vient d'ajouter, car c'est pour lui qu'on veut ajouter les images
    $recupNewManga->execute(array("titre" => $titre, "auteur" => $id_auteur));

    $ligne = $recupNewManga->fetch(); // On récupère la ligne contenant l'identifiant du manga
    if ($ligne === false) {
        $connex = null;
        return false; // par sécurité, si jamais rien n'est trouvé on renvoie false
    }

    $id_manga = $ligne["id_manga"]; // On stocke l'identifiant du manga pour l'utiliser dans la table des images

    // Si des url d'images ont été passées à la fonction, on les insère une par une dans la table image_manga
    foreach ($liste_images as $urlImage) {
        //Préparation de la requete pour parcourir la liste des images pour avoir les url de chaque image
        $image = $connex->prepare("INSERT INTO image_manga (id_manga, url_image) VALUES (:id, :url)");

        //exécuter la requete avec l'id du manga correspondant et l'url de l'image
        $image->execute(array("id" => $id_manga, "url" => $urlImage));
    }

    $connex = null; // On ferme la connexion à la base

    return true; // On retourne true pour dire que l’ajout a bien été fait
}






//modifier un manga existant

//supprimer un manga du site



//--------------------Changement de paramètres------------------------


//passer le site en thème sombre ou clair
//il faudra pouvoir appeler cette fonction partout sur le site, donc l'appeler sur chaque page

function changerTheme()
{
    // Vérifie si l'utilisateur a cliqué sur le bouton ayant un champ name "changer_theme"
    if (isset($_POST["changer_theme"])) {

        // On récupère le thème demandé (valeur transmise par le bouton)
        $nouveauTheme = $_POST["changer_theme"];

        // Vérifie que la valeur est bien "clair" ou "sombre"
        if ($nouveauTheme === "clair" || $nouveauTheme === "sombre") {

            // On stocke cette valeur dans la session pour la réutiliser sur tout le site sans avoir à le refaire à chaque page
            $_SESSION["theme"] = $nouveauTheme;
        }
    }

    // Si aucun thème n’est encore défini (ex : 1ère visite), on met "clair" par défaut
    if (isset($_SESSION["theme"]) == false) {
        $_SESSION["theme"] = "clair";
    }
}


//passer le site en anglais --> on verra, pas sur du tout





?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>


</body>

</html>