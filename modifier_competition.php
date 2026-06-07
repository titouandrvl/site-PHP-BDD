<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include('config.php');

// Vérification connexion et rôle organisateur
if (!isset($_SESSION['utilisateur_id'])) {
    header('Location: connexion.php');
    exit;
}

if ($_SESSION['role'] != 1) {
    header('Location: index.php');
    exit;
}

// Vérification de l'id dans l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit;
}
//conversion en entier pour securiser la valeur
$id_competition = (int)$_GET['id'];

// Récupération de la compétition et vérification qu'elle appartient bien à cet organisateur
$stmt = $pdo->prepare("SELECT * FROM sae203_competition WHERE id_competition = ? AND organisateur_id = ?");
$stmt->execute([$id_competition, $_SESSION['utilisateur_id']]);
$competition = $stmt->fetch();

// Si la compétition n'existe pas ou n'appartient pas à cet organisateur
if (!$competition) {
    header('Location: admin_dashboard.php');
    exit;
}

$message_erreur = '';
$message_succes = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['lieu'], $_POST['date'], $_POST['description'], $_POST['categorie'], $_POST['nb_participant'])) {
        $lieu = trim(strip_tags($_POST['lieu']));
        $date = trim(strip_tags($_POST['date']));
        $description = trim($_POST['description']);
        $categorie = trim(strip_tags($_POST['categorie']));
        $nb_participant = (int)$_POST['nb_participant'];

        // Calcul du nouveau nb_restant en fonction des inscrits actuels
        $stmt = $pdo->prepare("SELECT COUNT(*) AS nb_inscrits FROM sae203_inscription WHERE competition_id = ?");
        $stmt->execute([$id_competition]);
        $result = $stmt->fetch();
        $nb_inscrits = $result['nb_inscrits'];

        $nb_restant = $nb_participant - $nb_inscrits;
        if ($nb_restant < 0) $nb_restant = 0;

        //maj de la compete dans la BDD
        $stmt = $pdo->prepare("UPDATE sae203_competition SET lieu = ?, date = ?, description = ?, categorie = ?, nb_participant = ?, nb_restant = ? WHERE id_competition = ?");
        if ($stmt->execute([$lieu, $date, $description, $categorie, $nb_participant, $nb_restant, $id_competition])) {
            $message_succes = "Compétition modifiée avec succès !";
            $competition['lieu'] = $lieu;
            $competition['date'] = $date;
            $competition['description'] = $description;
            $competition['categorie'] = $categorie;
            $competition['nb_participant'] = $nb_participant;
            $competition['nb_restant'] = $nb_restant;
        } else {
            $message_erreur = "Erreur lors de la modification.";
        }
    } else {
        $message_erreur = "Tous les champs sont requis.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier la compétition</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <nav>
            <a href="admin_dashboard.php">Dashboard</a>
            <a href="resultats.php">Résultats</a>
            <a href="deconnexion.php">Déconnexion</a>
        </nav>
    </header>

    <h1>Modifier la compétition</h1>

    <?php if (!empty($message_erreur)) : ?>
        <p class="msg-erreur"><?= htmlspecialchars($message_erreur) ?></p>
    <?php endif; ?>
    <?php if (!empty($message_succes)) : ?>
        <p class="msg-succes"><?= htmlspecialchars($message_succes) ?></p>
    <?php endif; ?>

    <form action="modifier_competition.php?id=<?= $id_competition ?>" method="POST">

        <p><label for="lieu">Lieu :</label>
        <input type="text" id="lieu" name="lieu" value="<?= htmlspecialchars($competition['lieu']) ?>" required></p>

        <p><label for="date">Date :</label>
        <input type="datetime-local" id="date" name="date" value="<?= date('Y-m-d\TH:i', strtotime($competition['date'])) ?>" required></p>

        <p><label for="description">Description :</label><br>
        <textarea id="description" name="description" rows="6" cols="50"><?= htmlspecialchars($competition['description']) ?></textarea></p>

        <p><label for="categorie">Catégorie :</label>
        <select id="categorie" name="categorie" required>
            <option value="">-- Choisir --</option>
            <option value="Course à pieds" <?= $competition['categorie'] == 'Course à pieds' ? 'selected' : '' ?>>Course à pieds</option>
            <option value="Equipe" <?= $competition['categorie'] == 'Equipe' ? 'selected' : '' ?>>Equipe</option>
            <option value="Natation" <?= $competition['categorie'] == 'Natation' ? 'selected' : '' ?>>Natation</option>
        </select></p>

        <p><label for="nb_participant">Nombre de participants max :</label>
        <input type="number" id="nb_participant" name="nb_participant" min="1" value="<?= $competition['nb_participant'] ?>" required></p>

        <button type="submit">Enregistrer les modifications</button>
    </form>

    <p><a href="admin_dashboard.php">Retour au dashboard</a></p>

</body>
</html>