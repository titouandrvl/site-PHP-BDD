<?php
session_start(); //demarrage session
session_unset(); // Supprimer toutes les variables de session stocké de connexion.php
session_destroy(); // Détruire la session

// Rediriger vers la page de connexion
header('Location: connexion.php');
exit;
