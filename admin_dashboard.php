<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include('config.php');

// Vérification que l'utilisateur est connecté et est bien un organisateur
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}

//seul orga sinon envoyé vers index
if ($_SESSION['role'] != 1) {
    header('Location: index.php');
    exit;
}

// Récupération des compétitions de l'organisateur connecté, empeche de modifier les compete des autres
$stmt = $pdo->prepare("SELECT * FROM sae203_competition WHERE organisateur_id = ? ORDER BY date ASC");
$stmt->execute([$_SESSION['utilisateur_id']]);
$competitions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard organisateur</title>
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

    <h1>Bienvenue <?= htmlspecialchars($_SESSION['prenom']) ?> !</h1>
    <h2>Vos compétitions</h2>

    //si pas encore de compete
    <?php if (count($competitions) == 0) : ?>
        <p>Vous n'avez pas encore créé de compétition.</p>
    <?php else : ?>
        <div class="competitions-wrapper">
            <?php foreach ($competitions as $competition) : ?>
                <div class="competition">
                    <?= $competition['description'] ?>
                    <p><strong>Catégorie :</strong> <?= htmlspecialchars($competition['categorie']) ?></p>
                    <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($competition['date'])) ?></p>
                    <p><strong>Lieu :</strong> <?= htmlspecialchars($competition['lieu']) ?></p>
                    <p><strong>Places restantes :</strong> <?= $competition['nb_restant'] ?> / <?= $competition['nb_participant'] ?></p>

                    <a href="modifier_competition.php?id=<?= $competition['id_competition'] ?>">Modifier</a><br>
                    <a href="saisie_resultats.php?id=<?= $competition['id_competition'] ?>">Saisir les résultats</a>
                </div>
                <hr>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <hr>
    <p>Connecté en tant que <?= htmlspecialchars($_SESSION['prenom']) ?> (organisateur) | <a href="deconnexion.php">Déconnexion</a></p>

</body>
</html>