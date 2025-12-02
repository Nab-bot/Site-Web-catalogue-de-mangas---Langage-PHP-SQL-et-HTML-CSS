<?php
//Cette page aura plusieurs usages, elle servira aux utilisateurs simples et aux administrateurs, le contenu de la page changera selon le type d'utilisateur.
//les administrateurs auront accès à certaines fonctions et les utilisateurs à d'autres.


session_start(); //démarrer la session

$confirmationSuppressionPerso = false; //booléen indiquant si on doit afficher ou pas un message de suppresion d'un manga du catalogue perso d'un utilisateur, à false de base car on n esupprime rien de base

//on utilise GET ici car pas de risque de fuite de donnée,s on ne passe pas d'infos sensibles par l'url dans ce cas
//ici on vérifie si l'url contient l'indication qu'une suppression a eu lieu, on le sait su "suppr" est dans l'url
//ici on vérifie que l'url comporte "ok" comme message de controle de suppression de manga du catalogue perso
if (isset($_GET["suppr"]) && $_GET["suppr"] === "ok") {
    $confirmationSuppressionPerso = true;//le booléen est alors à true, on va bien afficher le message de confirmation de suppression
}



include("fonction/fonctions.php"); //on appelle les fonctions pour notamment gérer le thème du site (clair ou sombre)

changerTheme(); // gère le thème clair/sombre, on l'appelle dés le début de la page pour traiter cette info avant tout, elle est récupéré avec la méthode POST et stockée en session, il faut donc l'appeler juste après le session start


//bloc permettant de controler si le bouton de suppression pour un manga est cliqué, ET si l'id du manga est passé via la méthode poste (c'est en value du bouton de suppression manga ?) ET on controle aussi si l'utilisateur est connecté, si ces 3 éléments sont contrôlés, alors on entre dans le if

// Appel de la fonction pour supprimer ce manga du catalogue personnel de l'utilisateur connecté
//si le bouton de suppression est pressé, que l'id manga est envoyé via la méthode post et que l'utilisateru est connecté, on entre dans ce if
if (isset($_POST["supprimer_manga"]) && isset($_POST["id_manga"]) && isset($_SESSION["num_utilisateur"])) {

    //appel de la fonction en prenant en arguments l'id du manga (on le récupère via un champ caché du formulaire) et le numéro d'utilisateur gardé en session car connecté
    SupprimerMangaPerso($_POST["id_manga"], $_SESSION["num_utilisateur"]);

    // Puis une fois la suppression du manga du catalogue perso (qu'on voit dans l'espace utilisateur, on n'a pas créé de page catalogue perso à part) faite, on peut rediriger vers la page Catalogue
    header("Location: EspaceUtilisateur.php?suppr=ok"); //on renvoie vers la même page mais avec une info dans l'url qui permettra de savoir que la suppression a bien été faite
    exit; //on arrete le déroulement des instructions
}


//test pour voir si le thème en session est clair ou sombre pour savoir lequel appliquer
if ($_SESSION["theme"] == "clair") {
    $valeurTheme = "sombre";    //si le thème en session est clair, alors on proposera d'afficher le thèlme sombre
    $texteBouton = "Thème : Clair";  //permettra d'indiquer le thème actif actuellement sur le site, c'est le thème en session
} else {
    //ci-dessous la logique est la même mais dans le cas où e thème est sombre et ou on propose clair
    $valeurTheme = "clair";
    $texteBouton = "Thème : Sombre";
}

//logique d'insertion au catalogue perso d'un utilisateur, ce bloc est déclenché par le clic sur un bouton dans le catalogue global : 
// Si un ajout est demandé depuis le catalogue global, qui comportera un bouton d'ajout par manga, alors c'est que l'utilisateur a cliqué sur ce bouton d'ajout et donc on exécute ce bloc de code ci-dessous !
if (isset($_POST["ajouter_manga"])) {
    //si l'utilisateur a cliqué sur le bouton d'ajout de manga au catalogue perso

    $idMangaAAjouter = $_POST["ajouter_manga"]; //on stocke l'id du manga, qu'on va obtenir en le mettant dans le bouton d'ajout d'un manga en particulier, chacun aura le sien avec dans le champ name l'identifiant du manga associé, ainsi, le bouton d'ajout du manga onerpiece aura pour value l'id du manga one piece
    $numUtilisateur = $_SESSION["num_utilisateur"]; //le numéro de l'utilisateur connecté qui a été récupéré en session

    // Appel de la fonction d'ajout au catalogue, si elle renvoie true, alors on a bien fait l'ajout, sinon le manga est déjà dedans et donc pas d'ajout, renvoie false
    $ajoutOk = ajouterAuCatalogue($idMangaAAjouter, $numUtilisateur);

    // Stocker un message dans une variable 
    if ($ajoutOk) {
        //comme dit plus haut, si $ajoutOK vaut true, on affichera un message de confirmation
        $messageAjout = "Manga ajouté avec succès dans votre catalogue personnel.";
    } else {
        //autrement, ce sera un message disant que l'ajout a échoué car déjà dans le catalogue perso
        $messageAjout = "Ce manga est déjà dans votre catalogue personnel.";
    }
}



