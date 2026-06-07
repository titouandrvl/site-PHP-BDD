<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
include('config.php');

$message_erreur = '';
$message_succes = '';

//traitement du form uniqueement si soumis en POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //verifie que les champs sont remplies comme demandé
    if (isset($_POST['nom'], $_POST['prenom'], $_POST['mail'], $_POST['mot_de_passe'], $_POST['confirmer_mot_de_passe'], $_POST['genre'], $_POST['date_naissance'], $_POST['ville'], $_POST['role'])) {
        //nettoyage des champs
        $nom = trim(strip_tags($_POST['nom']));
        $prenom = trim(strip_tags($_POST['prenom']));
        $mail = trim(strip_tags($_POST['mail']));
        $mot_de_passe = trim($_POST['mot_de_passe']);
        $confirmer_mot_de_passe = trim($_POST['confirmer_mot_de_passe']);
        $genre = trim(strip_tags($_POST['genre']));
        $date_naissance = trim(strip_tags($_POST['date_naissance']));
        $ville = trim(strip_tags($_POST['ville']));
        $role = isset($_POST['role']) && $_POST['role'] === '1' ? 1 : 0; //seul ces valeurs sont acceptées

        //validation format mail
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $message_erreur = "Adresse email invalide.";
            //verification cohérence des mdp
        } elseif ($mot_de_passe !== $confirmer_mot_de_passe) {
            $message_erreur = "Les mots de passe ne correspondent pas.";
        } else {
            //verification aucun compte existe deja
            $stmt = $pdo->prepare("SELECT id_utilisateur FROM sae203_utilisateur WHERE mail = ?");
            $stmt->execute([$mail]);
            $user = $stmt->fetch();

            if ($user) {
                $message_erreur = "Cet email est déjà utilisé.";
            } else {
                $password_hash = sha1($mot_de_passe);
                $stmt = $pdo->prepare("INSERT INTO sae203_utilisateur (nom, prenom, password_hash, mail, genre, date_naissance, ville, role) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$nom, $prenom, $password_hash, $mail, $genre, $date_naissance, $ville, $role])) {
                    $message_succes = "Compte créé avec succès ! Vous pouvez vous connecter.";
                header('Location: connexion.php');
                exit;
                    } else {
                    $message_erreur = "Erreur lors de l'inscription.";
                }
            }
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
    <title>Inscription</title>
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

    <h1>Inscription</h1>

    <?php if (!empty($message_erreur)) : ?>
        <p class="msg-erreur"><?= htmlspecialchars($message_erreur) ?></p>
    <?php endif; ?>
    <?php if (!empty($message_succes)) : ?>
        <p class="msg-succes"><?= htmlspecialchars($message_succes) ?></p>
    <?php endif; ?>

    <form action="inscription.php" method="POST">
        <p><label for="nom">Nom :</label>
        <input type="text" id="nom" name="nom" required></p>

        <p><label for="prenom">Prénom :</label>
        <input type="text" id="prenom" name="prenom" required></p>

        <p><label for="mail">Email :</label>
        <input type="email" id="mail" name="mail" required></p>

        <p><label for="mot_de_passe">Mot de passe :</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required></p>

        <p><label for="confirmer_mot_de_passe">Confirmer le mot de passe :</label>
        <input type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" required></p>

        <p><label for="genre">Genre :</label>
        <select id="genre" name="genre" required>
            <option value="Féminin">Féminin</option>
            <option value="Masculin">Masculin</option>
        </select></p>

        <p><label for="date_naissance">Date de naissance :</label>
        <input type="date" id="date_naissance" name="date_naissance" required></p>

        <p><label for="ville">Ville :</label>
        <input type="text" id="ville" name="ville" required></p>

        <p><label for="role">Rôle :</label>
        <select id="role" name="role" required>
            <option value="0">Participant</option>
            <option value="1">Organisateur</option>
        </select></p>

        <button type="submit">S'inscrire</button>
    </form>

    <p>Déjà un compte ? <a href="connexion.php">Se connecter</a></p>
</body>
</html>