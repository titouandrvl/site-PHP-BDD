<?php
//affichage des erreurs php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include('config.php');

//recupere les competitions et nom de l'organisateur (JOIN relie deux tables)
$query = "SELECT c.*, u.nom AS nom_organisateur, u.prenom AS prenom_organisateur 
          FROM sae203_competition c
          JOIN sae203_utilisateur u ON c.organisateur_id = u.id_utilisateur
          ORDER BY c.date ASC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$competitions = $stmt->fetchAll(); //reecupere les resultats sous tableau
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <nav>
            <?php if (!isset($_SESSION['utilisateur_id'])) : ?>
                <a href="index.php">Accueil</a>
                <a href="connexion.php">Connexion</a>
                <a href="inscription.php">Inscription</a>

            <?php elseif ($_SESSION['role'] == 1) : ?>
                <a href="admin_dashboard.php">Dashboard</a>
                <a href="resultats.php">Résultats</a>
                <a href="deconnexion.php">Déconnexion</a>

            <?php else : ?>
                <a href="index.php">Accueil</a>
                <a href="profil.php">Mon profil</a>
                <a href="resultats.php">Résultats</a>
                <a href="deconnexion.php">Déconnexion</a>
            <?php endif; ?>
        </nav>
    </header>
    
    <h1>Compétitions disponibles</h1>

    //si aucun competitions en BDD
    <?php if (count($competitions) == 0) : ?>
        <p>Aucune compétition disponible pour le moment.</p>

    <?php else : ?>
        <div class="competitions-wrapper">
            <?php foreach ($competitions as $competition) : ?>

                //description des competes avec infos de la BDD
                <div class="competition">

                    <?= $competition['description'] ?>

                    <p><strong>Catégorie :</strong> <?= htmlspecialchars($competition['categorie']) ?></p>
                    <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($competition['date'])) ?></p>
                    <p><strong>Lieu :</strong> <?= htmlspecialchars($competition['lieu']) ?></p>
                    <p><strong>Places restantes :</strong> <?= $competition['nb_restant'] ?> / <?= $competition['nb_participant'] ?></p>
                    <p><strong>Organisateur :</strong> <?= htmlspecialchars($competition['prenom_organisateur'] . ' ' . $competition['nom_organisateur']) ?></p>

                    <?php if ($competition['nb_restant'] > 0) : ?>
                        <?php if (isset($_SESSION['utilisateur_id'])) : ?>
                            <a href="inscription_competition.php?id=<?= $competition['id_competition'] ?>">S'inscrire</a>
                        <?php else : ?>
                            //non co : invitation a se connecter
                            <a href="connexion.php">Connectez-vous pour vous inscrire</a>
                        <?php endif; ?>
                    <?php else : ?>
                        //plus de places disponibles
                        <p style="color: red;">Complet</p>
                    <?php endif; ?>

                </div>
                <hr>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <hr>
    <?php if (isset($_SESSION['utilisateur_id'])) : ?>
        <p>Connecté en tant que <?= htmlspecialchars($_SESSION['prenom']) ?> | <a href="deconnexion.php">Déconnexion</a></p>
    <?php else : ?>
        <p><a href="connexion.php">Se connecter</a> pour accéder à vos résultats</p>
    <?php endif; ?>

</body>
</html>