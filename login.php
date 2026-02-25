<?php
/**
 * login.php
 *
 * Affiche le formulaire de connexion de la boutique VortexVR.
 *
 * Flux :
 *   1. L'utilisateur saisit son nom d'utilisateur et son mot de passe.
 *   2. Le formulaire est soumis en POST vers traitement.php (action=login).
 *   3. pretraitement.php vérifie les identifiants :
 *      - Succès → redirection vers index.php (session initialisée).
 *      - Échec  → stockage de login_error en session + retour sur login.php.
 *   4. Si login_error existe en session, ce fichier l'affiche puis l'efface.
 *
 * @project VortexVR – Boutique de casques VR
 */

// Chargement de l'en-tête HTML commun (session, autoloader, CSS, nav).
require_once "inc/header.php";
?>

<h1 class="center">
    Entrez votre utilisateur et mot de passe <br>
    pour accéder aux fonctionnalités
</h1>

<?php
// Affichage du message d'erreur de connexion s'il en existe un en session.
// Ce message est positionné par pretraitement.php lors d'un échec de login.
if (!empty($_SESSION['login_error'])): ?>
    <p class="login-error">
        <?= htmlspecialchars($_SESSION['login_error']) ?>
    </p>
    <?php
    // On supprime immédiatement le message après affichage pour éviter
    // qu'il ne réapparaisse lors d'un rechargement de page.
    unset($_SESSION['login_error']);
endif; ?>

<div class="login-section">
    <!-- Formulaire de connexion : soumis vers traitement.php -->
    <form class="login" action="traitement.php" method="post">

        <label for="username">Nom d'utilisateur :</label>
        <input type="text" name="username" id="username" required>

        <label for="password">Mot de passe :</label>
        <input type="password" name="pass" id="password" required>

        <!-- Champ caché indiquant à traitement.php/pretraitement.php
             quelle action déclencher (login, logout ou register). -->
        <input type="hidden" name="action" value="login">

        <button type="submit" class="rectangle-button">Se connecter</button>
    </form>
</div>

<?php
// Fermeture du <main>, affichage du pied de page HTML.
require_once "inc/footer.php";
?>