// Si l'utilisateur est connecté, on continue, sinon il est redirigé vers la page de connexion
if (isset($_SESSION["num_utilisateur"])) {

    //création du tableau des mangas perso
    $mangasPerso = array(); // on initialise toujours le tableau, même si l'utilisateur est admin, pour éviter les erreurs avec count()


    // Connexion à la base de données et récupération des infos de l'utilisateur connecté
    $connex = connecterBD();//connexion à la BDD
    //ci-dessous la requete va prendre toutes les colonnes de la table utilisateurs pour un id donné, donc pour un utilisateur donné
    //prepare pour éviter les injections sql
    $requete = $connex->prepare("SELECT * FROM utilisateur WHERE num_utilisateur = :id");

    //on exécute la reqeute en replaçant id par le numéro d'utilisateru qu'on a récupéré dans la session
    $requete->execute(array("id" => $_SESSION["num_utilisateur"]));

    //ci-dessous on récupère la ligne
    $utilisateur = $requete->fetch();

    //ici on ajoute un bloc qui va permettre directemment sur l apage utilisateur, si ce n'est pas un admin, d'avoir accès à son catalogue directement dans son espace utilisateur plutot que de devoir ensuite cliquer sur un bouton supplémentaire pour y accéder, c'est une façon de rendre l'expérience utilisateur plus fluide quand on y pense (le bon terme c'est UX je crois)
    // Si l'utilisateur est un visiteur (type = 2), on récupère ses mangas personnels avec la requete sql
    if ($utilisateur["id_type_utilisateur"] == 2) {
        //ci-dessous on prépare la requete sql qui va récupérer le catalogue de mangas pour un utilisateru donné, identifié par son numéro utilisateur

        //requete V2 ci-dessous
        $requete = $connex->prepare(" SELECT m.id_manga, m.titre, m.synopsis, 
                                                COALESCE(MAX(c.numero_chapitre), 0) AS dernier_chapitre_lu,
                                                (SELECT i.url_image FROM image_manga i WHERE i.id_manga = m.id_manga LIMIT 1) AS image
                                            FROM catalogue_utilisateur cu
                                            JOIN manga m ON cu.id_manga = m.id_manga
                                            LEFT JOIN lecture l ON m.id_manga = l.id_manga AND l.num_utilisateur = :id
                                            LEFT JOIN chapitre c ON l.id_chapitre = c.id_chapitre
                                            WHERE cu.num_utilisateur = :id
                                            GROUP BY m.id_manga, m.titre, m.synopsis
                                        ");




        //requete V1
        /*("SELECT m.id_manga, m.titre, m.synopsis, MAX(c.numero_chapitre) AS dernier_chapitre_lu,
                                    (SELECT i.url_image FROM image_manga i WHERE i.id_manga = m.id_manga LIMIT 1) AS image
                                    FROM manga m
                                    JOIN lecture l ON m.id_manga = l.id_manga
                                    JOIN chapitre c ON l.id_chapitre = c.id_chapitre
                                    WHERE l.num_utilisateur = :id
                                    GROUP BY m.id_manga, m.titre, m.synopsis
                                    ");*/

        //ci-dessous on rempalce id dans la requete par le numéro utilisateur récupéré dans le tableau de session, qui indique l'utilisateru actuellemebnt connecté
        $requete->execute(array("id" => $_SESSION["num_utilisateur"]));

        //on créé le tableau de manga perso, donc le catalogue perso d'un visiteur et on le rempli ensuite
        $mangasPerso = [];

        //tant que la requete trouve des données, elle les récupère et les stocke dans le tableau créé juste avant, chaque ligne est une entrée pour un manga donné du cataloguie de l'utlisateur 
        while ($ligne = $requete->fetch()) {
            $mangasPerso[] = $ligne;
        }
    }


} else {
    // Redirection vers la page de connexion si aucun utilisateur n'est connecté
    header("Location: Connexion.php");
    exit;
}


// le bloc ci-dessous servira au chagement de mot de passe

$messageMotDePasse = ""; // message pour informer l'utilisateur du changement de mot de passe (s'il a réusssi ou échoué)

// Si le formulaire de changement de mot de passe est soumis, donc si les champs pour l'ancien ET le nouveau mot de passe sont remplis et que l'utilisateur est connecté, les conditions sont remplies
if (isset($_POST["ancien"]) && isset($_POST["nouveau"]) && isset($_SESSION["num_utilisateur"])) {

    //la variable va recueillir soit true si le changement a réussi, soit fals esi ça a échoué et on l'affichera plus bas
    $ChangementMotDePasse = changerMotDePasse($_SESSION["num_utilisateur"], $_POST["ancien"], $_POST["nouveau"]);
    if ($ChangementMotDePasse) {
        $messageMotDePasse = "succès";
    } else {
        $messageMotDePasse = "erreur";
    }
}



// Bloc de mise à jour du chapitre lu
$messageMajChapitre = null; //le message indiquant la mise à jour du dernier chapitre lu est null par défaut

//si le bouton de mise à jour du chapitre est cliqué ET que l'id d'un chapitre est choisi (via une liste déroulante) ET que l'utilisateur est connecté
if (isset($_POST["maj_chapitre"]) && isset($_POST["id_manga"]) && isset($_POST["id_chapitre"]) && isset($_SESSION["num_utilisateur"])) {

    //si les conditions sont remplies, on appelle la fonction de mise à jour du dernier chapitre lu avec les paramtres récupérés via la méthode post du formulaire
    mettreAJourChapitreLu($_POST["id_manga"], $_POST["id_chapitre"], $_SESSION["num_utilisateur"]);

    //et la variable de message reçoit une chaine de caractères qui va confirmer la mise à jour
    $messageMajChapitre = "Le chapitre lu a bien été mis à jour.";


    //ajout d'une requete pour mettre à jour le tableau des mangas du catalogue de l'utilisateur pour que la mise à jour se fasse dés que l'utilisateur a cliqué sur le chapitre de son choix puis sur le bouton de mise à jour, une fois fait, ça affichait le résultat précédent et un second clic était nécessaire, le passage qui suit permet d'éviter cela 

    //préparer requete pour récupérer toutes les informations qui seront affichées dans l'espace utlisateur concernant le manga 
    $requete = $connex->prepare(" SELECT m.id_manga, m.titre, m.synopsis, 
                                        COALESCE(MAX(c.numero_chapitre), 0) AS dernier_chapitre_lu,
                                        (SELECT i.url_image FROM image_manga i WHERE i.id_manga = m.id_manga LIMIT 1) AS image
                                    FROM catalogue_utilisateur cu
                                    JOIN manga m ON cu.id_manga = m.id_manga
                                    LEFT JOIN lecture l ON m.id_manga = l.id_manga AND l.num_utilisateur = :id
                                    LEFT JOIN chapitre c ON l.id_chapitre = c.id_chapitre
                                    WHERE cu.num_utilisateur = :id
                                    GROUP BY m.id_manga, m.titre, m.synopsis
                                ");

    //exécution de la requete avec le numéro de l'utilisateur connecté
    $requete->execute(array("id" => $_SESSION["num_utilisateur"]));

    //création du tableau manga des informations qui vont apparaitre sur l'espace utilisateur concernant le manga, dont le dernier chapitre lu
    $mangasPerso = [];

    //enfin, on récupère chaque ligne renvoyées par la rzquete et on les stocke dans le tableau créé jsute avant pour en afficher le contenu après
    while ($ligne = $requete->fetch()) {
        $mangasPerso[] = $ligne;
    }

}

//bloc de gestion de la logique des fonctions de gestion des utilisateurs du site (promotion, rétrograder et suppression d'un compte)

$messageActionAdmin = "";//message permettant de faire un retour à l'admin lui disant si l'action effectuée à fonctionné ou pas

//si un admin est connecté, on remplit ces tableaux pour les afficher plus tard dans les lsites déroulantes
if ($utilisateur["id_type_utilisateur"] == 1) {
    //logique php pour la gestion du catalogue global du site pour l'admin, donc c'est dans le bloc si le type d'utilisateur est 1, donc admin

    $connex = connecterBD();// Connexion à la BDD

    // Création des tableaux qu'on va utiliser pour les select plus tard
    $genres = array();
    $statuts = array();
    $editeurs = array();
    $auteurs = array();
    $types = array();

    // Préparation et execution des requêtes SQL pour chaque table référentielle contenant des infos liées aux manga (le statut, le genre, le type, etc...)
    $reqGenres = $connex->prepare("SELECT id_genre, libelle_genre FROM genre");
    //exécution de la requete pour stocker l'enesmble des genres dans le tableau des genre qu'on a créé ci-dessus
    $reqGenres->execute();
    while ($ligne = $reqGenres->fetch()) {
        $genres[] = $ligne;
    }

    //la logique ic sera la même mais pour le statut,et idem pour la suite pour les éditerurs, les auteurs et les types de manga
    $reqStatuts = $connex->prepare("SELECT id_statut_manga, libelle_statut_manga FROM statut_manga");
    $reqStatuts->execute();
    while ($ligne = $reqStatuts->fetch()) {
        $statuts[] = $ligne;
    }

    $reqEditeurs = $connex->prepare("SELECT id_editeur, nom_editeur FROM editeur");
    $reqEditeurs->execute();
    while ($ligne = $reqEditeurs->fetch()) {
        $editeurs[] = $ligne;
    }

    $reqAuteurs = $connex->prepare("SELECT id_auteur, nom, prenom FROM auteur");
    $reqAuteurs->execute();
    while ($ligne = $reqAuteurs->fetch()) {
        $auteurs[] = $ligne;
    }

    $reqTypes = $connex->prepare("SELECT id_type_manga, libelle_type_manga FROM type_manga");
    $reqTypes->execute();
    while ($ligne = $reqTypes->fetch()) {
        $types[] = $ligne;
    }

    $connex = null;//déconnexion de la BDD

    //on a récupéré les listes depuis les tables référentielles liées aux mangas, maintenant on va les traiter pour préparer le formulaire à suivre

    // Si le formulaire a été soumis, donc si l'admin az cliqué sur le bouton d'ajout de manga au catalogue
    if (isset($_POST["ajouter_manga_site"])) {

        // Vérifier que tous les champs obligatoires sont bien remplis (on utilise la méthode POST avec le formulaire ne bas de page permettant l'ajout d'un manga), sinon on aurait une fiche manga sans l'auteur, ou le titre ou le synopsis par exemple
        if (
            isset($_POST["titre"]) && $_POST["titre"] != "" &&
            isset($_POST["synopsis"]) && $_POST["synopsis"] != "" &&

            //auteur : on peut le choisir dans le Liste déroulante OU en ajouter un via le formulaire
/*
            (isset($_POST["id_auteur"]) && $_POST["id_auteur"] !== "") ||
            (isset($_POST["nouveau_prenom_auteur"]) && $_POST["nouveau_prenom_auteur"] !== "" &&
                isset($_POST["nouveau_nom_auteur"]) && $_POST["nouveau_nom_auteur"] !== "") &&
*/
            isset($_POST["id_genre"]) && $_POST["id_genre"] != "" &&
            isset($_POST["id_type"]) && $_POST["id_type"] != "" &&
            isset($_POST["id_statut"]) && $_POST["id_statut"] != "" &&
            isset($_POST["id_editeur"]) && $_POST["id_editeur"] != ""
        ) {
            //traitement du tableau des images, les 4 images pour un manga doivent être saisies, enfin leur url car les images en elles-mêrmes sont stockées dans un dossier sur le site, donc on indique les url ici
            $images = array();
            if (isset($_POST["image1"]) && $_POST["image1"] != "")
                $images[] = $_POST["image1"];
            if (isset($_POST["image2"]) && $_POST["image2"] != "")
                $images[] = $_POST["image2"];
            if (isset($_POST["image3"]) && $_POST["image3"] != "")
                $images[] = $_POST["image3"];
            if (isset($_POST["image4"]) && $_POST["image4"] != "")
                $images[] = $_POST["image4"];
            /*
                        //ajouter un auteur 
                        if (isset($_POST["nouvel_auteur"]) && $_POST["nouvel_auteur"] !== "") {
                            $nouvelAuteur = $_POST["nouvel_auteur"];

                            $req = $connex->prepare("SELECT id_auteur FROM auteur WHERE CONCAT(prenom, ' ', nom) = ?");
                            $req->execute([$nouvelAuteur]);
                            $res = $req->fetch();

                            if ($res) {
                                $id_auteur = $res["id_auteur"];
                            } else {
                                $req = $connex->prepare("INSERT INTO auteur (prenom, nom) VALUES (?, '')");
                                $req->execute([$nouvelAuteur]);
                                $req = $connex->prepare("SELECT id_auteur FROM auteur WHERE prenom = ? AND nom = ''");
                                $req->execute([$nouvelAuteur]);
                                $id_auteur = $req->fetch()["id_auteur"];
                            }
                        } else {
                            $id_auteur = $_POST["id_auteur"];
                        }
            */
            //bloc d'ajout des infos en plus de celles des listes déroulantes

            //ajouter un auteur
            $connex = connecterBD(); //connexion a la BDD


            //Si l'utilisateur a rempli le champ nouveau nom
            if (isset($_POST["nouvel_auteur"]) && $_POST["nouvel_auteur"] !== "") {

                //on récupère le nouvel auteur saisi
                $nouvelAuteur = $_POST["nouvel_auteur"];

                //on prépare la requete pour essayer de trouver si l'auter est déjà dans la BDD
                $NomAuteurPresent = $connex->prepare("SELECT id_auteur FROM auteur WHERE nom = :nom");

                //on l'exécute avec le nom saisi pour savoir si deja dans la base
                $NomAuteurPresent->execute(["nom" => $nouvelAuteur]);

                //on récupère le résultat dans la variable res
                $res = $NomAuteurPresent->fetch();

                //controle si le nom est déjà présent
                if ($res) {
                    $id_auteur = $res["id_auteur"];
                } else
                //sinon, on insert le nouvel auteur dans la base
                {
                    $insert = $connex->prepare("INSERT INTO auteur (nom) VALUES (:nom)");
                    $insert->execute(["nom" => $nouvelAuteur]);
                    $req = $connex->prepare("SELECT id_auteur FROM auteur WHERE nom = :nom");
                    $req->execute(["nom" => $nouvelAuteur]);
                    $id_auteur = $req->fetch()["id_auteur"];
                }
            } else {
                $id_auteur = $_POST["id_auteur"];
            }




            //test pour ajout auteur ça marche !

            $connex = connecterBD(); // Connexion à la BDD

            // on vérifie si le nom et le prénom sont saisis !
            if (
                isset($_POST["nouveau_prenom_auteur"], $_POST["nouveau_nom_auteur"]) &&
                $_POST["nouveau_prenom_auteur"] !== "" &&
                $_POST["nouveau_nom_auteur"] !== ""
            ) {
                //on stocke les noms et prénoms
                $prenomAuteur = $_POST["nouveau_prenom_auteur"];
                $nomAuteur = $_POST["nouveau_nom_auteur"];

                // requete pour vérifier si l’auteur existe déjà
                $req = $connex->prepare("SELECT id_auteur FROM auteur WHERE nom = :nom AND prenom = :prenom");

                //exécuter la requete avec les noms et prénoms saisis
                $req->execute(["nom" => $nomAuteur, "prenom" => $prenomAuteur]);

                //on stocke le résultat de l arequete dans la variable
                $res = $req->fetch();

                //si l'auteur existe deja, on affecte la variable id auteur du resultat
                if ($res) {
                    $id_auteur = $res["id_auteur"];
                } else {
                    // Sinon on l'insère dans la BDD
                    $insert = $connex->prepare("INSERT INTO auteur (nom, prenom) VALUES (:nom, :prenom)");
                    $insert->execute(["nom" => $nomAuteur, "prenom" => $prenomAuteur]);

                    // Et on récupère son id
                    $req = $connex->prepare("SELECT id_auteur FROM auteur WHERE nom = :nom AND prenom = :prenom");
                    $req->execute(["nom" => $nomAuteur, "prenom" => $prenomAuteur]);
                    $id_auteur = $req->fetch()["id_auteur"];
                }
            } else {
                // Sinon on utilise celui sélectionné dans la liste
                $id_auteur = $_POST["id_auteur"];
            }


            //appel de la fonction d'ajout d'un manga et on ne récupère le résultat dans la variables $ajoutMangaOk
            $ajoutMangaOk = ajouterManga(
                $_POST["titre"],
                $_POST["synopsis"],
                $_POST["date_debut"],
                $_POST["date_fin"],
                $_POST["nb_chapitres"],
                $_POST["nb_volumes"],
                $_POST["id_statut"],
                $_POST["id_editeur"],
                //$_POST["id_auteur"],
                $id_auteur,

                $_POST["id_genre"],
                $_POST["id_type"],
                $images
            );

            //test pour voir si l'ajout a fonctionné ou pas, on renverra un message différent selon le cas
            if ($ajoutMangaOk) {
                echo "<p style='color:green; font-weight:bold;'>Manga ajouté au catalogue avec succès.</p>";
            } else {
                echo "<p style='color:red; font-weight:bold;'>Erreur : Ce manga est déjà présent ou une erreur est survenue.</p>";
            }
        } else
        //cas où tous els champs n'ont pas été remplis
        {
            echo "<p style='color:red; font-weight:bold;'>Tous les champs obligatoires doivent être remplis.</p>";
        }
    }
}

//si l'utilisateur est connecté ET qu'on a choisi l'id de l'utilisateur dont on veut changer le ttype (admin ou utilisateur) ET que l'utilisateur a soumis le formulaire d'action
if (isset($_POST["action_admin"]) && isset($_POST["id_utilisateur_cible"]) && isset($_SESSION["num_utilisateur"])) {

    //récupérer l'id de l'utilisateur dont on change le type 
    $idCible = $_POST["id_utilisateur_cible"];

    //on récupère le type de l'action qu'on veut réaliser, dont si on rétrograde ou promeut ou supprime
    $action = $_POST["action_admin"];

    //si l'admin (type utilisateur 1) demande a promouvoir un utilisateur, alors on appelle la fonction adéquate en lui passant le numéro de l'utilisateur ciblé
    if ($utilisateur["id_type_utilisateur"] == 1) {



        if ($action == "promouvoir") {
            if (promouvoirUtilisateur($idCible)) {
                //cas où la fonction renvoie true, donc que l'utilisateur a bien été promu !
                $messageActionAdmin = "Utilisateur promu avec succès.";
            } else {
                //cas inverse
                $messageActionAdmin = "Erreur lors de la promotion.";
            }
            //ce bloc suit la même logique mais appelle la fonction pour rétrograder
        } elseif ($action == "retrograder") {
            if (retrograderUtilisateur($idCible)) {
                $messageActionAdmin = "Utilisateur rétrogradé avec succès.";
            } else {
                $messageActionAdmin = "Erreur lors de la rétrogradation.";
            }
            //idem pour la suppression
        } elseif ($action == "supprimer") {
            if (supprimerUtilisateur($idCible)) {
                $messageActionAdmin = "Utilisateur supprimé avec succès.";
            } else {
                $messageActionAdmin = "Erreur lors de la suppression.";
            }
        }



    }
}



?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
</head>

<body class="<?php echo $_SESSION['theme']; ?>">

    <?php include("header.php") ?>

    <!-- Affichage d'un message d'accueil et on utilise htmlspecialchars pour éviter la faille XSS -->
    <div class="Titre">
        <h2>Bienvenue, <?php echo htmlspecialchars($utilisateur["identifiant"]); ?> !</h2>
    </div>
    <div class="information_utilisateur">
        <p>Adresse e-mail : <?php echo htmlspecialchars($utilisateur["email"]); ?></p>
        <!-- Bloc de message confirmant si un manga a pu être ajouté ou pas au catalogue perso des utilisateurs -->
        <?php if (isset($messageAjout)) {
            echo "<p style='color:green; font-weight:bold;'>" . htmlspecialchars($messageAjout) . "</p>";
        } ?>

        <!-- Message affichant si le chapitre a bien été mis à jour -->
        <?php if ($messageMajChapitre != null) { //si $messageMajChapitre contient une valeur, on l'affiche !  ?>
            <p style="color: green; font-weight: bold;"><?php echo $messageMajChapitre; ?></p>
        <?php } ?>


        <!-- on affiche ceci si le type utilisateur vaut 1 donc si c'est un admin  -->
        <?php
        //si l'utilisateur connecté est un type 1, donc admin, ce sera son affichage
        if ($utilisateur["id_type_utilisateur"] == 1) {



            // Récupérer tous les utilisateurs sauf l’admin connecté via la fonction qu'on a créé
            $listeUtilisateurs = getTousUtilisateurs($_SESSION["num_utilisateur"]);

            echo "<p>Voici la liste des utilisateurs du site :</p>";

            //parcourir la liste des utilisateurs qu'on vient de récupérer avec la fonction dédiée qui renvoi un tableaau avec leurs infos
            foreach ($listeUtilisateurs as $utilisateur) {
                echo "<div style='margin-bottom: 15px;'>";

                echo "<strong>" . htmlspecialchars($utilisateur["identifiant"]) . "</strong> - ";
                echo htmlspecialchars($utilisateur["email"]) . " - ";
                //si l'utilisateur affiché est admin, on affiche qu'il est de type admin, sinon que c'est un visiteur
                if ($utilisateur["id_type_utilisateur"] == 1) {
                    echo "Type : Administrateur";
                } else {
                    echo "Type : Visiteur";
                }

                // Formulaire de gestion (promotion, rétrogradation, suppression) en POST
                echo "<form method='post' style='display:inline; margin-left: 10px;'>";

                //ci-dessous c'est un champ caché contenant le numéro de l'utilisateur ciblé, celui sur qui on va réaliser l'action 
                // a l'envoi du formulaire donc du clic sur un des 3 boutons d'action, je vais agir sur un 
                // utilisateur donné, donc c'est celui-ci qui est indiqué 
                echo "<input type='hidden' name='id_utilisateur_cible' value='" . $utilisateur["num_utilisateur"] . "'>";
                echo "<input type='submit' name='action_admin' value='promouvoir'>";
                echo "<input type='submit' name='action_admin' value='retrograder'>";
                echo "<input type='submit' name='action_admin' value='supprimer'>";
                echo "</form>";

                echo "</div>";
            }

            // Affichage du message d’action si nécessaire
            if ($messageActionAdmin != "") {
                echo "<p style='font-weight: bold; color: green;'>" . $messageActionAdmin . "</p>";
            }


            ?>
        </div>

        <div class="formulaire_ajout_manga">
            <!-- FORMULAIRE D'AJOUT D'UN MANGA AU CATALOGUE GLOBAL DU SITE -->
            <h3>Ajouter un manga au catalogue global</h3>

            <form method="post" action="EspaceUtilisateur.php">
                <label for="titre">Titre du manga :</label><br>
                <input type="text" name="titre" id="titre" required><br><br>

                <label for="synopsis">Synopsis :</label><br>
                <textarea name="synopsis" id="synopsis" rows="4" cols="50" required></textarea><br><br>

                <label for="date_debut">Date de début de publication :</label><br>
                <input type="date" name="date_debut" id="date_debut"><br><br>

                <label for="date_fin">Date de fin de publication (facultative) :</label><br>
                <input type="date" name="date_fin" id="date_fin"><br><br>

                <label for="nb_chapitres">Nombre de chapitres parus :</label><br>
                <input type="number" name="nb_chapitres" id="nb_chapitres"><br><br>

                <label for="nb_volumes">Nombre de volumes parus :</label><br>
                <input type="number" name="nb_volumes" id="nb_volumes"><br><br>

                <!-- LISTE DES AUTEURS -->
                <label for="id_auteur">Auteur :</label><br>
                <select name="id_auteur" id="id_auteur">
                    <option value="">-- Sélectionner un auteur --</option>
                    <?php foreach ($auteurs as $a) //parcourir le tableau des auteurs pour les afficher  dans la lsite déroulante
                        {
                            echo '<option value="' . $a["id_auteur"] . '">' . htmlspecialchars($a["prenom"]) . ' ' . htmlspecialchars($a["nom"]) . '</option>';
                        } ?>
                </select><br><br>

                <!-- Ajouter un nouvel auteur qui n'est pas dans la liste déroulante -->
                <label for="nouveau_prenom_auteur">Prénom :</label><br>
                <input type="text" name="nouveau_prenom_auteur" id="nouveau_prenom_auteur"><br>
                <label for="nouveau_nom_auteur">Nom :</label><br>
                <input type="text" name="nouveau_nom_auteur" id="nouveau_nom_auteur"><br><br>

                <!-- LISTE DES GENRES -->
                <label for="id_genre">Genre :</label><br>
                <select name="id_genre" id="id_genre" required>
                    <option value="">-- Sélectionner un genre --</option>
                    <?php foreach ($genres as $g)  //parcourir le tableau des genres pour les afficher
                        {
                            echo '<option value="' . $g["id_genre"] . '">' . htmlspecialchars($g["libelle_genre"]) . '</option>';
                        } ?>
                </select><br><br>

                <!-- LISTE DES TYPES -->
                <label for="id_type">Type de manga :</label><br>
                <select name="id_type" id="id_type" required>
                    <option value="">-- Sélectionner un type --</option>
                    <?php foreach ($types as $t) //parcourir le tableau des types pour les afficher
                        {
                            echo '<option value="' . $t["id_type_manga"] . '">' . htmlspecialchars($t["libelle_type_manga"]) . '</option>';
                        } ?>
                </select><br><br>

                <!-- LISTE DES STATUTS -->
                <label for="id_statut">Statut de publication :</label><br>
                <select name="id_statut" id="id_statut" required>
                    <option value="">-- Sélectionner un statut --</option>
                    <?php foreach ($statuts as $s) //parcourir le tableau des statuts pour les afficher 
                        {
                            echo '<option value="' . $s["id_statut_manga"] . '">' . htmlspecialchars($s["libelle_statut_manga"]) . '</option>';
                        } ?>
                </select><br><br>

                <!-- LISTE DES EDITEURS -->
                <label for="id_editeur">Éditeur :</label><br>
                <select name="id_editeur" id="id_editeur" required>
                    <option value="">-- Sélectionner un éditeur --</option>
                    <?php foreach ($editeurs as $e) //parcourir le tableau des éditeurs pour les afficher 
                        {
                            echo '<option value="' . $e["id_editeur"] . '">' . htmlspecialchars($e["nom_editeur"]) . '</option>';
                        } ?>
                </select><br><br>

                <!-- CHAMPS POUR LES 4 IMAGES -->
                <label>URL de l'image 1 :</label><br>
                <input type="text" name="image1"><br><br>

                <label>URL de l'image 2 :</label><br>
                <input type="text" name="image2"><br><br>

                <label>URL de l'image 3 :</label><br>
                <input type="text" name="image3"><br><br>

                <label>URL de l'image 4 :</label><br>
                <input type="text" name="image4"><br><br>

                <!-- BOUTON D'ENVOI DU FORMULAIRE -->
                <input type="submit" name="ajouter_manga_site" value="Ajouter au catalogue">
            </form>


            <?php
        }
        ?>

    </div>

    <p>Voici vos mangas enregistrés. Vous pouvez gérer vos lectures :</p>
    <div class="catalogue">
        <?php if (count($mangasPerso) == 0) { //cas où l'utilisateur n'a aucun manga dans sa liste ?>
            <p>Vous n’avez encore ajouté aucun manga dans votre catalogue personnel.</p>
        <?php } else { //cas ou l'utilisateur a au moins 1 manga ?>
            <?php foreach ($mangasPerso as $manga) { //parcourir le tableau des mangas perso ?>

                <!-- les htmlspecialchars permettent de sécuriser la faille rxss -->
                <div style="margin-bottom: 20px;">

                    <!-- ci-dessous le bloc qui va afficher l'image du manga en tant que lien vers la page ficheManga du manga concerné -->
                    <form action="FicheManga.php" method="post" style="display:inline;">
                        <input type="hidden" name="mot" value="<?php echo htmlspecialchars($manga["titre"]); ?>">
                        <button style="background-image= url('<?php echo htmlspecialchars($manga["image"]); ?>');"
                            type="submit">
                            <img src="<?php echo htmlspecialchars($manga["image"]); ?>"
                                alt="<?php echo htmlspecialchars($manga["titre"]); ?>" width="300">
                        </button>
                    </form>
                    <h3><?php echo htmlspecialchars($manga["titre"]); ?></h3>




                    <p><?php echo htmlspecialchars($manga["synopsis"]); ?></p>
                    <p>Dernier chapitre lu : <?php echo htmlspecialchars($manga["dernier_chapitre_lu"]); ?></p>

                    <div style="display:flex; gap:10px;"> <!-- je veux afficher les 2 boutons l'un a coté de l'autre -->
                        <!-- Modifier chapitre lu 
                        <form action="ModifierChapitre.php" method="post">
                            <input type="hidden" name="id_manga" value="<?php echo $manga["id_manga"]; ?>">
                            <input type="submit" value="Modifier le chapitre lu">
                        </form>-->

                        <!-- Supprimer le manga -->
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="id_manga" value="<?php echo $manga["id_manga"]; ?>">
                            <input type="hidden" name="supprimer_manga" value="1">
                            <input type="submit" value="Retirer du catalogue personnel">
                        </form>
                    </div>

                    <!-- Mettre à jour le dernier chapitre lu -->
                    <form method="post" style="margin-top:10px;">
                        <input type="hidden" name="id_manga"
                            value="<?php echo $manga["id_manga"]; //ce champ est caché et quand le formulaire est soumis, il envoie l'id du manga  ?>">

                        <label
                            for="id_chapitre_<?php echo $manga["id_manga"]; //label de la liste déroulante des chapitres ?>">Mettre
                            à jour le chapitre lu :</label>
                        <select name="id_chapitre"
                            id="id_chapitre_<?php echo $manga["id_manga"]; //id manga pour savoir à quel manga correpond la liste déroulante ?>">
                            <?php
                            // On récupère tous les chapitres pour le manga courant en appelant la fonction dédiée
                            $chapitres = getChapitresPourManga($manga["id_manga"]);

                            // On affiche les chapitres dans la liste déroulante
                            foreach ($chapitres as $chapitre) {
                                echo '<option value="' . $chapitre["id_chapitre"] . '">Chapitre ' . $chapitre["numero_chapitre"] . '</option>';
                            }
                            ?>
                        </select>

                        <!-- Bouton pour confirmer la mise à jour -->
                        <input type="submit" name="maj_chapitre" value="Mettre à jour">
                    </form>



                </div>


            <?php } ?>
        <?php } ?>
    </div>

    <div class="changement_mdp">
        <!-- FORMULAIRE : Changer le mot de passe (visible pour tous les utilisateurs,admin ou non) -->
        <h3>Changer votre mot de passe</h3>
        <form method="post" style="margin-bottom: 20px;">
            <label for="ancien">Ancien mot de passe :</label><br>
            <input type="password" name="ancien" id="ancien" required><br><br>

            <label for="nouveau">Nouveau mot de passe :</label><br>
            <input type="password" name="nouveau" id="nouveau" required><br><br>

            <input type="submit" value="Changer le mot de passe">
        </form>

        <?php if ($messageMotDePasse == "succès") { //si le changement a réussi, on affiche le message en, dessous ?>
            <p style="color:green; font-weight:bold;">Mot de passe changé avec succès.</p>
        <?php } elseif ($messageMotDePasse == "erreur") { //sinon, on affiche l'autre ?>
            <p style="color:red; font-weight:bold;">Ancien mot de passe incorrect.</p>
        <?php } ?>
    </div>

</body>

</html>