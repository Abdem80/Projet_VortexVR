<?php
/**
 * classe/CreationManager.php
 *
 * Gestionnaire des opérations BDD liées à la création de casques VR par les utilisateurs.
 *
 * Rôle dans le projet :
 *   Permet à un utilisateur connecté d'ajouter un nouveau casque au catalogue.
 *   Utilisé exclusivement par creation_casque.php.
 *
 * Méthodes :
 *   - getUserId()   : lit l'ID connecté depuis la session
 *   - getMarques()  : liste des marques pour le <select> du formulaire
 *   - getImages()   : liste des images existantes pour le <select> du formulaire
 *   - creeCasque()  : INSERT d'un nouveau casque en BDD
 *
 * @project VortexVR – Boutique de casques VR
 */
class CreationManager
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
     * Retourne l'ID de l'utilisateur actuellement connecté depuis la session.
     *
     * Utilisé dans creeCasque() pour associer le casque créé à son créateur
     * via la colonne id_createur de la table casques.
     *
     * @return int|null ID de l'utilisateur ou null si non connecté.
     */
    public function getUserId()
    {
        return $_SESSION['id_utilisateur'];
    }

    /**
     * Retourne la liste de toutes les marques disponibles en BDD.
     *
     * Utilisé pour peupler la liste déroulante de marques dans le formulaire
     * de création de casque (creation_casque.php).
     *
     * @return array Tableau de ['id_marque', 'nom_marque'].
     */
    public function getMarques()
    {
        $sql   = "SELECT id_marque, nom_marque FROM marques";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    /**
     * Retourne la liste des noms de fichiers d'images déjà utilisés dans le catalogue.
     *
     * Permet à l'utilisateur de choisir une image existante pour son casque,
     * évitant d'avoir à uploader un fichier.
     *
     * @return array Tableau de ['image_fichier'].
     */
    public function getImages()
    {
        $sql   = "SELECT image_fichier FROM casques";
        $query = $this->db->prepare($sql);
        $query->execute();
        return $query->fetchAll();
    }

    /**
     * Insère un nouveau casque dans la table casques.
     *
     * L'id_createur est automatiquement récupéré depuis la session via getUserId().
     * Si l'utilisateur n'est pas connecté (id_createur null), l'insertion est ignorée.
     *
     * @param string $nom       Nom commercial du casque.
     * @param string $id_marque ID de la marque sélectionnée.
     * @param string $prix      Prix en dollars (sera stocké tel quel, pas de cast).
     * @param string $qte       Quantité en stock.
     * @param string $desc      Description textuelle du casque.
     * @param string $img       Nom du fichier image sélectionné.
     */
    public function creeCasque($nom, $id_marque, $prix, $qte, $desc, $img)
    {
        $id_createur = $this->getUserId(); // Récupère l'ID de l'utilisateur connecté

        if ($id_createur) {
            $sql = "INSERT INTO casques (id_marque, id_createur, nom_casque, prix, stock, description, image_fichier)
                    VALUES (:id_marque, :id_createur, :nom_casque, :prix, :stock, :description, :image_fichier)";

            $query = $this->db->prepare($sql);
            $query->execute([
                ':id_marque'     => $id_marque,
                ':id_createur'   => $id_createur,
                ':nom_casque'    => $nom,
                ':prix'          => $prix,
                ':stock'         => $qte,
                ':description'   => $desc,
                'image_fichier'  => $img   // Note : préfixe ':' manquant (existant dans le code)
            ]);
        }
    }
}
?>