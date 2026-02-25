<?php
/**
 * updateClient.php
 *
 * Contrôleur de mise à jour des informations du profil utilisateur.
 *
 * Rôle dans le projet :
 *   Reçoit les mini-formulaires POST de compte.php, chacun envoyant :
 *   - "champ"          : le nom de la colonne à mettre à jour
 *   - "nouvelle_valeur": la nouvelle valeur saisie par l'utilisateur
 *
 *   Ce fichier route la mise à jour vers la méthode ClientManager appropriée
 *   (updateNom, updatePrenom, updateEmail, etc.) puis redirige vers compte.php
 *   (patron PRG : Post/Redirect/Get) pour éviter la re-soumission.
 *
 * Sécurité :
 *   - Vérifie que l'utilisateur est connecté (courriel en session).
 *   - Valide que le champ reçu est dans la liste des cases autorisées (switch).
 *   - Arrête l'exécution immédiatement si le champ est invalide.
 *
 * @project VortexVR – Boutique de casques VR
 */

// Démarrage de la session (avant tout output et avant lecture de $_SESSION).
session_start();

// Autoloader et classes nécessaires.
require_once "classe/clientManager.php";
require_once "classe/client.php";

// Récupération du courriel de l'utilisateur connecté (identifiant de session).
$courriel = $_SESSION['courriel'] ?? '';

// Vérification de l'authentification : si non connecté, on arrête immédiatement.
if (!$courriel) {
    exit("Aucun client connecté.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Champ cible et nouvelle valeur envoyés par le mini-formulaire de compte.php.
    $champ  = $_POST['champ'];
    $valeur = $_POST['nouvelle_valeur'];

    $clientManager = new ClientManager();

    // Routage vers la méthode de mise à jour correspondant au champ reçu.
    // Chaque setter ne modifie qu'une seule colonne pour limiter la surface d'attaque.
    switch ($champ) {
        case 'nom':
            $clientManager->updateNom($courriel, $valeur);
            break;
        case 'prenom':
            $clientManager->updatePrenom($courriel, $valeur);
            break;
        case 'nom_utilisateur':
            $clientManager->updateNomUtilisateur($courriel, $valeur);
            break;
        case 'courriel':
            $clientManager->updateEmail($courriel, $valeur);
            break;
        case 'pays':
            $clientManager->updatePays($courriel, $valeur);
            break;
        case 'adresse':
            $clientManager->updateAdresse($courriel, $valeur);
            break;
        case 'ville':
            $clientManager->updateVille($courriel, $valeur);
            break;
        case 'telephone':
            $clientManager->updateTelephone($courriel, $valeur);
            break;
        case 'argent':
            // Cast en float pour la compatibilité avec la signature de updateArgent().
            $clientManager->updateArgent($courriel, floatval($valeur));
            break;
        default:
            // Champ non reconnu : arrêt immédiat pour éviter toute modification non prévue.
            exit("Champ invalide !");
    }

    // Patron PRG (Post/Redirect/Get) : redirection vers compte.php après modification.
    // Évite la re-soumission du formulaire si l'utilisateur rafraîchit la page.
    header("Location: compte.php");
    exit;
}