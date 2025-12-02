<?php
//page qui affichera les informations d'un manga telles que le type, le titre, l'auteur, le synopsis et une série d'images liées au manga concerné


session_start();//démarrer la session, car on va vouloir voir à quel chapitre en est l'utilisateur dans sa lecture, donc on va devoir récupérer le numéro d'utilisateur dans la session

include("fonction/fonctions.php"); // j'appelle le fichier qui contient les fonctions pour pouvoir 
// les appeler (à noter que ce fichier contient lui-même le fichier des paramètres, 
// donc inutile de le rappeler)


changerTheme(); // gère le thème clair/sombre, on l'appelle dés le début de la page pour traiter cette info avant tout, elle est récupéré avec la méthode POST et stockée en session, il faut donc l'appeler juste après le session start





//test pour voir si le thème en session est clair ou sombre pour savoir lequel appliquer
if ($_SESSION["theme"] == "clair") {
    $valeurTheme = "sombre";    //si le thème en session est clair, alors on proposera d'afficher le thèlme sombre
    $texteBouton = "Thème : Clair";  //permettra d'indiquer le thème actif actuellement sur le site, c'est le thème en session
} else {
    //ci-dessous la logique est la même mais dans le cas où e thème est sombre et ou on propose clair
    $valeurTheme = "clair";
    $texteBouton = "Thème : Sombre";
}


$connex = connecterBD();//connexion à la BDD


//vérifier si un mot clé a été saisi ET qu'il ne s'agit pas non plus d'un clic sur le bouton pour changer le thème, si pas le cas, on renvoie vers la page catalogue
if (isset($_POST["mot"]) == false && isset($_POST["changer_theme"]) == false) {
    // Aucun mot envoyé, on a accédé à cette page de façon anormale donc on renvoie sur le catalogue
    header("Location: Catalogue.php");
    exit;
}





