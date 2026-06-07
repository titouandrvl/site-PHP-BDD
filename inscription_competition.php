<?php
ini_set('display_errors', 1); //affichage errreur
error_reporting(E_ALL);

session_start();
include('config.php');

// Vérification connexion, sinon redirection a la page de connexion
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}

$message_erreur = '';
$message_succes = '';

// Récupération de l'id de la compétition depuis l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}
//force la conversion en entier pour securiser la valeur
$id_competition = (int)$_GET['id'];

// Récupération de la compétition en BDD
$stmt = $pdo->prepare("SELECT * FROM sae203_competition WHERE id_competition = ?");
$stmt->execute([$id_competition]);
$competition = $stmt->fetch();

// redirection vers index
if (!$competition) {
    header('Location: index.php');
    exit;
}

// Vérification que l'utilisateur n'est pas déjà inscrit
$stmt = $pdo->prepare("SELECT id_inscription FROM sae203_inscription WHERE utilisateur_id = ? AND competition_id = ?");
$stmt->execute([$_SESSION['utilisateur_id'], $id_competition]);
$deja_inscrit = $stmt->fetch();

// Traitement du formulaire quand l'utilisateur confirme
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($deja_inscrit) {
        $message_erreur = "Vous êtes déjà inscrit à cette compétition.";
    } elseif ($competition['nb_restant'] <= 0) {
        $message_erreur = "Cette compétition est complète.";
    } else {
        //recupere la valeur de la case coché
        $donnees_publiques = isset($_POST['donnees_publiques']) ? 1 : 0;

        // Insertion de l'inscription dans la BDD
        $stmt = $pdo->prepare("INSERT INTO sae203_inscription (utilisateur_id, competition_id, donnees_publiques) VALUES (?, ?, ?)");
        $stmt->execute([$_SESSION['utilisateur_id'], $id_competition, $donnees_publiques]);

        // Mise à jour du nombre de places restantes
        $stmt = $pdo->prepare("UPDATE sae203_competition SET nb_restant = nb_restant - 1 WHERE id_competition = ?");
        $stmt->execute([$id_competition]);

        $message_succes = "Inscription réussie ! Bonne chance pour la compétition.";
        $deja_inscrit = true; // Pour cacher le formulaire après inscription
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription à la compétition</title>
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

    <h1>Inscription à la compétition</h1>

    <!-- Infos de la compétition -->
    <div class="competition">
        <?= $competition['description'] ?>
        <p><strong>Catégorie :</strong> <?= htmlspecialchars($competition['categorie']) ?></p>
        <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($competition['date'])) ?></p>
        <p><strong>Lieu :</strong> <?= htmlspecialchars($competition['lieu']) ?></p>
        <p><strong>Places restantes :</strong> <?= $competition['nb_restant'] ?> / <?= $competition['nb_participant'] ?></p>
    </div>

    //affichages de messages erreurs ou succes
    <?php if (!empty($message_erreur)) : ?>
        <p class="msg-erreur"><?= htmlspecialchars($message_erreur) ?></p>
    <?php endif; ?>
    <?php if (!empty($message_succes)) : ?>
        <p class="msg-succes"><?= htmlspecialchars($message_succes) ?></p>
    <?php endif; ?>

    //formulaire affiche si utilisateur deja inscrit
    <?php if ($deja_inscrit && empty($message_succes)) : ?>
        <p class="msg-erreur">Vous êtes déjà inscrit à cette compétition.</p>
    <?php endif; ?>

    //Formulaire uniquement si pas encore inscrit et places disponibles
    <?php if (!$deja_inscrit && $competition['nb_restant'] > 0) : ?>
        <form action="inscription_competition.php?id=<?= $id_competition ?>" method="POST">
            <p><strong>Nom :</strong> <?= htmlspecialchars($_SESSION['nom']) ?></p>
            <p><strong>Prénom :</strong> <?= htmlspecialchars($_SESSION['prenom']) ?></p>

            <p>
                <input type="checkbox" id="donnees_publiques" name="donnees_publiques">
                <label for="donnees_publiques">J'accepte que mon classement soit affiché publiquement</label>
            </p>

            <button type="submit">Confirmer l'inscription</button>
        </form>
    <?php elseif ($competition['nb_restant'] <= 0 && !$deja_inscrit) : ?>
        <p class="msg-erreur">Cette compétition est complète.</p>
    <?php endif; ?>

    <p><a href="index.php">Retour aux compétitions</a></p>

    <hr>
    <p>Connecté en tant que <?= htmlspecialchars($_SESSION['prenom']) ?> | <a href="deconnexion.php">Déconnexion</a></p>
</body>
</html>