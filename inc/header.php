<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php include_once("inc/autoloader.php"); ?>

<!DOCTYPE html>
<html lang="fr-ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VortexVR</title>
    <link rel="stylesheet" href="css/style.css">
    <script src='js/script.js' defer></script>
</head>

<body>

<header class="site-header">
    <div class="container header-inner">

        <h1 class="site-title">
            <a href="index.php">Vortex <span>VR</span></a>
        </h1>

        <a href="index.php" class="logo-image">
            <img src="images/favicon.png" alt="Logo Vortex VR">
        </a>

        <nav class="main-nav">
            <ul>
                <li><a href="catalogue.php">Catalogue</a></li>
   <?php if (!empty($_SESSION['nom_utilisateur'])): ?>
        <li><a href="panier.php">Panier</a></li>
        <li><a href="creation_casque.php">Créer un casque</a></li>
        <a href="compte.php">Compte</a>
        <a href="traitement.php?action=logout">Se déconnecter</a>
        <li class="user-info">
            Bienvenu, <?= htmlspecialchars($_SESSION['nom_utilisateur']) ?>
        </li>
    <?php else: ?>
        <a href="register.php">Inscription</a>
        <a href="login.php">Se connecter</a>
    <?php endif; ?>
            </ul>
        </nav>

    </div>
</header>

<main class="site-main">