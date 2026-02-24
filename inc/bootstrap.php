<?php
declare(strict_types=1);

// Démarrer la session une seule fois
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Charger le fichier .env (si présent à la racine du projet)
$envPath = __DIR__ . '/../.env';

if (file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Ignorer commentaires et lignes vides
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Format attendu : KEY="VALUE" ou KEY=VALUE
        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);

        // Enlever guillemets éventuels
        $value = trim($value, "\"'");

        // Définir variable d'environnement
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
}