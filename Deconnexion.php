<?php
//page qui ne s'affichera pas, elle sert juste à permettre la déconnexion en détruisant la session et ramène à la page d'accueil

// On démarre la session pour pouvoir la détruire
session_start();


// On supprime toutes les variables de session
$_SESSION = array();

/* ci-dessous c'est pour gérer les cookies, pour le moment on en utilise pas donc laisser en commentaire
// S'il y a un cookie de session, on le supprime aussi (par sécurité)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params(); // récupère les paramètres actuels du cookie
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}
*/

// On détruit la session 
session_destroy();

// On détruit aussi le tableau $_SESSION 
unset($_SESSION);

// Enfin, on redirige l'utilisateur vers l'accueil du site
header("Location: Accueil.php");
exit;
?>