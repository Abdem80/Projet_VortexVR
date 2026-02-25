<?php
/**
 * inc/bootstrap.php
 *
 * Point d'initialisation de l'application VortexVR.
 * Responsabilités :
 *   1. Démarrer la session PHP (si elle n'est pas déjà active).
 *   2. Charger les variables d'environnement depuis un fichier .env
 *      situé à la racine du projet (si ce fichier existe).
 *
 * Les variables d'environnement peuvent ensuite être lues via :
 *   - getenv('NOM_VAR')
 *   - $_ENV['NOM_VAR']
 *
 * Format attendu dans le fichier .env :
 *   NOM_VAR=valeur          # sans guillemets
 *   NOM_VAR="valeur"        # avec guillemets doubles
 *   NOM_VAR='valeur'        # avec guillemets simples
 *   # Ceci est un commentaire (ignoré)
 *
 * Ce fichier est conçu pour être inclus une seule fois, typiquement
 * depuis header.php qui est lui-même inclus en début de chaque page.
 *
 * @project VortexVR – Boutique de casques VR
 */

declare(strict_types=1);

// Démarrage de la session uniquement si elle n'est pas encore active.
// Évite l'erreur "Cannot send session cache limiter" si la session
// est déjà démarrée par un fichier inclus précédemment.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Chemin absolu vers le fichier .env à la racine du projet.
// __DIR__ retourne le dossier de ce fichier (inc/), on remonte d'un niveau.
$envPath = __DIR__ . '/../.env';

// Chargement du fichier .env uniquement s'il existe (pas d'erreur en production).
if (file_exists($envPath)) {
    // Lecture du fichier ligne par ligne, en ignorant les lignes vides.
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Ignorer les lignes vides et les commentaires (qui commencent par #).
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Découpage au premier "=" pour séparer la clé de la valeur.
        // Le paramètre 2 limite à 2 parties (la valeur peut contenir des "=").
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue; // Ligne mal formée : on l'ignore silencieusement.
        }

        $key   = trim($parts[0]);

        // Suppression des guillemets simples ou doubles encadrant la valeur.
        $value = trim(trim($parts[1]), "\"'");

        // Injection dans deux emplacements pour compatibilité maximale :
        // - putenv()  : accessible via getenv()
        // - $_ENV[]   : accessible directement via le tableau superglobal
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}