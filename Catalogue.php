<?php
//page php qui permettra d'afficher le catalogue global du site, donc tous les mangas présents dans la base de données du site 

session_start();//démarrer la session


include("fonction/fonctions.php"); // Inclut les fonctions

changerTheme(); // Gère le changement de thème 

//test pour voir si le thème en session est clair ou sombre pour savoir lequel appliquer
if ($_SESSION["theme"] == "clair") {
    $valeurTheme = "sombre";    //si le thème en session est clair, alors on proposera d'afficher le thèlme sombre
    $texteBouton = "Thème : Clair";  //permettra d'indiquer le thème actif actuellement sur le site, c'est le thème en session
} else {
    //ci-dessous la logique est la même mais dans le cas où e thème est sombre et ou on propose clair
    $valeurTheme = "clair";
    $texteBouton = "Thème : Sombre";
}

// on vérifie si des mots clés sont saisis, si oui, on fait la recherche, sinon, on affiche le catalogue complet
if (isset($_POST["mot"])) {
    $motCle = $_POST["mot"];
    $mangas = Recherche($motCle); // fonction de recherche
} else {
    // on appelle la fonction qui récupère tous les mangas avec leur image et titre
    $mangas = CatalogueManga(); // tout le catalogue
}
?>

<!DOCTYPE html>
<html>

<!-- afficher les images à la même taille dans le catalogue, sinon c'était moche et pas très lisible -->

<style>
    .catalogue img {
        width: 150px;
        height: auto;
        display: block;
        margin: 0 auto 10px auto;
    }

    .carte {
        display: inline-block;
        text-align: center;
        margin: 10px;
    }
</style>


<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>

    <style>
        .navigation {
            display: flex;
            gap: 10px;
            /* espace entre les boutons */
            margin-bottom: 20px;
        }

        .navigation form {
            margin: 0;
        }

        .clair {
            background-color: white;
            color: black;
        }

        .sombre {
            background-color: #121212;
            color: white;
        }

        .clair button {
            background-color: #f0f0f0;
            color: black;
        }

        .sombre button {
            background-color: #333;
            color: white;
        }
    </style>

</head>
<?php include("header.php") ?>

<body class="<?php echo $_SESSION['theme']; ?>">
    <div id="espaceur_connexion">
        <br>
    </div>
    <h1>Ce qu'on a en rayon pour toi!</h1>
    <div class="catalogue">
        <?php foreach ($mangas as $id => $manga) { //parcourir le tableau des mangas ?>
            <div class="carte">
                <!-- Formulaire vers la fiche du manga -->
                <form action="FicheManga.php" method="post">
                    <input type="hidden" name="mot"
                        value="<?php echo htmlspecialchars($manga["titre"]); //affichage du titre avec htmlspecialchars pour éviter faille xss ?>">
                    <button type="submit" style="border:none; background:none; padding:0;">

                        <?php //affichage des images
                            // Cas 1 : une seule image par manga, c'est ce qu'on a dans le catalogue 
                            if (isset($manga["image"])) {
                                echo '<img src="' . htmlspecialchars($manga["image"]) . '" alt="' . htmlspecialchars($manga["titre"]) . '">';
                                //si on est dans le catalogue, alors si le champ images de manga n'est pas vide, on affiche l'image dont la source est indiquée par $manga["image"]
                            }
                            // Cas 2 : tableau d’images, c'est ce que la fonction recherche renvoie, avec un tableau d'images
                            elseif (isset($manga["images"]) && isset($manga["images"][0])) {
                                echo '<img src="' . htmlspecialchars($manga["images"][0]) . '" alt="' . htmlspecialchars($manga["titre"]) . '">';
                                //idem mais cas où on passe par la fonction de recherche car traite la chose différemment, images devient lui-même un tableau au sein de manga, et on en affiche la 1ère image, donc ici $manga["images"][0]
                            }
                            // Aucun visuel donc on affiche un message l'indiquant 
                            else {
                                echo '<p>Image manquante</p>';
                            }
                            ?>

                        <div><?php echo htmlspecialchars($manga["titre"]);//affichage du titre du manga ?></div>
                    </button>
                </form>

                <!-- Formulaire pour ajouter le manga au catalogue personnel -->
                <?php if (isset($_SESSION["num_utilisateur"]) && isset($manga["id"])) { //si l'utilisateur est connecté, donc on a son numéro en session et si l'id du manga est renseigné, donc qu'il existe bien dans la BDD ?>
                    <form action="EspaceUtilisateur.php" method="post" style="margin-top: 5px;">
                        <input type="hidden" name="ajouter_manga"
                            value="<?php echo $manga["id"]; //ce champ hidden permettra de passer l'info de quelk manga on veut ajouter au catalogue perso quand on va cliquer sur le bouton dédié ?>">
                        <input type="submit" value="Ajouter à mon catalogue">
                    </form>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
    <div id="espaceur_connexion">
        <br>
    </div>
</body>
<?php include("footer.php") ?>

</html>