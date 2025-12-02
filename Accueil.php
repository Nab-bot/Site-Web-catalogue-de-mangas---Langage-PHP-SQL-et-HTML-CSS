
<?php
//Page d'accueil du site, regroupera une barre de recherche et des onglets menant à l'ensemble d'un catalogue de mangas, 
// le bouton remenant à l'accueil et aussi un bouton pour accéder à son espace utilisateur ou pour se connecter 


session_start(); //démarrer la session 

include("fonction/fonctions.php"); //on appelle les fonctions pour notamment gérer le thème du site (clair ou sombre)

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
?>


<!DOCTYPE html>
<html lang="fr">

<?php include("header.php") ?>

<body
    class="<?php echo $_SESSION['theme']; //on récupère l'information stockée en session, donc le mot disant si le thème est clair ou sombre ?>">
    <div class="hero">
        <div class="hero_content">
            <h1>Plongez dans le coeur du manga à travers Punk Records</h1>
            <!-- BARRE DE RECHERCHE V2 -->
            <form action="Catalogue.php" method="post">
                <input type="text" name="mot" id="mot" placeholder="Titre, auteur...">
                <input type="submit" value="Rechercher">
            </form>
        </div>
    </div>
    <h2>Mangas en vedette</h2>
    <hr class="separateur">

    <!-- CARROUSEL -->
    <div class="carrousel">

        <!-- Carte 2 : Naruto -->


        <!-- Carte 3 : L'Attaque des Titans -->

    </div>
    <div class="espace_manga">
        <div class="ligne_manga">


            <form class="carte" action="FicheManga.php" method="post">
                <input type="hidden" name="mot" value="One Piece">
                <button type="submit">
                    <img src="images/onepiece1.jpg" alt="AOT"><br>
                    One piece
                </button>
            </form>
            <form class="carte" action="FicheManga.php" method="post">
                <input type="hidden" name="mot" value="Naruto">
                <button type="submit">
                    <img src="images/naruto1.png" alt="">
                    Naruto
                </button>
            </form>


            <form class="carte" action="FicheManga.php" method="post">
                <input type="hidden" name="mot" value="L'Attaque des Titans">
                <button type="submit">
                    <img src="images/aot1.jpg" alt="AOT"><br>
                    Attaque des Titans
                </button>
            </form>
        </div>
    </div>
    <hr class="separateur">
</body>
<?php include("footer.php") ?>



</html>