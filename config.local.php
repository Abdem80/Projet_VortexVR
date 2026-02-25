<?php
/**
 * config.local.php
 *
 * Fichier de configuration LOCAL pour la connexion BDD de VortexVR.
 *
 * ⚠️  NE PAS VERSIONNER CE FICHIER DANS GIT.
 *     Il contient un mot de passe réel et est listé dans .gitignore.
 *     Utiliser config.example.php comme gabarit de référence.
 *
 * Rôle dans le projet :
 *   Ce fichier est chargé par PDOFactory::getMySQLConnection() :
 *     $config = require __DIR__ . '/../config.local.php';
 *
 *   Il retourne un tableau PHP associatif contenant les paramètres
 *   nécessaires pour instancier une connexion PDO vers la base de
 *   données MySQL du projet (conteneur Docker).
 *
 * Structure du tableau retourné :
 *   - 'dsn'  : chaîne DSN PDO (driver, hôte Docker, port, BDD, charset)
 *   - 'user' : nom d'utilisateur MySQL
 *   - 'pass' : mot de passe MySQL (confidentiel — ne pas partager)
 *
 * @project VortexVR – Boutique de casques VR
 */
return [
    // DSN PDO pour MySQL via Docker :
    //   host=db   → nom du service MySQL dans docker-compose.yml
    //   port=3306 → port MySQL standard
    //   dbname=boutique_casques_vr → base de données du projet
    //   charset=utf8 → encodage des caractères
    'dsn'  => 'mysql:host=db;port=3306;dbname=boutique_casques_vr;charset=utf8',

    // Utilisateur MySQL (root par défaut dans l'image MySQL Docker officielle).
    'user' => 'root',

    // Mot de passe MySQL du conteneur de développement local.
    // ⚠️  Ne pas exposer cette valeur publiquement.
    'pass' => 'f4q2DG2obVd3I'
];