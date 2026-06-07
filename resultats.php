<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include('config.php');

// Récupération de toutes les compétitions qui ont des résultats
//DISTINCT evite les doublons
$stmt = $pdo->prepare("
    SELECT DISTINCT c.id_competition, c.lieu, c.date, c.categorie, c.description
    FROM sae203_competition c
    JOIN sae203_inscription i ON c.id_competition = i.competition_id
    WHERE i.chrono IS NOT NULL AND i.classement IS NOT NULL
    ORDER BY c.date ASC
");
$stmt->execute();
$competitions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats</title>
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

    <h1>Résultats des compétitions</h1>

    <?php if (count($competitions) == 0) : ?>
        <p>Aucun résultat disponible pour le moment.</p>
    <?php else : ?>
        <?php foreach ($competitions as $competition) : ?>
            <div class="resultat-bloc">
                <p><strong>Catégorie :</strong> <?= htmlspecialchars($competition['categorie']) ?></p>
                <p><strong>Date :</strong> <?= date('d/m/Y à H:i', strtotime($competition['date'])) ?></p>
                <p><strong>Lieu :</strong> <?= htmlspecialchars($competition['lieu']) ?></p>

                <h3>Classement</h3>
                <table>
                    <tr>
                        <th>Classement</th>
                        <th>Nom</th>
                        <th>Prénom</th>
                        <th>Chrono</th>
                    </tr>

                    <?php
                    // Si connecté, on voit ses propres résultats même si donnees_publiques = 0
                    // + tous les résultats publics
                    if (isset($_SESSION['utilisateur_id'])) {
                        $stmt = $pdo->prepare("
                            SELECT i.chrono, i.classement, i.donnees_publiques,
                                   u.nom, u.prenom, u.id_utilisateur
                            FROM sae203_inscription i
                            JOIN sae203_utilisateur u ON i.utilisateur_id = u.id_utilisateur
                            WHERE i.competition_id = ?
                            AND i.chrono IS NOT NULL
                            AND (i.donnees_publiques = 1 OR i.utilisateur_id = ?)
                            ORDER BY i.classement ASC
                        ");
                        $stmt->execute([$competition['id_competition'], $_SESSION['utilisateur_id']]);
                    } else {
                        // Non connecté, uniquement les résultats publics mais ici, si pas co on a juste pas acces
                        $stmt = $pdo->prepare("
                            SELECT i.chrono, i.classement, i.donnees_publiques,
                                   u.nom, u.prenom, u.id_utilisateur
                            FROM sae203_inscription i
                            JOIN sae203_utilisateur u ON i.utilisateur_id = u.id_utilisateur
                            WHERE i.competition_id = ?
                            AND i.chrono IS NOT NULL
                            AND i.donnees_publiques = 1
                            ORDER BY i.classement ASC
                        ");
                        $stmt->execute([$competition['id_competition']]);
                    }
                    $resultats = $stmt->fetchAll();
                    ?>

                    <?php if (count($resultats) == 0) : ?>
                        <tr><td colspan="4">Aucun résultat public disponible.</td></tr>
                    <?php else : ?>
                        <?php foreach ($resultats as $resultat) : ?>
                            <tr <?= (isset($_SESSION['utilisateur_id']) && $resultat['id_utilisateur'] == $_SESSION['utilisateur_id']) ? 'class="mon-resultat"' : '' ?>>
                                <td><?= $resultat['classement'] ?></td>
                                <td><?= htmlspecialchars($resultat['nom']) ?></td>
                                <td><?= htmlspecialchars($resultat['prenom']) ?></td>
                                <td><?= htmlspecialchars($resultat['chrono']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>

</body>
</html>