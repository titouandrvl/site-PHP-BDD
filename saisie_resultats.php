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

if ($_SESSION['role'] != 1) {
    header('Location: index.php');
    exit;
}

// Vérification de l'id dans l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$id_competition = (int)$_GET['id'];

// Vérification que la compétition appartient bien à cet organisateur
$stmt = $pdo->prepare("SELECT * FROM sae203_competition WHERE id_competition = ? AND organisateur_id = ?");
$stmt->execute([$id_competition, $_SESSION['utilisateur_id']]);
$competition = $stmt->fetch();

if (!$competition) {
    header('Location: admin_dashboard.php');
    exit;
}

$message_erreur = '';
$message_succes = '';

// Traitement du formulaire apres soumissions resultats
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // On reçoit des tableaux : chrono[id_inscription] et classement[id_inscription]
    if (isset($_POST['chrono']) && isset($_POST['classement'])) {
        $erreur = false;

        foreach ($_POST['chrono'] as $id_inscription => $chrono) {
            $id_inscription = (int)$id_inscription;
            $chrono = trim($chrono);
            $classement = trim($_POST['classement'][$id_inscription]);

            // Si les deux champs sont vides on passe
            if ($chrono === '' && $classement === '') {
                continue;
            }

            // Validation du chrono format HH:MM:SS
            if ($chrono !== '' && !preg_match('/^\d{2}:\d{2}:\d{2}$/', $chrono)) {
                $message_erreur = "Format de chrono invalide, utilisez HH:MM:SS.";
                $erreur = true;
                break;
            }

            // Validation classement
            if ($classement !== '' && !is_numeric($classement)) {
                $message_erreur = "Le classement doit être un nombre.";
                $erreur = true;
                break;
            }

            //Si vide, on stocke null
            $chrono_val = $chrono !== '' ? $chrono : null;
            $classement_val = $classement !== '' ? (int)$classement : null;

            $stmt = $pdo->prepare("UPDATE sae203_inscription SET chrono = ?, classement = ? WHERE id_inscription = ? AND competition_id = ?");
            $stmt->execute([$chrono_val, $classement_val, $id_inscription, $id_competition]);
        }

        if (!$erreur) {
            $message_succes = "Résultats enregistrés avec succès !";
        }
    }
}

// Récupération de tous les participants inscrits à cette compétition
$stmt = $pdo->prepare("
    SELECT i.id_inscription, i.chrono, i.classement, i.donnees_publiques,
           u.nom, u.prenom
    FROM sae203_inscription i
    JOIN sae203_utilisateur u ON i.utilisateur_id = u.id_utilisateur
    WHERE i.competition_id = ?
    ORDER BY i.classement ASC, u.nom ASC
");
$stmt->execute([$id_competition]);
$participants = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saisie des résultats</title>
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

    <h1>Saisie des résultats</h1>

    <div class="competition">
        <p><strong>Catégorie :</strong> <?= htmlspecialchars($competition['categorie']) ?></p>
        <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($competition['date'])) ?></p>
        <p><strong>Lieu :</strong> <?= htmlspecialchars($competition['lieu']) ?></p>
    </div>

    <?php if (!empty($message_erreur)) : ?>
        <p class="msg-erreur"><?= htmlspecialchars($message_erreur) ?></p>
    <?php endif; ?>
    <?php if (!empty($message_succes)) : ?>
        <p class="msg-succes"><?= htmlspecialchars($message_succes) ?></p>
    <?php endif; ?>

    <?php if (count($participants) == 0) : ?>
        <p>Aucun participant inscrit à cette compétition.</p>
    <?php else : ?>
        <form class="form-tableau" action="saisie_resultats.php?id=<?= $id_competition ?>" method="POST">
            <table>
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Chrono (HH:MM:SS)</th>
                    <th>Classement</th>
                    <th>Données publiques</th>
                </tr>
                <?php foreach ($participants as $participant) : ?>
                    <tr>
                        <td><?= htmlspecialchars($participant['nom']) ?></td>
                        <td><?= htmlspecialchars($participant['prenom']) ?></td>
                        <td>
                            //affiche une chaine vide si chrono null en BDD
                            <input
                                type="text"
                                name="chrono[<?= $participant['id_inscription'] ?>]"
                                value="<?= htmlspecialchars($participant['chrono'] ?? '') ?>"
                                placeholder="00:00:00"
                            >
                        </td>
                        <td>
                            <input
                                type="number"
                                name="classement[<?= $participant['id_inscription'] ?>]"
                                value="<?= $participant['classement'] ?? '' ?>"
                                min="1"
                            >
                        </td>
                        <td><?= $participant['donnees_publiques'] ? 'Oui' : 'Non' ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <br>
            <button type="submit">Enregistrer les résultats</button>
        </form>
    <?php endif; ?>

    <p><a href="admin_dashboard.php">Retour au dashboard</a></p>

</body>
</html>