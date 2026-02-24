<?php
session_start();
require_once "classe/clientManager.php";
require_once "classe/client.php";

$courriel = $_SESSION['courriel'] ?? '';

if (!$courriel) {
    exit("Aucun client connecté.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $champ = $_POST['champ'];
    $valeur = $_POST['nouvelle_valeur'];

    $clientManager = new ClientManager();

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
            $clientManager->updateArgent($courriel, floatval($valeur));
            break;
        default:
            exit("Champ invalide !");
    }

    header("Location: compte.php");
    exit;
}