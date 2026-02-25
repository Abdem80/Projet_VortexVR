<?php
/**
 * classe/UtilisateurManager.php
 *
 * Gestionnaire des opérations BDD liées au solde (wallet) des utilisateurs de VortexVR.
 *
 * Rôle dans le projet :
 *   Gère les opérations sur la colonne "solde" de la table utilisateurs.
 *   Utilisé par wallet.php et checkout.php pour lire et débiter le solde
 *   lors du paiement d'une commande.
 *
 *   Note : Ce manager travaille sur la colonne "solde" tandis que ClientManager
 *   travaille sur la colonne "argent". Selon la structure BDD, vérifier laquelle
 *   est réellement utilisée pour le portefeuille.
 *
 * Méthodes :
 *   - getSolde()                  : lecture du solde actuel
 *   - setSolde()                  : remplacement du solde (dépôt manuel)
 *   - debiterSolde()              : débit du solde (lors d'un paiement)
 *   - crediterSolde()             : crédit du solde (remboursement)
 *   - getUtilisateurParCourriel() : infos utilisateur pour l'authentification
 *   - verifierConnexion()         : vérification login (comparaison directe, legacy)
 *
 * @project VortexVR – Boutique de casques VR
 */
class UtilisateurManager
{
    /** Connexion PDO partagée par toutes les méthodes. */
    private PDO $db;

    /**
     * Initialise la connexion et configure le mode de fetch par défaut.
     */
    public function __construct()
    {
        $this->db = PDOFactory::getMySQLConnection();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Retourne le solde actuel d'un utilisateur.
     *
     * Retourne 0.0 si l'utilisateur n'existe pas (cas défensif).
     *
     * @param int $idUtilisateur ID de l'utilisateur.
     * @return float Solde en dollars (0.0 si non trouvé).
     */
    public function getSolde(int $idUtilisateur): float
    {
        $sql = "SELECT solde FROM utilisateurs WHERE id_utilisateur = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUtilisateur]);
        $row = $stmt->fetch();
        return $row ? (float)$row['solde'] : 0.0;
    }

    /**
     * Remplace le solde d'un utilisateur par une nouvelle valeur.
     *
     * Utilisé pour les dépôts manuels ou les ajustements de solde.
     *
     * @param int   $idUtilisateur ID de l'utilisateur.
     * @param float $nouveauSolde  Nouveau solde en dollars.
     */
    public function setSolde(int $idUtilisateur, float $nouveauSolde): void
    {
        $stmt = $this->db->prepare("UPDATE utilisateurs SET solde = :solde WHERE id_utilisateur = :id");
        $stmt->execute([':solde' => $nouveauSolde, ':id' => $idUtilisateur]);
    }

    /**
     * Déduit un montant du solde d'un utilisateur.
     *
     * Utilisé lors de la confirmation de paiement dans wallet.php.
     * Attention : ne vérifie pas si le solde est suffisant — vérifier avant d'appeler.
     *
     * @param int   $idUtilisateur ID de l'utilisateur.
     * @param float $montant       Montant à débiter en dollars.
     */
    public function debiterSolde(int $idUtilisateur, float $montant): void
    {
        $stmt = $this->db->prepare("UPDATE utilisateurs SET solde = solde - :montant WHERE id_utilisateur = :id");
        $stmt->execute([':montant' => $montant, ':id' => $idUtilisateur]);
    }

    /**
     * Ajoute un montant au solde d'un utilisateur.
     *
     * Utilisé pour créditer le compte (remboursement ou dépôt).
     *
     * @param int   $idUtilisateur ID de l'utilisateur.
     * @param float $montant       Montant à créditer en dollars.
     */
    public function crediterSolde(int $idUtilisateur, float $montant): void
    {
        $stmt = $this->db->prepare("UPDATE utilisateurs SET solde = solde + :montant WHERE id_utilisateur = :id");
        $stmt->execute([':montant' => $montant, ':id' => $idUtilisateur]);
    }

    /**
     * Retourne les informations minimales d'un utilisateur identifié par son courriel.
     *
     * Retourne uniquement les colonnes nécessaires à l'authentification :
     * id_utilisateur, nom_utilisateur, courriel, mot_de_passe.
     *
     * @param string $courriel Courriel de l'utilisateur à rechercher.
     * @return array|null Tableau associatif ou null si non trouvé.
     */
    public function getUtilisateurParCourriel(string $courriel): ?array
    {
        $sql = "SELECT id_utilisateur, nom_utilisateur, courriel, mot_de_passe
                FROM utilisateurs
                WHERE courriel = :courriel
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':courriel' => $courriel]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Vérifie les identifiants de connexion par comparaison directe (méthode legacy).
     *
     * ⚠️ Comparaison en clair : cette méthode ne supporte pas bcrypt.
     * À utiliser uniquement si les mots de passe ne sont pas hachés en BDD.
     * Pour les comptes bcrypt, utiliser ClientManager::clientExists() à la place.
     *
     * @param string $courriel    Courriel saisi.
     * @param string $motDePasse  Mot de passe en clair saisi.
     * @return array|null Données utilisateur si valides, null sinon.
     */
    public function verifierConnexion(string $courriel, string $motDePasse): ?array
    {
        $user = $this->getUtilisateurParCourriel($courriel);
        if (!$user) return null;

        // Comparaison directe (non sécurisée pour les mots de passe hachés).
        if ($motDePasse !== $user['mot_de_passe']) return null;

        return $user;
    }
}
?>