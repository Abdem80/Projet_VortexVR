<?php
class PanierManager {

    private $db;

    public function __construct() {
        $this->db = PDOFactory::getMySQLConnection();
    }

    public function getPanierActifPourUtilisateur(int $idUtilisateur): ?array {
        $sql = "SELECT id_panier, id_utilisateur
                FROM paniers
                WHERE id_utilisateur = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $idUtilisateur]);
        $panier = $stmt->fetch();

        if ($panier === false) {
            return null;
        }

        return $panier;
    }

    public function getArticlesDuPanier(int $idPanier): array {
            //id_article_panier et c.description ajouter par will
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
        $articles = $stmt->fetchAll();

        return $articles;
    }

    public function calculerTotal(array &$articles): float {
        $total = 0.0;

        foreach ($articles as &$article) {
            $article['sous_total'] = $article['quantite'] * $article['prix_unitaire'];
            $total += $article['sous_total'];
        }
        unset($article); 
        return $total;
    }

    public function creerCommande(int $idUtilisateur, int $idPanier, float $montantTotal): int {

        $sql = 
        "INSERT INTO commandes (id_utilisateur, id_panier, montant_total)
            VALUES (:id_utilisateur, :id_panier, :montant_total)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_utilisateur' => $idUtilisateur,
            ':id_panier'      => $idPanier,
            ':montant_total'  => $montantTotal
        ]);

        return $this->db->lastInsertId();
    }

    public function viderPanier(int $idPanier): void {
        $sql = "DELETE FROM articles_panier WHERE id_panier = :id_panier";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id_panier' => $idPanier]);
    }

    public function creerPanierPourUtilisateur(int $idUtilisateur): int {

        $sql = "INSERT INTO paniers (id_utilisateur)
                VALUES (:id_utilisateur)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_utilisateur' => $idUtilisateur
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function ajouterOuIncrementerArticle(int $idPanier, int $idCasque, float $prixUnitaire): void {
    $sql = "SELECT id_article_panier, quantite
            FROM articles_panier
            WHERE id_panier = :id_panier
              AND id_casque = :id_casque
            LIMIT 1";

    $stmt = $this->db->prepare($sql);
    $stmt->execute([
        ':id_panier' => $idPanier,
        ':id_casque' => $idCasque
    ]);

    $ligne = $stmt->fetch();

    if ($ligne) {
        $sqlUpdate = "UPDATE articles_panier
                      SET quantite = quantite + 1
                      WHERE id_article_panier = :id_article_panier";

        $stmtUpdate = $this->db->prepare($sqlUpdate);
        $stmtUpdate->execute([
            ':id_article_panier' => (int) $ligne['id_article_panier']
        ]);
        return;
    }

    $sqlInsert = "INSERT INTO articles_panier (id_panier, id_casque, quantite, prix_unitaire)
                  VALUES (:id_panier, :id_casque, 1, :prix_unitaire)";

    $stmtInsert = $this->db->prepare($sqlInsert);
    $stmtInsert->execute([
        ':id_panier' => $idPanier,
        ':id_casque' => $idCasque,
        ':prix_unitaire' => $prixUnitaire
    ]);
}


//ajouter par will


public function supprimerArticleUtilisateur(int $idUtilisateur, int $idArticlePanier){

    $sql = "DELETE ap FROM articles_panier 
    AS ap JOIN paniers AS p ON p.id_panier = ap.id_panier 
    WHERE ap.id_article_panier = :id_article 
    AND p.id_utilisateur = :id_utilisateur";

    $query = $this->db->prepare($sql);
    $query->execute([
        ':id_article' => $idArticlePanier,
        ':id_utilisateur' => $idUtilisateur
    ]);


}



public function addQteCasque(int $idUtilisateur, int $idArticlePanier){

    $sql = "UPDATE articles_panier AS ap JOIN paniers AS p ON p.id_panier = ap.id_panier 
    SET ap.quantite = ap.quantite + 1 WHERE ap.id_article_panier = :id_article 
    AND p.id_utilisateur = :id_utilisateur";

    $query = $this->db->prepare($sql);
    $query->execute([
        ':id_article' => $idArticlePanier,
        ':id_utilisateur' => $idUtilisateur
    ]);
}

public function rmvQteCasque(int $idUtilisateur, int $idArticlePanier){

    $sql = "UPDATE articles_panier AS ap JOIN paniers AS p ON p.id_panier = ap.id_panier 
    SET ap.quantite = ap.quantite - 1 WHERE ap.id_article_panier = :id_article 
    AND p.id_utilisateur = :id_utilisateur AND ap.quantite > 1";

    $query = $this->db->prepare($sql);
    $query->execute([
        ':id_article' => $idArticlePanier,
        ':id_utilisateur' => $idUtilisateur
    ]);

}

}