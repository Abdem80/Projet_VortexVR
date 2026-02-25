<?php
/**
 * inc/header.php
 *
 * En-tête commun inclus en début de chaque page de la boutique VortexVR.
 * Responsabilités :
 *   1. Démarrer (ou reprendre) la session PHP.
 *   2. Charger l'autoloader pour rendre toutes les classes disponibles.
 *   3. Générer la structure HTML de base (DOCTYPE, <head>, barre de navigation).
 *
 * La barre de navigation est conditionnelle :
 *   - Utilisateur NON connecté : liens "Inscription" et "Se connecter".
 *   - Utilisateur connecté (présence de $_SESSION['nom_utilisateur']) :
 *     liens Panier, Créer un casque, Compte, Se déconnecter + nom d'utilisateur.
 *
 * @project VortexVR – Boutique de casques VR
 */

// Démarrage de la session uniquement si elle n'est pas déjà active.
// Évite l'erreur "session already started" quand header.php est inclus
// après pretraitement.php (qui démarre lui-même la session).
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php
// Chargement de l'autoloader : enregistre spl_autoload_register afin que
// toutes les classes du dossier classe/ soient disponibles sans require_once manuels.
include_once("inc/autoloader.php");
?>

<!DOCTYPE html>
<html lang="fr-ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VortexVR</title>

    <!-- Feuille de style principale (chemin relatif à la racine du projet) -->
    <link rel="stylesheet" href="css/style.css">

    <!-- Script JS chargé en différé pour ne pas bloquer le rendu de la page -->
    <script src='js/script.js' defer></script>
</head>

<body>

<!-- =====================================================
     EN-TÊTE DU SITE : logo + titre + navigation principale
     ===================================================== -->
<header class="site-header">
    <div class="container header-inner">

        <!-- Titre / logo texte cliquable vers l'accueil -->
        <h1 class="site-title">
            <a href="index.php">Vortex <span>VR</span></a>
        </h1>

        <!-- Logo image cliquable vers l'accueil -->
        <a href="index.php" class="logo-image">
            <img src="images/favicon.png" alt="Logo Vortex VR">
        </a>

        <!-- Navigation principale : contenu différent selon l'état de connexion -->
        <nav class="main-nav">
            <ul>
                <!-- Lien catalogue toujours visible -->
                <li><a href="catalogue.php">Catalogue</a></li>

                <?php if (!empty($_SESSION['nom_utilisateur'])): ?>
                    <!-- Liens reservés aux utilisateurs connectés -->
                    <li><a href="panier.php">Panier</a></li>
                    <li><a href="creation_casque.php">Créer un casque</a></li>
                    <li><a href="compte.php">Compte</a></li>

                    <!-- Déclenchement du logout via GET action=logout dans traitement.php -->
                    <li><a href="traitement.php?action=logout">Se déconnecter</a></li>

                    <!-- Affichage du pseudonyme : htmlspecialchars protège contre le XSS -->
                    <li class="user-info">
                        Bienvenue, <?= htmlspecialchars($_SESSION['nom_utilisateur']) ?>
                    </li>
                <?php else: ?>
                    <!-- Liens visibles uniquement pour les visiteurs non connectés -->
                    <li><a href="register.php">Inscription</a></li>
                    <li><a href="login.php">Se connecter</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>
</header>

<!-- Ouverture du <main> : fermé dans footer.php -->
<main class="site-main">