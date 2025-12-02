<?php
//page permettant à un utilisateur de procéder à la création de son compte, par défaut le compte créé sera un compte utilisateur, pas administrateur

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


//les variables qui suivent serviront à afficher des messages après
$confirmation = "";
$erreur = "";

// Traitement du formulaire si les 3 champs sont bien remplis
//si les 3 champs sont remplis, on récupère les infos dedans
if (isset($_POST["login"]) && isset($_POST["motdepasse"]) && isset($_POST["mail"])) {
    $login = $_POST["login"];
    $mdp = $_POST["motdepasse"];
    $mail = $_POST["mail"];


    //appel de la focntion de création du compte pour insérer le compte en BDD
    $ok = CreerUtilisateur($login, $mdp, $mail);


    //si la fonction renvoie true, la fonction renvoie true pour dire que ça a marché, sinon false et dit que ça a échoué !
    if ($ok == true) {
        $confirmation = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
    } else {
        $erreur = "Ce login ou ce mail est déjà utilisé !";
    }
}

include("header.php")
    ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un compte</title>
</head>

<body class="<?php echo $_SESSION['theme']; ?>">
    <div id="espaceur_connexion">
        <br>
    </div>
    <div class="bouton_creation_compte">
        <h2>Créer un nouveau compte</h2>
    </div>
    <!-- Formulaire de création du nouveau compte -->
    <div class="conteneur_formulaire">
        <form action="CreationCompte.php" method="post">
            <label for="login">Identifiant :</label>
            <input type="text" name="login" id="login" required><br><br>

            <label for="motdepasse">Mot de passe :</label>
            <input type="password" name="motdepasse" id="motdepasse" required><br><br>

            <label for="mail">Adresse e-mail :</label>
            <input type="email" name="mail" id="mail" required><br><br>

            <input type="submit" value="Créer mon compte">
        </form>
    </div>

    <div class="message_formulaire">
        <!-- Messages en cas d'erreur ou de confirmation -->
        <?php if ($erreur != "") {
            echo "<p style='color:red;'>" . $erreur . "</p>";
        } ?>
        <?php if ($confirmation != "") {
            echo "<p style='color:green;'>" . $confirmation . "</p>";
        } ?>
    </div>
    <div id="espaceur_connexion">
        <br>
    </div>
</body>
<?php include("footer.php") ?>

</html>