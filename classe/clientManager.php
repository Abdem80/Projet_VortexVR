<?php
include_once "PDOFactory.php";
include_once "classe/client.php";

class ClientManager {
    private $client;

    public function __construct() {
        $this->client = PDOFactory::getMySQLConnection();
    }

    public function addClient($clientObj) {
        if (!($clientObj instanceof Client)) {
            throw new Exception("Le paramètre doit être une instance de Client");
        }

        try {
            $this->client->beginTransaction();

            $sql = "
                INSERT INTO utilisateurs (
                    nom, prenom, nom_utilisateur, courriel, mot_de_passe,
                    pays, adresse, argent, ville, telephone
                ) VALUES (
                    :nom, :prenom, :nom_utilisateur, :courriel, :mot_de_passe,
                    :pays, :adresse, :argent, :ville, :telephone
                )
            ";

            $stmt = $this->client->prepare($sql);

            $passFromObj = $clientObj->get_pass() ?? '';

            $info = password_get_info($passFromObj);
            if (isset($info['algo']) && $info['algo'] === 0) {
                $motdepasseAStocker = password_hash($passFromObj, PASSWORD_DEFAULT);
            } else {
                $motdepasseAStocker = $passFromObj;
            }

            $params = [
                ':nom'        => $clientObj->get_nom(),
                ':prenom'     => $clientObj->get_prenom(),
                ':nom_utilisateur'   => $clientObj->get_username(),
                ':courriel'   => $clientObj->get_courriel(),
                ':mot_de_passe' => $motdepasseAStocker,
                ':pays'       => $clientObj->get_pays(),
                ':adresse'    => $clientObj->get_adresse(),
                ':argent'     => (float)$clientObj->get_argent(),
                ':ville'      => $clientObj->get_ville(),
                ':telephone'  => $clientObj->get_tel()
            ];

            $stmt->execute($params);

            $lastId = $this->client->lastInsertId();
            if (!$lastId) {
                throw new Exception("Impossible de récupérer l'ID du dernier client inséré !");
            }

            $this->client->commit();

            return $lastId;

        } catch (Exception $e) {
            if ($this->client->inTransaction()) {
                $this->client->rollBack();
            }
            throw new Exception("Erreur lors de l'ajout du client : " . $e->getMessage());
        }
    }

