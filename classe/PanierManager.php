<?php
/**
 * classe/PanierManager.php
 *
 * Gestionnaire des opérations BDD liées au panier d'achat de VortexVR.
 *
 * Rôle dans le projet :
 *   Gère toutes les interactions avec les tables "paniers" et "articles_panier".
 *   Utilisé par panier.php (affichage, ajout, suppression, quantités),
 *   index.php (ajout rapide au panier depuis l'accueil),
 *   catalogue.php (ajout depuis le catalogue) et checkout.php (finalisation).
 *
 * Modèle de données :
 *   - Un panier appartient à un utilisateur (1 panier actif par utilisateur).
 *   - Un panier contient des articles_panier (lignes : casque + quantité + prix_unitaire).
 *   - Une commande est créée lors du paiement, puis le panier est vidé.
 *
 * @project VortexVR – Boutique de casques VR
 */
class PanierManager
{
    /** Connexion PDO partagée par toutes les méthodes. */
    private $db;

    /**
     * Initialise la connexion à la base de données via PDOFactory.
     */
    public function __construct()
    {
        $this->db = PDOFactory::getMySQLConnection();
    }

    /**
     * Récupère le panier actif d'un utilisateur.
     *
     * Retourne null si aucun panier n'existe encore pour cet utilisateur.
     * Dans ce cas, il faut d'abord appeler creerPanierPourUtilisateur().
     *
     * @param int $idUtilisateur ID de l'utilisateur connecté.
     * @return array|null Tableau [id_panier, id_utilisateur] ou null.
     */
    public function getPanierActifPourUtilisateur(int $idUtilisateur): ?array
    {
        $sql = "SELECT id_panier, id_utilisateur
                FROM paniers
                WHERE id_utilisateur = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUtilisateur]);
        $panier = $stmt->fetch();

        return $panier ?: null;
    }

    /**
     * Retourne les articles contenus dans un panier, avec les infos du casque associé.
     *
     * La jointure avec la table casques permet d'afficher nom_casque, description
     * et image_fichier directement dans panier.php et checkout.php.
     * Colonnes id_article_panier et description ajoutées par William.
     *
     * @param int $idPanier ID du panier dont on veut les articles.
     * @return array Tableau des articles avec infos casque.
     */
    public function getArticlesDuPanier(int $idPanier): array
    {
        $sql = "SELECT
                    ap.id_article_panier,
                    ap.id_panier,
                    ap.id_casque,
                    ap.quantite,
                    ap.prix_unitaire,
                    c.nom_casque,
                    c.description,
                    c.image_fichier
                FROM articles_panier ap
                JOIN casques c ON c.id_casque = ap.id_casque
                WHERE ap.id_panier = :id_panier";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_panier' => $idPanier]);
        return $stmt->fetchAll();
    }

    /**
     * Calcule le montant total du panier et enrichit chaque article d'un sous-total.
     *
     * Modifie le tableau $articles par référence pour ajouter la clé 'sous_total'
     * à chaque article (quantite * prix_unitaire).
     *
     * @param array $articles Tableau des articles du panier (passé par référence).
     * @return float Montant total du panier avant taxes.
     */
    public function calculerTotal(array &$articles): float
    {
        $total = 0.0;

        foreach ($articles as &$article) {
            $article['sous_total'] = $article['quantite'] * $article['prix_unitaire'];
            $total += $article['sous_total'];
        }
        unset($article); // Nettoyage de la référence (bonne pratique)

        return $total;
    }

    /**
     * Crée une commande en BDD à partir du panier et de son montant total.
     *
     * Appelé lors du paiement dans wallet.php après débit du solde.
     *
     * @param int   $idUtilisateur ID de l'utilisateur.
     * @param int   $idPanier      ID du panier commandé.
     * @param float $montantTotal  Montant total de la commande.
     * @return int  ID de la commande créée (lastInsertId).
     */
    public function creerCommande(int $idUtilisateur, int $idPanier, float $montantTotal): int
    {
        $sql = "INSERT INTO commandes (id_utilisateur, id_panier, montant_total)
                VALUES (:id_utilisateur, :id_panier, :montant_total)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_utilisateur' => $idUtilisateur,
            ':id_panier'      => $idPanier,
            ':montant_total'  => $montantTotal
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Supprime tous les articles d'un panier (sans supprimer le panier lui-même).
     *
     * Appelé après la confirmation du paiement pour libérer les articles.
     *
     * @param int $idPanier ID du panier à vider.
     */
    public function viderPanier(int $idPanier): void
    {
        $stmt = $this->db->prepare("DELETE FROM articles_panier WHERE id_panier = :id_panier");
        $stmt->execute([':id_panier' => $idPanier]);
    }

    /**
     * Crée un nouveau panier vide pour un utilisateur.
     *
     * Appelé si getPanierActifPourUtilisateur() retourne null (premier achat).
     *
     * @param int $idUtilisateur ID de l'utilisateur.
     * @return int ID du panier nouvellement créé.
     */
    public function creerPanierPourUtilisateur(int $idUtilisateur): int
    {
        $stmt = $this->db->prepare("INSERT INTO paniers (id_utilisateur) VALUES (:id_utilisateur)");
        $stmt->execute([':id_utilisateur' => $idUtilisateur]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Ajoute un casque au panier ou incrémente sa quantité s'il y est déjà.
     *
     * Logique :
     *   - Si le casque est déjà dans le panier (même id_panier + id_casque) → quantite + 1.
     *   - Sinon → INSERT d'une nouvelle ligne avec quantite = 1.
     *
     * @param int   $idPanier     ID du panier.
     * @param int   $idCasque     ID du casque à ajouter.
     * @param float $prixUnitaire Prix unitaire actuel du casque.
     */
    public function ajouterOuIncrementerArticle(int $idPanier, int $idCasque, float $prixUnitaire): void
    {
        // Vérifie si le casque est déjà présent dans ce panier.
        $stmt = $this->db->prepare("SELECT id_article_panier, quantite
            FROM articles_panier
            WHERE id_panier = :id_panier AND id_casque = :id_casque
            LIMIT 1");
        $stmt->execute([':id_panier' => $idPanier, ':id_casque' => $idCasque]);
        $ligne = $stmt->fetch();

        if ($ligne) {
            // Casque déjà présent : on incrémente la quantité.
            $stmtUpdate = $this->db->prepare(
                "UPDATE articles_panier SET quantite = quantite + 1 WHERE id_article_panier = :id_article_panier"
            );
            $stmtUpdate->execute([':id_article_panier' => (int) $ligne['id_article_panier']]);
            return;
        }

        // Casque absent : on crée une nouvelle ligne avec quantite = 1.
        $stmtInsert = $this->db->prepare(
            "INSERT INTO articles_panier (id_panier, id_casque, quantite, prix_unitaire)
             VALUES (:id_panier, :id_casque, 1, :prix_unitaire)"
        );
        $stmtInsert->execute([
            ':id_panier'     => $idPanier,
            ':id_casque'     => $idCasque,
            ':prix_unitaire' => $prixUnitaire
        ]);
    }

    /**
     * Supprime un article du panier d'un utilisateur.
     *
     * La jointure paniers/articles_panier garantit qu'un utilisateur ne peut
     * supprimer que ses propres articles (sécurité multi-utilisateur). Ajouté par William.
     *
     * @param int $idUtilisateur    ID de l'utilisateur propriétaire du panier.
     * @param int $idArticlePanier  ID de l'article à supprimer.
     */
    public function supprimerArticleUtilisateur(int $idUtilisateur, int $idArticlePanier): void
    {
        $sql = "DELETE ap FROM articles_panier AS ap
                JOIN paniers AS p ON p.id_panier = ap.id_panier
                WHERE ap.id_article_panier = :id_article
                AND p.id_utilisateur = :id_utilisateur";

        $query = $this->db->prepare($sql);
        $query->execute([
            ':id_article'     => $idArticlePanier,
            ':id_utilisateur' => $idUtilisateur
        ]);
    }

    /**
     * Augmente la quantité d'un article du panier de 1.
     *
     * La jointure garantit la sécurité : seul le propriétaire peut modifier.
     * Ajouté par William.
     *
     * @param int $idUtilisateur   ID de l'utilisateur.
     * @param int $idArticlePanier ID de l'article à modifier.
     */
    public function addQteCasque(int $idUtilisateur, int $idArticlePanier): void
    {
        $sql = "UPDATE articles_panier AS ap
                JOIN paniers AS p ON p.id_panier = ap.id_panier
                SET ap.quantite = ap.quantite + 1
                WHERE ap.id_article_panier = :id_article
                AND p.id_utilisateur = :id_utilisateur";

        $query = $this->db->prepare($sql);
        $query->execute([
            ':id_article'     => $idArticlePanier,
            ':id_utilisateur' => $idUtilisateur
        ]);
    }

    /**
     * Diminue la quantité d'un article du panier de 1 (minimum 1).
     *
     * La contrainte AND ap.quantite > 1 empêche de descendre en dessous de 1 unité.
     * Pour supprimer l'article, utiliser supprimerArticleUtilisateur(). Ajouté par William.
     *
     * @param int $idUtilisateur   ID de l'utilisateur.
     * @param int $idArticlePanier ID de l'article à modifier.
     */
    public function rmvQteCasque(int $idUtilisateur, int $idArticlePanier): void
    {
        $sql = "UPDATE articles_panier AS ap
                JOIN paniers AS p ON p.id_panier = ap.id_panier
                SET ap.quantite = ap.quantite - 1
                WHERE ap.id_article_panier = :id_article
                AND p.id_utilisateur = :id_utilisateur
                AND ap.quantite > 1";

        $query = $this->db->prepare($sql);
        $query->execute([
            ':id_article'     => $idArticlePanier,
            ':id_utilisateur' => $idUtilisateur
        ]);
    }
}