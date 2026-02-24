<?php

class UtilisateurManager
{
    private PDO $db;

    public function __construct()
    {
        $this->db = PDOFactory::getMySQLConnection();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }
    public function getSolde(int $idUtilisateur): float
    {
        $sql = "SELECT solde
                FROM utilisateurs
                WHERE id_utilisateur = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUtilisateur]);
        $row = $stmt->fetch();

        if ($row === false) {
            return 0.0;
        }

        return $row['solde'];
    }

    public function setSolde(int $idUtilisateur, float $nouveauSolde): void
    {
        $sql = "UPDATE utilisateurs
                SET solde = :solde
                WHERE id_utilisateur = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':solde' => $nouveauSolde,
            ':id'    => $idUtilisateur
        ]);
    }

    public function debiterSolde(int $idUtilisateur, float $montant): void
    {
        $sql = "UPDATE utilisateurs
                SET solde = solde - :montant
                WHERE id_utilisateur = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':montant' => $montant,
            ':id'      => $idUtilisateur
        ]);
    }

    public function crediterSolde(int $idUtilisateur, float $montant): void
    {
        $sql = "UPDATE utilisateurs
                SET solde = solde + :montant
                WHERE id_utilisateur = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':montant' => $montant,
            ':id'      => $idUtilisateur
        ]);
    }

     public function getUtilisateurParCourriel(string $courriel): ?array {
        $sql = "SELECT id_utilisateur, nom_utilisateur, courriel, mot_de_passe
                FROM utilisateurs
                WHERE courriel = :courriel
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':courriel' => $courriel]);

        $user = $stmt->fetch();
        return $user ? $user : null;
    }

    public function verifierConnexion(string $courriel, string $motDePasse): ?array {

        $user = $this->getUtilisateurParCourriel($courriel);
        if (!$user) return null;

        if ($motDePasse !== $user['mot_de_passe']) return null;

        return $user;
    }
}
?>