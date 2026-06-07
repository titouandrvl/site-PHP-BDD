<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include('config.php');

// Vérification connexion
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}

// Récupération des infos de l'utilisateur de la BDD
$stmt = $pdo->prepare("SELECT * FROM sae203_utilisateur WHERE id_utilisateur = ?");
$stmt->execute([$_SESSION['utilisateur_id']]);
$utilisateur = $stmt->fetch();

// Récupération des inscriptions et résultats de l'utilisateur
$stmt = $pdo->prepare("
    SELECT i.*, c.lieu, c.date, c.categorie, c.description
    FROM sae203_inscription i
    JOIN sae203_competition c ON i.competition_id = c.id_competition
    WHERE i.utilisateur_id = ?
    ORDER BY c.date ASC
");
$stmt->execute([$_SESSION['utilisateur_id']]);
$inscriptions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil</title>
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

    <h1>Mon profil</h1>

    <!-- Infos personnelles -->
    <div class="profil-infos">
        <p><strong>Nom :</strong> <?= htmlspecialchars($utilisateur['nom']) ?></p>
        <p><strong>Prénom :</strong> <?= htmlspecialchars($utilisateur['prenom']) ?></p>
        <p><strong>Email :</strong> <?= htmlspecialchars($utilisateur['mail']) ?></p>
        <p><strong>Genre :</strong> <?= htmlspecialchars($utilisateur['genre']) ?></p>
        <p><strong>Date de naissance :</strong> <?= date('d/m/Y', strtotime($utilisateur['date_naissance'])) ?></p>
        <p><strong>Ville :</strong> <?= htmlspecialchars($utilisateur['ville']) ?></p>
    </div>

    <hr>

    <!-- Inscriptions et résultats -->
    <h2>Mes inscriptions</h2>

    <?php if (count($inscriptions) == 0) : ?>
        <p>Vous n'êtes inscrit à aucune compétition pour le moment.</p>
    <?php else : ?>
        <div class="competitions-wrapper"> 
            <?php foreach ($inscriptions as $inscription) : ?>
                <div class="competition">
                    <p><strong>Catégorie :</strong> <?= htmlspecialchars($inscription['categorie']) ?></p>
                    <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($inscription['date'])) ?></p>
                    <p><strong>Lieu :</strong> <?= htmlspecialchars($inscription['lieu']) ?></p>
                    <p><strong>Données publiques :</strong> <?= $inscription['donnees_publiques'] ? 'Oui' : 'Non' ?></p>

                    <h3>Résultats</h3>
                    <?php if ($inscription['chrono'] !== null && $inscription['classement'] !== null) : ?>
                        <p><strong>Chrono :</strong> <?= htmlspecialchars($inscription['chrono']) ?></p>
                        <p><strong>Classement :</strong> <?= $inscription['classement'] ?></p>
                    <?php else : ?>
                        <p>Résultats pas encore disponibles.</p>
                    <?php endif; ?>
                </div>
                <hr>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <p><a href="index.php">Retour aux compétitions</a></p>

    <hr>
    <p>Connecté en tant que <?= htmlspecialchars($_SESSION['prenom']) ?> | <a href="deconnexion.php">Déconnexion</a></p>

</body>
</html>