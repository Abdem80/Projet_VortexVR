<?php
/**
 * compte.php
 *
 * Page de gestion du profil utilisateur de la boutique VortexVR.
 *
 * Fonctionnement :
 *   1. Vérifie que l'utilisateur est connecté (via $_SESSION['courriel']).
 *      Si non connecté : message d'erreur + pied de page + exit.
 *   2. Charge les données du client depuis la BDD via ClientManager::showClientByCourriel().
 *   3. Affiche un formulaire CAPTCHA (validation côté JS dans script.js).
 *   4. Affiche les champs du profil dans #protectedContent (masqué par défaut).
 *      Le contenu est révélé par JS une fois le CAPTCHA validé.
 *   5. Chaque champ dispose d'un mini-formulaire POST indépendant vers updateClient.php
 *      qui met à jour un seul champ à la fois en BDD.
 *
 * @project VortexVR – Boutique de casques VR
 */

declare(strict_types=1);

// Chargement de l'en-tête HTML commun (session, autoloader, CSS, nav).
require_once "inc/header.php";
require_once "classe/clientManager.php";
require_once "classe/client.php";

// --- Vérification de l'authentification ---
// Si le courriel n'est pas en session, l'utilisateur n'est pas connecté.
$courriel = $_SESSION['courriel'] ?? '';
if ($courriel === '') {
    echo "<p class='center message-error'>Aucun client connecté.</p>";
    require_once "inc/footer.php";
    exit; // Arrêt immédiat : inutile d'afficher le formulaire.
}

// Chargement des données du client depuis la base de données.
$clientManager = new ClientManager();
$client        = $clientManager->showClientByCourriel($courriel);

/**
 * Raccourci d'échappement HTML pour l'affichage des valeurs du client.
 *
 * Utilise htmlspecialchars() pour prévenir les injections XSS et (string) cast
 * pour éviter les erreurs si la valeur est null.
 *
 * @param mixed $v Valeur à afficher.
 * @return string Valeur sécurisée pour l'affichage HTML.
 */
function h($v): string
{
    return htmlspecialchars((string)($v ?? ''));
}
?>

<section class="form-container">

    <!-- =====================================================
         SECTION CAPTCHA
         Le formulaire est soumis via JS (checkform).
         Si le CAPTCHA est correct, #protectedContent devient visible.
         ===================================================== -->
    <form onsubmit="return checkform(this);" class="formmargin">
        <div class="capbox">
            <!-- Zone de génération du CAPTCHA (remplie par script.js) -->
            <div id="CaptchaDiv"></div>

            <div class="capbox-inner">
                Type the number:<br>
                <!-- txtCaptcha (hidden) contient la valeur correcte générée par JS -->
                <input type="hidden" id="txtCaptcha">
                <input type="text" name="CaptchaInput" id="CaptchaInput" size="15">
            </div>
        </div>

        <br>
        <input type="submit" value="Valider le Captcha" class="subbutx3">
    </form>

    <hr>

    <!-- =====================================================
         SECTION PROTÉGÉE (masquée via .is-hidden)
         Révélée par JS uniquement après validation du CAPTCHA.
         ===================================================== -->
    <div id="protectedContent" class="is-hidden">
        <h2 class="center">Modifier les informations du compte</h2>

        <!-- Chaque bloc ci-dessous affiche la valeur actuelle du champ
             et propose un mini-formulaire pour la modifier.
             Le champ caché "champ" indique à updateClient.php
             quelle colonne mettre à jour en BDD. -->

        <!-- Nom de famille -->
        <p><strong>Nom :</strong> <?= h($client['nom'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="nom">
                <input type="text" name="nouvelle_valeur" placeholder="Nouveau nom">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <!-- Prénom -->
        <p><strong>Prénom :</strong> <?= h($client['prenom'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="prenom">
                <input type="text" name="nouvelle_valeur" placeholder="Nouveau prénom">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <!-- Nom d'utilisateur (pseudonyme) -->
        <p><strong>Nom d'utilisateur :</strong> <?= h($client['nom_utilisateur'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="nom_utilisateur">
                <input type="text" name="nouvelle_valeur" placeholder="Nouveau nom d'utilisateur">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <!-- Courriel -->
        <p><strong>Email :</strong> <?= h($client['courriel'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="courriel">
                <input type="email" name="nouvelle_valeur" placeholder="Nouvel email">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <!-- Pays -->
        <p><strong>Pays :</strong> <?= h($client['pays'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="pays">
                <input type="text" name="nouvelle_valeur" placeholder="Nouveau pays">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <!-- Adresse postale -->
        <p><strong>Adresse :</strong> <?= h($client['adresse'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="adresse">
                <input type="text" name="nouvelle_valeur" placeholder="Nouvelle adresse">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <!-- Ville -->
        <p><strong>Ville :</strong> <?= h($client['ville'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="ville">
                <input type="text" name="nouvelle_valeur" placeholder="Nouvelle ville">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <!-- Téléphone (format 000-000-0000 imposé par pattern HTML5) -->
        <p><strong>Téléphone :</strong> <?= h($client['telephone'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="telephone">
                <input type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" name="nouvelle_valeur" placeholder="000-000-0000">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <!-- Solde du wallet (min 0$, pas de 0.01$) -->
        <p><strong>Argent :</strong> <?= h($client['argent'] ?? '') ?> $</p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="argent">
                <input type="number" min="0" step="0.01" name="nouvelle_valeur" placeholder="Nouveau montant">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>
    </div>
</section>

<?php
// Fermeture du <main>, affichage du pied de page HTML.
require_once "inc/footer.php";
?>