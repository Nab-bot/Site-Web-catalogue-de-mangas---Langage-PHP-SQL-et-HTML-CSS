<?php
//page permettant de procéder à la connexion et si la connexion réussi, elle mène sur l'espace de l'utilisateur connecté 

session_start();//démarrage de la session


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


$erreur = ""; //création du message d'erreur

//contrôle de si le login ET le mot de passe ont été saisis
if (isset($_POST["login"]) && isset($_POST["motdepasse"])) {

    //récupérer les valeur saisies dans le formulaire
    $login = $_POST["login"];
    $mdp = $_POST["motdepasse"];

    //appel de la fonction de connexion pour se connecter à la BDD, vérifier si l'utilisateur correspondant 
    // à ces identifiants existe bien dans la BDD, enfin elle lance la sesion et stocke les infos de l'user si trouvé
    $ok = ConnexionUtilisateur($login, $mdp); // la fonction est dans fonctions.php

    //si la fonction de connexion renvoie true, elle a trouvé un user, sinon c'est false
    if ($ok == true) {
        //header va faire une redirection automatique vers l'espace utilisateur si la connexion marche
        header("Location: EspaceUtilisateur.php");
        exit;//on sort vu qu'on a vu que la connexion marchait, plus besoin d'exécuter la suite du code
    } else {
        $erreur = "Identifiants incorrects.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">x
    <title>Connexion</title>
</head>
<?php include("header.php") ?>

<body class="<?php echo $_SESSION['theme']; ?>">

    <div id="espaceur_connexion">
        <br>
    </div>
    <div class="Titre">
        <h2>Connexion à votre compte</h2>
    </div>
    <div class="contenu_formulaire">
        <div class="espace_connection">
            <form action="Connexion.php" method="post">
                <label for="login">Identifiant :</label>
                <input type="text" name="login" id="login" required><br><br>
                <label for="motdepasse">Mot de passe :</label>
                <input type="password" name="motdepasse" id="motdepasse" required><br><br>
                <input type="submit" value="Se connecter">
            </form>

            <!-- message d’erreur éventuel -->
            <?php if ($erreur != "") {
                echo "<p style='color:red;'>" . $erreur . "</p>";
            } ?>

            <!-- lien vers la création de compte si l'utilisateur n'en a pas encore et qu'il veut en créer un -->
        </div>
        <div class="espace_creation_compte">
            <p> Pas encore inscrit ? <a href="CreationCompte.php">Créer un compte</a></p>
        </div>
    </div>
    <div id="espaceur_connexion">
        <br>
    </div>
    <?php include("footer.php") ?>

</body>

</html>