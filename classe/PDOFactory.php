<?php
/**
 * classe/PDOFactory.php
 *
 * Fabrique de connexion PDO pour la boutique VortexVR.
 *
 * Rôle dans le projet :
 *   Fournit un point d'entrée unique et centralisé pour obtenir une
 *   connexion PDO à la base de données MySQL. Tous les Managers
 *   (ClientManager, CasqueManager, PanierManager…) appellent
 *   PDOFactory::getMySQLConnection() dans leur constructeur.
 *
 *   Cela respecte le principe DRY (Don't Repeat Yourself) : la
 *   configuration de connexion n'est définie qu'en un seul endroit
 *   (config.local.php).
 *
 * Configuration attendue dans config.local.php :
 *   return [
 *       'dsn'  => 'mysql:host=db;port=3306;dbname=boutique_casques_vr;charset=utf8',
 *       'user' => 'root',
 *       'pass' => '',
 *   ];
 *
 * @project VortexVR – Boutique de casques VR
 */

declare(strict_types=1);

class PDOFactory
{
    /**
     * Crée et retourne une connexion PDO à la base de données MySQL.
     *
     * Configure PDO en mode exception (ERRMODE_EXCEPTION) afin que toute
     * erreur SQL lève automatiquement une PDOException au lieu d'échouer
     * silencieusement.
     *
     * @return PDO Instance de connexion prête à l'emploi.
     * @throws void En cas d'erreur, die() est appelé (usage en dev/prod simple).
     */
    public static function getMySQLConnection(): PDO
    {
        // Chemin absolu vers le fichier de configuration local (non versionné).
        $configPath = __DIR__ . '/../config.local.php';

        // Vérification de l'existence du fichier avant toute tentative de connexion.
        if (!file_exists($configPath)) {
            die("Fichier config.local.php manquant.");
        }

        // Chargement du tableau de configuration (retourné par config.local.php).
        $config = require $configPath;

        try {
            // Instanciation de la connexion PDO avec le DSN, l'utilisateur et le mot de passe.
            $db = new PDO(
                $config['dsn'],
                $config['user'],
                $config['pass']
            );

            // Mode exception : toute erreur SQL lève une PDOException.
            // Indispensable pour pouvoir attraper les erreurs avec try/catch.
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $db;

        } catch (PDOException $e) {
            // Erreur critique de connexion (hôte inaccessible, identifiants incorrects…).
            die("Erreur connexion BD : " . $e->getMessage());
        }
    }
}