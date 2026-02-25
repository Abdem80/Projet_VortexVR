<?php
/**
 * inc/pretraitement.php
 *
 * Mini-routeur d'actions de session pour la boutique VortexVR.
 * Gère les actions "login" (connexion) et "logout" (déconnexion).
 *
 * Ce fichier est inclus en premier dans traitement.php, avant tout
 * output HTML. Il émet systématiquement un header() + exit() afin
 * que la page appelante ne génère aucun HTML pour ces deux actions.
 *
 * Variables de session écrites lors d'un login réussi :
 *   - $_SESSION['courriel']        : courriel de l'utilisateur connecté
 *   - $_SESSION['id_utilisateur']  : identifiant numérique en base de données
 *   - $_SESSION['nomComplet']      : prénom + nom concaténés
 *   - $_SESSION['nom_utilisateur'] : pseudonyme affiché dans la nav
 *
 * @project VortexVR – Boutique de casques VR
 */

// Chargement des classes nécessaires à la vérification des identifiants.
include_once __DIR__ . "/../classe/clientManager.php";
include_once __DIR__ . "/../classe/client.php";

// Instanciation du gestionnaire de clients (accès BDD via PDO).
$clientManager = new ClientManager();

// Lecture de l'action demandée (POST ou GET selon le contexte).
if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    switch ($action) {

        // -------------------------------------------------------
        // CAS : connexion d'un utilisateur existant
        // -------------------------------------------------------
        case "login":
            // Récupération et nettoyage des champs du formulaire.
            $username = trim($_REQUEST['username'] ?? '');
            $password = $_REQUEST['pass'] ?? '';

            // Vérification minimale : les deux champs doivent être remplis.
            if ($username && $password) {
                try {
                    // Recherche de l'utilisateur par nom d'utilisateur ou courriel.
                    // clientExists() retourne un objet Client si les identifiants
                    // sont valides (password_verify), ou false sinon.
                    $client = $clientManager->clientExists($username, $password);

                    if ($client) {
                        // Login réussi : on remplit la session avec les données du client.
                        $_SESSION['courriel']        = $client->get_courriel();
                        $_SESSION['id_utilisateur']  = $clientManager->getIdByCourriel($_SESSION['courriel']);
                        $_SESSION['nomComplet']       = $client->get_prenom() . ' ' . $client->get_nom();
                        $_SESSION['nom_utilisateur']  = $client->get_username();

                        // Redirection vers l'accueil. exit() est obligatoire après header().
                        header("Location: index.php");
                        exit();
                    } else {
                        // Identifiants incorrects : on stocke le message d'erreur en session
                        // pour l'afficher sur la page de login après redirection.
                        $_SESSION['login_error'] = "Nom d'utilisateur ou mot de passe incorrect.";
                        header("Location: login.php");
                        exit();
                    }

                } catch (Exception $e) {
                    // Erreur inattendue (ex. : panne BDD) : afficher un message générique.
                    $_SESSION['login_error'] = "Il y a une erreur lors de la connexion : " . $e->getMessage();
                    header("Location: login.php");
                    exit();
                }
            } else {
                // Champs vides : rappel de remplir le formulaire.
                $_SESSION['login_error'] = "Veuillez remplir tous les champs.";
                header("Location: login.php");
                exit();
            }

        // -------------------------------------------------------
        // CAS : déconnexion de l'utilisateur courant
        // -------------------------------------------------------
        case "logout":
            // Suppression de toutes les variables de session en mémoire.
            session_unset();

            // Destruction du fichier/cookie de session côté serveur.
            session_destroy();

            // Retour à la page d'accueil après déconnexion.
            header("Location: index.php");
            exit();
    }
}
?>
