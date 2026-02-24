<?php
declare(strict_types=1);

class PDOFactory {

    public static function getMySQLConnection(): PDO {

        $configPath = __DIR__ . '/../config.local.php';

        if (!file_exists($configPath)) {
            die("Fichier config.local.php manquant.");
        }

        $config = require $configPath;

        try {
            $db = new PDO(
                $config['dsn'],
                $config['user'],
                $config['pass']
            );

            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $db;

        } catch (PDOException $e) {
            die("Erreur connexion BD : " . $e->getMessage());
        }
    }
}