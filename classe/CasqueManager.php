<?php
/**
 * classe/CasqueManager.php
 *
 * Gestionnaire des opérations BDD liées aux casques VR du catalogue.
 *
 * Rôle dans le projet :
 *   Fournit toutes les requêtes SELECT sur la table "casques" et la table
 *   "marques". Utilisé par catalogue.php (filtres, liste complète),
 *   index.php (top 3) et partout où un casque doit être récupéré par ID.
 *
 * Méthodes disponibles :
 *   - getMarques()          : liste des marques pour les filtres du catalogue
 *   - getTroisCasques()     : top 3 casques pour la page d'accueil
 *   - getTousLesCasques()   : catalogue complet sans filtre
 *   - getCasquesFiltres()   : catalogue avec filtres dynamiques (recherche, marque, prix, tri)
 *   - getCasqueParId()      : détail d'un casque par son ID (utilisé lors de l'ajout au panier)
 *
 * @project VortexVR – Boutique de casques VR
 */
class CasqueManager
{
    /** Connexion PDO partagée par toutes les méthodes. */
    private PDO $db;

    /**
     * Initialise la connexion et configure le mode de fetch par défaut.
     * FETCH_ASSOC retourne les résultats comme tableaux associatifs.
     */
    public function __construct()
    {
        $this->db = PDOFactory::getMySQLConnection();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    /**
     * Retourne toutes les marques disponibles, triées alphabétiquement.
     *
     * Utilisé pour peupler les listes déroulantes de filtres dans catalogue.php
     * et la liste de sélection de marque dans creation_casque.php.
     *
     * @return array Tableau de ['id_marque', 'nom_marque'].
     */
    public function getMarques(): array
    {
        $sql = "SELECT id_marque, nom_marque FROM marques ORDER BY nom_marque ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Retourne les 3 premiers casques du catalogue (tri alphabétique).
     *
     * Utilisé sur la page d'accueil (index.php) pour afficher les
     * "meilleures offres du moment". Ajouté par William.
     *
     * @return array Tableau des 3 casques avec leurs données + nom_marque.
     */
    public function getTroisCasques(): array
    {
        $sql = "SELECT c.id_casque, c.id_marque, c.id_createur,
                       c.nom_casque, c.prix, c.stock, c.description, c.image_fichier,
                       m.nom_marque
                FROM casques c
                JOIN marques m ON m.id_marque = c.id_marque
                ORDER BY c.nom_casque ASC
                LIMIT 3";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Retourne l'intégralité du catalogue sans filtre, triée alphabétiquement.
     *
     * @return array Tableau de tous les casques avec leur nom de marque.
     */
    public function getTousLesCasques(): array
    {
        $sql = "SELECT c.id_casque, c.id_marque, c.id_createur,
                       c.nom_casque, c.prix, c.stock, c.description, c.image_fichier,
                       m.nom_marque
                FROM casques c
                JOIN marques m ON m.id_marque = c.id_marque
                ORDER BY c.nom_casque ASC";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Retourne les casques filtrés et triés selon les critères passés en paramètre.
     *
     * Construit dynamiquement la clause WHERE et ORDER BY selon les filtres
     * actifs, en utilisant des requêtes préparées pour chaque paramètre.
     *
     * Filtres supportés :
     *   - q         : recherche textuelle sur nom_casque et description (LIKE)
     *   - id_marque : filtre par marque (entier)
     *   - prix_min  : filtre par prix minimum (float)
     *   - prix_max  : filtre par prix maximum (float)
     *   - sort      : ordre de tri ('nom_asc', 'nom_desc', 'prix_asc', 'prix_desc')
     *
     * @param array $filtres Tableau associatif des critères de filtrage.
     * @return array Tableau des casques correspondants.
     */
    public function getCasquesFiltres(array $filtres): array
    {
        $where  = [];  // Clauses WHERE accumulées
        $params = [];  // Paramètres liés aux clauses

        // Filtre texte : recherche dans le nom et la description
        if (!empty($filtres['q'])) {
            $where[] = "(c.nom_casque LIKE :q OR c.description LIKE :q)";
            $params[':q'] = '%' . $filtres['q'] . '%';
        }

        // Filtre par marque
        if (!empty($filtres['id_marque'])) {
            $where[] = "c.id_marque = :id_marque";
            $params[':id_marque'] = (int) $filtres['id_marque'];
        }

        // Filtre par prix minimum
        if (isset($filtres['prix_min']) && $filtres['prix_min'] !== "") {
            $where[] = "c.prix >= :prix_min";
            $params[':prix_min'] = (float) $filtres['prix_min'];
        }

        // Filtre par prix maximum
        if (isset($filtres['prix_max']) && $filtres['prix_max'] !== "") {
            $where[] = "c.prix <= :prix_max";
            $params[':prix_max'] = (float) $filtres['prix_max'];
        }

        $sql = "SELECT c.id_casque, c.id_marque, c.id_createur,
                       c.nom_casque, c.prix, c.stock, c.description, c.image_fichier,
                       m.nom_marque
                FROM casques c
                JOIN marques m ON m.id_marque = c.id_marque";

        // Ajout de la clause WHERE seulement si des filtres sont actifs
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        // Tri dynamique selon le paramètre 'sort'
        $sort = $filtres['sort'] ?? 'nom_asc';
        switch ($sort) {
            case 'nom_desc':  $sql .= " ORDER BY c.nom_casque DESC"; break;
            case 'prix_asc':  $sql .= " ORDER BY c.prix ASC";        break;
            case 'prix_desc': $sql .= " ORDER BY c.prix DESC";       break;
            default:          $sql .= " ORDER BY c.nom_casque ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Retourne un casque par son identifiant numérique.
     *
     * Utilisé lors de l'ajout au panier pour valider l'existence du casque
     * et lire son prix et son stock avant l'insertion.
     *
     * @param int $idCasque Identifiant du casque.
     * @return array|null Données du casque ou null si non trouvé.
     */
    public function getCasqueParId(int $idCasque): ?array
    {
        $sql = "SELECT c.id_casque, c.id_marque, c.id_createur,
                       c.nom_casque, c.prix, c.stock, c.description, c.image_fichier,
                       m.nom_marque
                FROM casques c
                JOIN marques m ON m.id_marque = c.id_marque
                WHERE c.id_casque = :id
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idCasque]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
