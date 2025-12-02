<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Punk Records</title>
    <link rel="stylesheet" href="stylefinal.css">

</head>
<header>
    <div class="<?php echo $_SESSION['theme']; //on récupère l'information stockée en session, donc le mot disant si le thème est clair ou sombre ?>"
        id="header">
        <div class="logo">
            <a href="Accueil.php">
                <p>Punk Records</p>
            </a>
        </div>
        <!-- NAVIGATION -->
        <div class="navigation">
            <form action="Accueil.php" method="get">
                <button type="submit">Accueil</button>
            </form>
            <form action="Catalogue.php" method="get">
                <button type="submit">Catalogue</button>
            </form>

            <?php
            // si l'utilisateur est connecté, on propose d'aller sur l'espace utilisateur et on propose la déconnexion déconnexion
            if (isset($_SESSION["num_utilisateur"])) {
                ?>
                <form action="EspaceUtilisateur.php" method="get">
                    <button type="submit">Espace Utilisateur</button>
                </form>
                <form action="Deconnexion.php" method="get">
                    <button type="submit">Déconnexion</button>
                </form>
                <?php
            } else //sinon, on lui propose de se connecter
            {
                ?>
                <form action="Connexion.php" method="get">
                    <button type="submit">Se connecter</button>
                </form>
                <?php
            }
            ?>
            <!-- formulaire de changement de thème -->
            <form method="post" style="display:inline;">
                <input type="hidden" name="changer_theme" value="<?php echo $valeurTheme; ?>">

                <?php if (isset($_POST['mot'])) { //permet de vérfiier si l'utilkisateur a atteint la page suite à une recherche ?>
                    <input type="hidden" name="mot" value="<?php echo htmlspecialchars($_POST['mot']); ?>">
                <?php } ?>
                <input type="submit"
                    value="<?php echo $texteBouton; //cliquer sur ce formulaire va renvoyer le même mot clé donc permettre de ne pas perdre la page et envoyer dans le même temps lka valeur du thème pour afficher le thème clair ou sombre ?>">
            </form>
        </div>


        <!-- Formulaire pour changer le thème clair/sombre -->
    </div>
    </div>
</header>