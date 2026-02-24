<?php

class CasqueManager {

    private PDO $db;

    public function __construct() {
        $this->db = PDOFactory::getMySQLConnection();
        $this->db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function getMarques(): array {
        $sql = "SELECT id_marque, nom_marque
                FROM marques
                ORDER BY nom_marque ASC";
        return $this->db->query($sql)->fetchAll();
    }


        //ajout de will pour ramaser seulement les 3 premiers casques

    public function getTroisCasques(): array {
        $sql = "SELECT c.id_casque, c.id_marque, c.id_createur,
                c.nom_casque, c.prix, c.stock, c.description, c.image_fichier,
                m.nom_marque
        FROM casques c
        JOIN marques m ON m.id_marque = c.id_marque
        ORDER BY c.nom_casque ASC
        LIMIT 3";
        return $this->db->query($sql)->fetchAll();
    }

    //fin de l'ajout



    public function getTousLesCasques(): array {
        $sql = "SELECT c.id_casque, c.id_marque, c.id_createur,
                       c.nom_casque, c.prix, c.stock, c.description, c.image_fichier,
                       m.nom_marque
                FROM casques c
                JOIN marques m ON m.id_marque = c.id_marque
                ORDER BY c.nom_casque ASC";
        return $this->db->query($sql)->fetchAll();
    }

    public function getCasquesFiltres(array $filtres): array {

        $where = [];
        $params = [];

        if (!empty($filtres['q'])) {
            $where[] = "(c.nom_casque LIKE :q OR c.description LIKE :q)";
            $params[':q'] = '%' . $filtres['q'] . '%';
        }

        if (!empty($filtres['id_marque'])) {
            $where[] = "c.id_marque = :id_marque";
            $params[':id_marque'] = (int) $filtres['id_marque'];
        }

        if (isset($filtres['prix_min']) && $filtres['prix_min'] !== "") {
            $where[] = "c.prix >= :prix_min";
            $params[':prix_min'] = (float) $filtres['prix_min'];
        }

        if (isset($filtres['prix_max']) && $filtres['prix_max'] !== "") {
            $where[] = "c.prix <= :prix_max";
            $params[':prix_max'] = (float) $filtres['prix_max'];
        }

        $sql = "SELECT c.id_casque, c.id_marque, c.id_createur,
                       c.nom_casque, c.prix, c.stock, c.description, c.image_fichier,
                       m.nom_marque
                FROM casques c
                JOIN marques m ON m.id_marque = c.id_marque";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sort = $filtres['sort'] ?? 'nom_asc';
        switch ($sort) {
            case 'nom_desc':
                $sql .= " ORDER BY c.nom_casque DESC";
                break;
            case 'prix_asc':
                $sql .= " ORDER BY c.prix ASC";
                break;
            case 'prix_desc':
                $sql .= " ORDER BY c.prix DESC";
                break;
            default:
                $sql .= " ORDER BY c.nom_casque ASC";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getCasqueParId(int $idCasque): ?array {
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