// Récupération des infos du manga à partir des mots clés saisis
if (isset($_POST["mot"])) {
    $titre = $_POST["mot"];
    $requete = $connex->prepare(" SELECT m.*, a.nom AS nom, a.prenom AS prenom, g.libelle_genre AS genre, t.libelle_type_manga AS type
    FROM manga m
    JOIN auteur a ON m.id_auteur = a.id_auteur
    JOIN genre g ON m.id_genre = g.id_genre
    JOIN type_manga t ON m.id_type_manga = t.id_type_manga
    WHERE m.titre = :titre");

    $requete->execute(array("titre" => $titre));
    $manga = $requete->fetch();

    //on vérifie si un manga a été trouvé ou pas avec le titre donné
    $mangaIntrouvable = false; //créer variable booléenne indiquant si le manga est trouvé ou pas, par défaut, on suppose que le manga a été trouvé

    //si aucun manga n'est renvoyé par la requete sql on met que la variable $mangaIntrouvable est vraie, on s'en servira pour l'affichage après
    if ($manga === false) {
        $mangaIntrouvable = true; // pas de manga trouvé !
    } else { //ici le cas ou un manga est trouvé, on récupère l'identifiant du manga
        $id_manga = $manga["id_manga"];
        // (le reste de ton code continue ici : images, chapitre lu, etc.)



        // On récupère aussi l’id du manga pour d'autres requêtes
        $id_manga = $manga["id_manga"];

        // Récupération des images associées aux mangas
        $requete = $connex->prepare("SELECT url_image FROM image_manga WHERE id_manga = :id_manga ORDER BY id_image ASC LIMIT 4");
        $requete->execute(array("id_manga" => $id_manga));

        $manga["images"] = [];//création du tableau d'images manga
        while ($ligne = $requete->fetch()) { //tant que la requete trouve des entrées, elle l'affecte à $ligne
            $manga["images"][] = $ligne["url_image"]; //l'image est ajoutée au tableau des mangas dans le champ image, car le champ images lui-même est un tableau car contient plusieurs images (ici 4)
        }


        //ce qui va suivre servira à récupérer le dernier chapitre lu dans l apage fiche manga afin de l'afficher dans ladite page
        $dernierChapitreLu = null; //créer la variable qui va contenir le dernier chapitre lu, on met null pour gérer le cas où le visiteur n'a jaamais lu le manga

        //vérifier si l'utilisateur est connecté
        if (isset($_SESSION["num_utilisateur"])) {

            //on récupère le numéro utilisateur dans une variable ici, on s'en servira dans la requete sql juste après
            $id_utilisateur = $_SESSION["num_utilisateur"];

            //on passe donc à ladite requete sql, elle donnera le chapitre le plus récent lu par l'utilisateur
            $requete = $connex->prepare("SELECT MAX(c.numero_chapitre) AS dernier_chapitre
                                 FROM lecture l
                                 JOIN chapitre c ON l.id_chapitre = c.id_chapitre
                                 WHERE l.num_utilisateur = :id_user AND l.id_manga = :id_manga");

            //on exécute la requete avec les valeurs du numéro du visiteur et le numéro identifiant le manga
            $requete->execute(array(
                "id_user" => $id_utilisateur,
                "id_manga" => $id_manga
            ));
            //on récupère le résultat de la requete, donc le chapitre le plus récent et on le stocke dans notre variable
            $resultatChapitre = $requete->fetch();

            $connex = null; // on se déconnecte la BDD


            //on vérifie que la requete a bien renvoyé un résultat et que dernier chapitre n'est pas null
            //ça veut dire que que cette condition est remplie dés qu'un utilisateur aura lu au moins un chapitre
            if ($resultatChapitre && $resultatChapitre["dernier_chapitre"] !== null) {


                //si les conditions sont bonnes, le résultat de la requete sql est stocké dans la variable $dernierChapitreLu
                // on récupère le numéro du dernier chapitre lu depuis le champ "dernier_chapitre"
                $dernierChapitreLu = $resultatChapitre["dernier_chapitre"]; //
            }




        }
    }
}
/*
//récupérer le mot clé saisi dans la barre de recherche
//récpérer le résultazt de la fonction de recherche dans une variable, qui est $resultat 
// (voir si je peux le diviser en plusieurs éléments car je dois les placer à des endroits différents sur la page, pas juste à la suite)
if (isset($_POST['mot'])) {
    $motCle = $_POST['mot'];
    $resultat = Recherche($motCle);
} else {
    $resultat = "Aucun mot clé reçu.";
}
*/
//comme je viens de récupérer les infos dans la BDD, je dois me déconnecter de la BDD après coup

include("header.php");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<!-- Affichage des résultats (il faudra que je le traite comme un tableau ou que je stocke les différents) -->

<body class="<?php echo $_SESSION['theme']; ?>">
    <div>

        <?php
        if ($mangaIntrouvable) { //si $mangaIntrouvable est true, on affiche le message ci-dessous
            echo "<p>Aucun manga trouvé avec ce titre.</p>";
        } else { //sinon, on affiche les infos du manga et si l'utilisateur est connecté, sa progression !
            echo "<div style='border:1px solid black; margin-bottom:20px;'>";

            // IMAGE 1 : bandeau tout en haut
            echo "<div class='conteneur_hero_fiche_manga' style=\"background-image: url('" . $manga["images"][0] . "');\">";
            // Titre
            echo "<h1>" . $manga["titre"] . "</h1>";
            echo "<div><strong>Genre :</strong> " . $manga["genre"] . " | <strong>Type :</strong> " . $manga["type"] . "</div>";
            echo "</div>";
            /*    echo "<img src='" . $manga["images"][0] . "' alt='Image 1' width='100%' height='500px' />";
            echo "</div>"; */



            // Image + texte sur la droite
            echo "<div class='synopsis_manga'>";
            echo "<div class='texte_synopsis'>";
            // Synopsis
            echo "<h2> SYNOPSIS </h2>";
            echo "<br>";
            echo "<p> " . $manga["synopsis"] . "</p>";
            echo "</div>";
            echo "<div class='image_synopsis'>";
            echo "<img src='" . $manga["images"][1] . "' alt='Image 2' width='120' />";
            echo "</div>";
            echo "</div>";
            //si l'utilisateur est connecté, on affiche ceci, sinon, ce message n'apparaitra pas
            if (isset($_SESSION["num_utilisateur"])) {
                //si la variable dernier chapitre lu ne vaut pas null, on affichera son contenu pour indiquer au visiteur où il en est dans sa lecture !
                if ($dernierChapitreLu !== null) {
                    echo "<p><strong>Votre progression :</strong> Vous avez lu jusqu’au chapitre " . htmlspecialchars($dernierChapitreLu) . ".</p>";
                }
                //sinon, on affiche ce message disant qu'il n'a pas encore lu le manga
                else {
                    echo "<p><strong>Votre progression :</strong> Vous n’avez pas encore commencé ce manga.</p>";
                }
            }

            // IMAGE 3 + auteur au-dessus
            echo "<div class='synopsis_manga' >";
            echo "<div class='image_synopsis'>";
            echo "<img src='" . $manga["images"][2] . "' alt='Image 3' width='150' />";
            echo "</div>";
            echo "<br>";
            echo "<div class='texte_synopsis'>";
            echo "<div> <h2><strong>Auteur :</strong> " . $manga["prenom"] . " " . $manga["nom"] . " </h2> </div>";
            echo "</div>";
            echo "</div>";

            // IMAGE 4 tout en bas
            echo "<div class='conteneur_hero_fiche_manga' style=\"background-image: url('" . $manga["images"][3] . "');\">";
            echo "</div>";
            echo "</div>"; // fermeture du bloc manga
            //}
            // } else {
            //   echo $resultat;
            //}
        }
        ?>
    </div>
</body>

<?php include("footer.php") ?>

</html>