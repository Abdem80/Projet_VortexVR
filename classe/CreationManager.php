<?php
class CreationManager {

    private $db;

    public function __construct() {
        $this->db = PDOFactory::getMySQLConnection();
    }

    public function getUserId(){
        return $_SESSION['id_utilisateur'];
    }

    public function getMarques(){
        $sql = "SELECT id_marque, nom_marque FROM marques";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchall();
    }

    public function getImages(){
        $sql = "SELECT image_fichier FROM casques";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    public function creeCasque($nom, $id_marque, $prix, $qte, $desc, $img){
        $id_createur = NULL;
        $id_createur = $this->getUserId();

        if($id_createur){
            $sql = "INSERT INTO casques (id_marque, id_createur, nom_casque, prix, stock, description, image_fichier) VALUES (:id_marque, :id_createur, :nom_casque, :prix, :stock, :description, :image_fichier)";
            $query = $this->db->prepare($sql);
            $query->execute([
                ':id_marque' => $id_marque,
                ':id_createur' => $id_createur,
                ':nom_casque' => $nom,
                ':prix' => $prix,
                ':stock' => $qte,
                ':description' => $desc,
                'image_fichier' => $img
            ]);
        }
    }
}



?>