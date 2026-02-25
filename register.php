<?php
/**
 * register.php
 *
 * Affiche le formulaire d'inscription à la boutique VortexVR.
 *
 * Le formulaire soumet les données en POST vers traitement.php,
 * qui instancie un objet Client, l'insère en BDD (via ClientManager)
 * et affiche une page de confirmation avec CSS et bouton de connexion.
 *
 * Champs obligatoires : nom, prénom, nom d'utilisateur, courriel, mot de passe.
 * Champs optionnels   : pays, adresse, ville, téléphone, argent initial.
 *
 * @project VortexVR – Boutique de casques VR
 */

// Chargement de l'en-tête HTML commun (session, autoloader, CSS, nav).
require_once "inc/header.php";
?>

<!-- Conteneur principal centré avec mise en page flex -->
<div class="container form-container">
    <div class="form-wrapper">
        <h2 class="center">Création de compte</h2>

        <!-- Formulaire d'inscription : action vers traitement.php (sans champ action caché,
             pretraitement.php ne réagit que si $_REQUEST['action'] est défini) -->
        <form action="traitement.php" method="post" class="styled-form">

            <!-- Nom de famille -->
            <div class="form-row">
                <label for="nom">Nom :</label>
                <input id="nom" type="text" name="nom" required>
            </div>

            <!-- Prénom -->
            <div class="form-row">
                <label for="prenom">Prénom :</label>
                <input id="prenom" type="text" name="prenom" required>
            </div>

            <!-- Pseudonyme unique (utilisé pour l'authentification) -->
            <div class="form-row">
                <label for="username">Nom d'utilisateur :</label>
                <input id="username" type="text" name="username" required>
            </div>

            <!-- Pays (optionnel) -->
            <div class="form-row">
                <label for="pays">Pays :</label>
                <input id="pays" type="text" name="pays">
            </div>

            <!-- Adresse postale (optionnel) -->
            <div class="form-row">
                <label for="adresse">Adresse :</label>
                <input id="adresse" type="text" name="adresse">
            </div>

            <!-- Solde initial du wallet (optionnel, min 0, pas de 0.01$) -->
            <div class="form-row">
                <label for="argent">Argent dans le compte :</label>
                <input id="argent" type="number" min="0" name="argent" step="0.01">
            </div>

            <!-- Ville (optionnel) -->
            <div class="form-row">
                <label for="ville">Ville :</label>
                <input id="ville" type="text" name="ville">
            </div>

            <!-- Téléphone (optionnel, format 000-000-0000 imposé par pattern HTML) -->
            <div class="form-row">
                <label for="telephone">Téléphone :</label>
                <input id="telephone" type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" name="telephone">
            </div>

            <!-- Courriel (utilisé comme identifiant alternatif lors du login) -->
            <div class="form-row">
                <label for="email">Email :</label>
                <input id="email" type="email" name="email" required>
            </div>

            <!-- Mot de passe : haché avec password_hash() dans ClientManager::addClient() -->
            <div class="form-row">
                <label for="motdepasse">Mot de passe :</label>
                <input id="motdepasse" type="password" name="motdepasse" required>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-submit">Créer le compte</button>
            </div>
        </form>

        <!-- Lien alternatif pour les utilisateurs qui ont déjà un compte -->
        <div class="login-box">
            <p>Vous avez déjà un compte ?</p>
            <a class="btn-submit" href="login.php">Se connecter</a>
        </div>
    </div>
</div>

<?php
// Fermeture du <main>, affichage du pied de page HTML.
require_once "inc/footer.php";
?>