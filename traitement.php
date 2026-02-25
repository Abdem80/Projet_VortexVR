<?php
/**
 * traitement.php
 *
 * Contrôleur central de VortexVR. Gère deux flux distincts :
 *
 * 1. login / logout (via inc/pretraitement.php) :
 *    pretraitement.php intercepte ces actions et émet un header() + exit()
 *    AVANT tout output HTML. Le script s'arrête donc ici pour ces cas.
 *
 * 2. Inscription (register) :
 *    Si aucune action de session n'a été déclenchée, on charge le HTML
 *    (header.php) et on traite le formulaire d'inscription POST.
 *
 * @project VortexVR – Boutique de casques VR
 */

declare(strict_types=1);

// Démarrage de session si ce n'est pas déjà fait.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// pretraitement.php gère login et logout avec header() + exit().
// Il doit être inclus AVANT tout output HTML pour que les redirections fonctionnent.
include_once "inc/pretraitement.php";

// À ce stade : si l'action était "login" ou "logout", le script est déjà terminé.
// La suite ne s'exécute que pour l'inscription.

// Chargement de l'en-tête HTML commun (CSS, nav, balise <main>).
require_once "inc/header.php";

// Classes nécessaires à la création d'un compte client.
require_once "classe/client.php";
require_once "classe/clientManager.php";
?>

<div class="container">
    <h1 class="center">Traitement de l'inscription</h1>

    <?php
    // On traite uniquement les requêtes POST (soumission du formulaire register.php).
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // --- Récupération des données du formulaire ---
        // L'opérateur ?? garantit une valeur vide par défaut si le champ est absent.
        $nom       = $_POST['nom']       ?? '';
        $prenom    = $_POST['prenom']    ?? '';
        $username  = $_POST['username']  ?? '';
        $pays      = $_POST['pays']      ?? '';
        $adresse   = $_POST['adresse']   ?? '';
        $argent    = $_POST['argent']    ?? 0;
        $ville     = $_POST['ville']     ?? '';
        $telephone = $_POST['telephone'] ?? '';
        $courriel  = $_POST['email']     ?? '';
        $password  = $_POST['motdepasse'] ?? '';

        // Création de l'objet Client à partir des données saisies.
        // Le constructeur reçoit un tableau associatif de propriétés.
        $client = new Client([
            'nom'      => $nom,
            'prenom'   => $prenom,
            'username' => $username,
            'pays'     => $pays,
            'adresse'  => $adresse,
            'argent'   => $argent,
            'ville'    => $ville,
            'tel'      => $telephone,
            'courriel' => $courriel,
            'pass'     => $password   // le mot de passe sera haché dans addClient()
        ]);

        // Gestionnaire responsable de l'insertion en base de données.
        $clientManager = new ClientManager();

        try {
            // Insertion du client en BDD. Lance une Exception en cas d'échec
            // (ex. : doublon de nom d'utilisateur ou de courriel).
            $idClient = $clientManager->addClient($client);

            // Initialisation de la session pour connecter automatiquement
            // l'utilisateur juste après la création de son compte.
            $_SESSION['nom_utilisateur'] = $client->get_username();
            $_SESSION['prenom']          = $client->get_prenom();
            $_SESSION['argent']          = $client->get_argent();

            // --- Confirmation visuelle : succès ---
            echo "<h2 class='center message-success'>Compte créé avec succès ✅</h2>";
            echo "<p class='center'>Vous pouvez maintenant vous connecter.</p>";

            // Bouton de redirection vers la page de connexion.
            echo "<div class='center actions'>";
            echo "  <a class='rectangle-button' href='login.php'>Aller à la page de connexion</a>";
            echo "</div>";
            ?>

            <!-- Récapitulatif des informations du compte créé -->
            <ul class="traitement">

                <!-- Section : informations de profil -->
                <li><strong>Profil</strong>
                    <ul>
                        <!-- htmlspecialchars protège contre les injections XSS -->
                        <li>Prénom : <?= htmlspecialchars($client->get_prenom()) ?></li>
                        <li>Nom : <?= htmlspecialchars($client->get_nom()) ?></li>
                        <li>Nom d'utilisateur : <?= htmlspecialchars($client->get_username()) ?></li>
                        <li>Courriel : <?= htmlspecialchars($client->get_courriel()) ?></li>
                    </ul>
                </li>

                <!-- Section : coordonnées géographiques -->
                <li><strong>Coordonnées</strong>
                    <ul>
                        <li>Pays : <?= htmlspecialchars($client->get_pays()) ?></li>
                        <li>Adresse : <?= htmlspecialchars($client->get_adresse()) ?></li>
                        <li>Ville : <?= htmlspecialchars($client->get_ville()) ?></li>
                        <li>Téléphone : <?= htmlspecialchars($client->get_tel()) ?></li>
                    </ul>
                </li>

                <!-- Section : solde du compte (formaté à 2 décimales) -->
                <li><strong>Compte</strong>
                    <ul>
                        <li>Argent disponible : <?= number_format((float)$client->get_argent(), 2) ?> $</li>
                    </ul>
                </li>
            </ul>

            <?php

        } catch (Exception $e) {
            // Erreur lors de l'insertion (ex. doublon, contrainte BDD).
            // Le message de l'exception est affiché de façon sécurisée.
            echo "<h2 class='center message-error'>Erreur lors de la création du compte</h2>";
            echo "<p class='center'>" . htmlspecialchars($e->getMessage()) . "</p>";
        }

    } else {
        // Accès direct à traitement.php sans soumission de formulaire.
        echo "<p class='center'>Aucune donnée reçue.</p>";
    }
    ?>
</div>

<?php
// Fermeture du <main>, affichage du pied de page HTML.
require_once "inc/footer.php";