    public function clientExists($identifier, $password) {
        try {
            $stmt = $this->client->prepare("
                SELECT * FROM utilisateurs 
                WHERE nom_utilisateur = :ident OR courriel = :ident
                LIMIT 1
            ");
            $stmt->execute([':ident' => $identifier]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return false;
            }

            $mdpStocke = $data['mot_de_passe'] ?? '';

            if (!empty($mdpStocke) && password_verify($password, $mdpStocke)) {
            } else {
                if ($mdpStocke !== $password) {
                    return false;
                }
            }

            $client = new Client([
                'prenom'   => $data['prenom']   ?? '',
                'nom'      => $data['nom']      ?? '',
                'username' => $data['nom_utilisateur'] ?? '',
                'courriel' => $data['courriel'] ?? '',
                'pass'     => $mdpStocke,
                'pays'     => $data['pays']     ?? '',
                'adresse'  => $data['adresse']  ?? '',
                'argent'   => $data['argent']   ?? 0.0,
                'ville'    => $data['ville']    ?? '',
                'tel'      => $data['telephone']?? ''
            ]);

            if (isset($data['idClient'])) {
                $client->set_idClient($data['id_utilisateur']);
            }

            return $client;

        } catch (Exception $e) {
            throw new Exception("Erreur lors de la vérification du client : " . $e->getMessage());
        }
    }
    //rajouter cette fonction par will
    public function getIdByCourriel($courriel){
         $stmt = $this->client->prepare("SELECT id_utilisateur FROM utilisateurs  WHERE courriel = :courriel LIMIT 1");
         $stmt->execute([':courriel' => $courriel]);
         $data = $stmt->fetch(PDO::FETCH_ASSOC);
         if($data && isset($data['id_utilisateur'])){
            return (int)$data['id_utilisateur'];
         }
         return null;
    }
    //fin de l'ajout

    public function getClientById($id) {
        try {
            $stmt = $this->client->prepare("SELECT * FROM utilisateurs  WHERE id_utilisateur = :id LIMIT 1");
            $stmt->execute([':id' => (int)$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) return false;

            $client = new Client([
                'prenom'   => $data['prenom']   ?? '',
                'nom'      => $data['nom']      ?? '',
                'nom_utilisateur' => $data['nom_utilisateur'] ?? '',
                'courriel' => $data['courriel'] ?? '',
                'pass'     => $data['mot_de_passe'] ?? '',
                'pays'     => $data['pays']     ?? '',
                'adresse'  => $data['adresse']  ?? '',
                'argent'   => $data['argent']   ?? 0.0,
                'ville'    => $data['ville']    ?? '',
                'tel'      => $data['telephone']?? ''
            ]);

            $client->set_idClient($data['id_utilisateur'] ?? null);

            return $client;

        } catch (Exception $e) {
            throw new Exception("Erreur getClientById : " . $e->getMessage());
        }
    }

    public function showClientByCourriel($courriel) {
        try{
            $sql = "
            SELECT nom, prenom, nom_utilisateur, courriel, mot_de_passe,
                    pays, adresse, argent, ville, telephone
            FROM utilisateurs
            WHERE courriel = :courriel
            ";

        $stmt = $this->client->prepare($sql);
        $stmt->bindValue(':courriel', $courriel, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
        catch (Exception $e) {
            throw new Exception("Erreur lors de l'affichage de la table : " . $e->getMessage());
        }
    }

    public function updateNom(string $courriel, string $valeur) {
        $sql = "UPDATE utilisateurs 
        SET nom = :valeur 
        WHERE courriel = :courriel";
        $stmt = $this->client->prepare($sql);
        $stmt->execute([
            ':valeur' => $valeur,
            ':courriel' => $courriel
        ]);
    }

    public function updatePrenom(string $courriel, string $valeur) {
        $sql = "UPDATE utilisateurs 
        SET prenom = :valeur 
        WHERE courriel = :courriel";
        $stmt = $this->client->prepare($sql);
        $stmt->execute([
            ':valeur' => $valeur,
            ':courriel' => $courriel
        ]);
    }

    public function updateNomUtilisateur(string $courriel, string $valeur) {
        $sql = "UPDATE utilisateurs 
        SET nom_utilisateur = :valeur 
        WHERE courriel = :courriel";
        $stmt = $this->client->prepare($sql);
        $stmt->execute([
            ':valeur' => $valeur,
            ':courriel' => $courriel
        ]);
    }

    public function updateEmail(string $courriel, string $valeur) {
        $sql = "UPDATE utilisateurs 
        SET courriel = :valeur 
        WHERE courriel = :courriel";
        $stmt = $this->client->prepare($sql);
        $stmt->execute([
            ':valeur' => $valeur,
            ':courriel' => $courriel
        ]);
    }

    public function updatePays(string $courriel, string $valeur) {
        $sql = "UPDATE utilisateurs 
        SET pays = :valeur 
        WHERE courriel = :courriel";
        $stmt = $this->client->prepare($sql);
        $stmt->execute([
            ':valeur' => $valeur,
            ':courriel' => $courriel
        ]);
    }

    public function updateAdresse(string $courriel, string $valeur) {
        $sql = "UPDATE utilisateurs 
        SET adresse = :valeur 
        WHERE courriel = :courriel";
        $stmt = $this->client->prepare($sql);
        $stmt->execute([
            ':valeur' => $valeur,
            ':courriel' => $courriel
        ]);
    }

    public function updateVille(string $courriel, string $valeur) {
        $sql = "UPDATE utilisateurs 
        SET ville = :valeur 
        WHERE courriel = :courriel";
        $stmt = $this->client->prepare($sql);
        $stmt->execute([
            ':valeur' => $valeur,
            ':courriel' => $courriel
        ]);
    }

    public function updateTelephone(string $courriel, string $valeur) {
        $sql = "UPDATE utilisateurs 
        SET telephone = :valeur 
        WHERE courriel = :courriel";
        $stmt = $this->client->prepare($sql);
        $stmt->execute([
            ':valeur' => $valeur,
            ':courriel' => $courriel
        ]);
    }

    public function updateArgent(string $courriel, float $valeur) {
        $sql = "UPDATE utilisateurs 
        SET argent = :valeur 
        WHERE courriel = :courriel";
        $stmt = $this->client->prepare($sql);
        $stmt->execute([
            ':valeur' => $valeur,
            ':courriel' => $courriel
        ]);
    }

}
?>