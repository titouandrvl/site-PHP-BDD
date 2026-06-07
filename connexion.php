<?php
ini_set('display_errors', 1); // affichage des errreurs PHP
error_reporting(E_ALL);

session_start();
include('config.php');

// Redirection si déjà connecté (si orga ou participant)
if (isset($_SESSION['utilisateur_id'])) {
    if ($_SESSION['role'] == 1) {
        header('Location: admin_dashboard.php'); //orga
    } else {
        header('Location: index.php'); //participant
    }
    exit;
}

$message = "";

//Verifie que le formulaire a bien ete soumis
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    //verifie si les champs sont pas vides
    if (!empty($_POST['mail']) && !empty($_POST['mot_de_passe'])) {
        $mail = strip_tags(trim($_POST['mail'])); //supprime les balises HTML et espaces inutiels
        $mot_de_passe = trim($_POST['mot_de_passe']);
        //validation du format de l'email
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            $message = "Adresse email invalide.";
        } else {
            //eviete les injections SQL
            $query = "SELECT * FROM sae203_utilisateur WHERE mail = ?";
            $stmt = $pdo->prepare($query);
            $stmt->execute([$mail]);
            $utilisateur = $stmt->fetch(); //recupere l'utilisateur trouvé

            //verification de la cohérence des données
            if ($utilisateur && $utilisateur['password_hash'] === sha1($mot_de_passe)) {
                //connexion reussi. on stocke les infos dans la session
                $_SESSION['authentification'] = "OK";
                $_SESSION['utilisateur_id'] = $utilisateur['id_utilisateur'];
                $_SESSION['nom'] = $utilisateur['nom'];
                $_SESSION['prenom'] = $utilisateur['prenom'];
                $_SESSION['mail'] = $utilisateur['mail'];
                $_SESSION['role'] = $utilisateur['role'];

                //redirection selon les roles
                if ($utilisateur['role'] == 1) {
                    header('Location: admin_dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            } else {
                //si mdp incorect
                $message = "Email ou mot de passe incorrect.";
            }
        }
    } else {
        $message = "Veuillez remplir tous les champs.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
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

    <h1>Connexion</h1>

    <?php if (!empty($message)) : ?>
        <p class="msg-erreur"><?= htmlspecialchars($message) ?></p>    <?php endif; ?>

    <form action="connexion.php" method="POST">
        <p>
            <label for="mail">Email :</label>
            <input type="email" id="mail" name="mail" required>
        </p>
        <p>
            <label for="mot_de_passe">Mot de passe :</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" required>
        </p>
        <p>
            <button type="submit">Se connecter</button>
        </p>
    </form>

    <p>Pas encore inscrit ? <a href="inscription.php">Créez un compte</a></p>
</body>
</html>