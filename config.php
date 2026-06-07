<?php
// parametres de connexion a la BDD
$host = 'localhost';
$dbname = 'db-dervatit';
$username = 'usr-dervatit';
$password = 'sPrh6zMuSe_/';

//Connexion a la BDD avec PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données.");
}
?>
