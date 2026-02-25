<?php
/**
 * config.example.php
 *
 * Fichier de configuration exemple (modèle) pour la connexion BDD de VortexVR.
 *
 * Rôle dans le projet :
 *   Ce fichier est versionné dans Git et sert de gabarit pour créer
 *   config.local.php. Il contient des valeurs fictives (credentials
 *   de développement Docker) sans jamais exposer de mot de passe réel.
 *
 * Comment l'utiliser :
 *   1. Copier ce fichier : cp config.example.php config.local.php
 *   2. Modifier config.local.php avec vos vraies valeurs.
 *   3. Ne JAMAIS committer config.local.php (il est dans .gitignore).
 *
 * Structure du tableau retourné :
 *   - 'dsn'  : chaîne DSN PDO (driver, hôte, port, nom de la BDD, charset)
 *   - 'user' : nom d'utilisateur MySQL
 *   - 'pass' : mot de passe MySQL (vide par défaut dans Docker)
 *
 * Ce fichier est chargé par PDOFactory::getMySQLConnection() via :
 *   $config = require __DIR__ . '/../config.local.php';
 *
 * @project VortexVR – Boutique de casques VR
 */
return [
    // DSN PDO pour MySQL via Docker :
    //   host=db   → nom du service Docker (défini dans docker-compose.yml)
    //   port=3306 → port MySQL standard
    //   dbname=boutique_casques_vr → nom de la base de données du projet
    //   charset=utf8 → encodage des caractères
    'dsn'  => 'mysql:host=db;port=3306;dbname=boutique_casques_vr;charset=utf8',

    // Utilisateur MySQL (root par défaut dans l'image Docker MySQL officielle).
    'user' => 'root',

    // Mot de passe MySQL (vide par défaut dans le conteneur de développement).
    // À remplacer par un mot de passe sécurisé en production.
    'pass' => ''
];