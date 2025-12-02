<!-- ce fichier contiendra les constantes et infos de connexion à la BDD ainsi que des paramètres du site (à voir comment faire, peut-être la langue ou l'encodage ?), je n'aurai donc besoin que de mofifier ce fichier si je veux changer des parmaètres du site -->

<?php
//Ce seront les infos de connexion à la BDD : 

$host = "localhost";
$dbname = "punkrecords";
$user = "root";
$password = ""; //y a pas de password donc chaine vide

//paramètres de configuration du site 
$langue = "fr";
$encodage = "utf8"; //ATTENTION --> UTF-8 avec un tiret causait une erreur !



//V2 de param.php pour régler les problèmes de connexion à la BDD, on va en faire un tableau associatif
//ce fichier renvoie directement ce tableau associatif conteantn les paramètres de la BDD
/*
return array(
    "host" => "localhost",
    "dbname" => "punkrecords",
    "user" => "root",
    "password" => "",
    "langue" => "fr",
    "encodage" => "utf8" //ATTENTION --> UTF-8 avec un tiret causait une erreur !
);
*/

?>
