<?php
/**
 * classe/clientManager.php
 *
 * Gestionnaire des opérations BDD liées aux clients de VortexVR.
 *
 * Rôle dans le projet :
 *   Fait le lien entre les pages PHP et la table "utilisateurs" en base
 *   de données. Toutes les opérations CRUD sur les clients passent par
 *   cette classe, qui utilise uniquement des requêtes préparées PDO
 *   pour prévenir les injections SQL.
 *
 * Méthodes principales :
 *   - addClient()             : INSERT d'un nouveau client
 *   - clientExists()          : vérification des identifiants (login)
 *   - getIdByCourriel()       : récupère l'ID par courriel (pour la session)
 *   - getClientById()         : récupère un objet Client complet par ID
 *   - showClientByCourriel()  : tableau associatif pour l'affichage du profil
 *   - updateXxx()             : mise à jour d'un champ spécifique du profil
 *
 * @project VortexVR – Boutique de casques VR
 */

include_once "PDOFactory.php";
include_once "classe/client.php";

class ClientManager
{
    /**
     * Connexion PDO partagée par toutes les méthodes de la classe.
     * Nommée $client par convention héritée du projet.
     */
    private $client;

    /**
     * Initialise la connexion à la base de données via PDOFactory.
     */
    public function __construct()
    {
        $this->client = PDOFactory::getMySQLConnection();
    }

    /**
     * Insère un nouveau client en base de données dans une transaction.
     *
     * Utilise une transaction PDO pour garantir l'atomicité :
     * si une étape échoue, le rollback annule tout l'INSERT.
     * Le mot de passe est haché si ce n'est pas déjà fait.
     *
     * @param Client $clientObj Objet Client complet à insérer.
     * @return int ID du client nouvellement créé (lastInsertId).
     * @throws Exception Si le paramètre n'est pas un Client, ou en cas d'erreur BDD.
     */
    public function addClient($clientObj)
    {
        if (!($clientObj instanceof Client)) {
            throw new Exception("Le paramètre doit être une instance de Client");
        }

        try {
            $this->client->beginTransaction(); // Début de la transaction

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

            // Sécurité : on vérifie que le mot de passe n'est pas déjà haché
            // avant de le hacher à nouveau pour éviter un double hachage.
            $passFromObj = $clientObj->get_pass() ?? '';
            $info = password_get_info($passFromObj);
            if (isset($info['algo']) && $info['algo'] === 0) {
                $motdepasseAStocker = password_hash($passFromObj, PASSWORD_DEFAULT);
            } else {
                $motdepasseAStocker = $passFromObj; // Déjà haché
            }

            $params = [
                ':nom'               => $clientObj->get_nom(),
                ':prenom'            => $clientObj->get_prenom(),
                ':nom_utilisateur'   => $clientObj->get_username(),
                ':courriel'          => $clientObj->get_courriel(),
                ':mot_de_passe'      => $motdepasseAStocker,
                ':pays'              => $clientObj->get_pays(),
                ':adresse'           => $clientObj->get_adresse(),
                ':argent'            => (float)$clientObj->get_argent(),
                ':ville'             => $clientObj->get_ville(),
                ':telephone'         => $clientObj->get_tel()
            ];

            $stmt->execute($params);

            $lastId = $this->client->lastInsertId();
            if (!$lastId) {
                throw new Exception("Impossible de récupérer l'ID du dernier client inséré !");
            }

            $this->client->commit(); // Confirmation de la transaction

            return $lastId;

        } catch (Exception $e) {
            // Annulation de la transaction en cas d'erreur (ex. doublon de courriel).
            if ($this->client->inTransaction()) {
                $this->client->rollBack();
            }
            throw new Exception("Erreur lors de l'ajout du client : " . $e->getMessage());
        }
    }

    /**
     * Vérifie les identifiants de connexion d'un utilisateur.
     *
     * Recherche par nom_utilisateur OU courriel (les deux servent d'identifiant).
     * Vérifie le mot de passe via password_verify() (bcrypt).
     * Compatibilité : si le mot de passe en BDD n'est pas haché, comparaison directe.
     *
     * @param string $identifier Nom d'utilisateur ou courriel.
     * @param string $password   Mot de passe en clair soumis par le formulaire.
     * @return Client|false Objet Client si l'authentification réussit, false sinon.
     * @throws Exception En cas d'erreur BDD.
     */
    public function clientExists($identifier, $password)
    {
        try {
            $stmt = $this->client->prepare("
                SELECT * FROM utilisateurs
                WHERE nom_utilisateur = :ident OR courriel = :ident
                LIMIT 1
            ");
            $stmt->execute([':ident' => $identifier]);

            $data = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$data) {
                return false; // Aucun utilisateur trouvé avec cet identifiant
            }

            $mdpStocke = $data['mot_de_passe'] ?? '';

            // Vérification du mot de passe : on tente d'abord password_verify (bcrypt).
            // Si le hash est invalide, on tente une comparaison directe (legacy).
            if (!empty($mdpStocke) && password_verify($password, $mdpStocke)) {
                // OK : mot de passe bcrypt vérifié
            } else {
                if ($mdpStocke !== $password) {
                    return false; // Mot de passe incorrect
                }
            }

            // Construction de l'objet Client avec les données retournées par la BDD.
            $client = new Client([
                'prenom'   => $data['prenom']           ?? '',
                'nom'      => $data['nom']               ?? '',
                'username' => $data['nom_utilisateur']   ?? '',
                'courriel' => $data['courriel']          ?? '',
                'pass'     => $mdpStocke,
                'pays'     => $data['pays']              ?? '',
                'adresse'  => $data['adresse']           ?? '',
                'argent'   => $data['argent']            ?? 0.0,
                'ville'    => $data['ville']             ?? '',
                'tel'      => $data['telephone']         ?? ''
            ]);

            if (isset($data['idClient'])) {
                $client->set_idClient($data['id_utilisateur']);
            }

            return $client;

        } catch (Exception $e) {
            throw new Exception("Erreur lors de la vérification du client : " . $e->getMessage());
        }
    }

    /**
     * Retourne l'identifiant numérique d'un utilisateur à partir de son courriel.
     *
     * Utilisé lors du login pour stocker l'id en session ($_SESSION['id_utilisateur']).
     * Ajouté par William pour permettre la gestion du panier par ID utilisateur.
     *
     * @param string $courriel Courriel de l'utilisateur connecté.
     * @return int|null ID numérique ou null si non trouvé.
     */
    public function getIdByCourriel($courriel)
    {
        $stmt = $this->client->prepare("SELECT id_utilisateur FROM utilisateurs WHERE courriel = :courriel LIMIT 1");
        $stmt->execute([':courriel' => $courriel]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data && isset($data['id_utilisateur'])) {
            return (int)$data['id_utilisateur'];
        }
        return null;
    }

    /**
     * Retourne un objet Client complet à partir d'un identifiant numérique.
     *
     * Utilisé principalement pour des opérations internes nécessitant
     * l'objet Client plutôt qu'un tableau brut.
     *
     * @param int $id Identifiant numérique de l'utilisateur.
     * @return Client|false Objet Client ou false si non trouvé.
     * @throws Exception En cas d'erreur BDD.
     */
    public function getClientById($id)
    {
        try {
            $stmt = $this->client->prepare("SELECT * FROM utilisateurs WHERE id_utilisateur = :id LIMIT 1");
            $stmt->execute([':id' => (int)$id]);
            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$data) return false;

            $client = new Client([
                'prenom'   => $data['prenom']           ?? '',
                'nom'      => $data['nom']               ?? '',
                'username' => $data['nom_utilisateur']   ?? '',
                'courriel' => $data['courriel']          ?? '',
                'pass'     => $data['mot_de_passe']      ?? '',
                'pays'     => $data['pays']              ?? '',
                'adresse'  => $data['adresse']           ?? '',
                'argent'   => $data['argent']            ?? 0.0,
                'ville'    => $data['ville']             ?? '',
                'tel'      => $data['telephone']         ?? ''
            ]);

            $client->set_idClient($data['id_utilisateur'] ?? null);

            return $client;

        } catch (Exception $e) {
            throw new Exception("Erreur getClientById : " . $e->getMessage());
        }
    }

    /**
     * Retourne un tableau associatif avec les données du profil d'un client.
     *
     * Utilisé dans compte.php pour afficher les informations actuelles
     * du client connecté sans construire un objet Client complet.
     *
     * @param string $courriel Courriel de l'utilisateur connecté.
     * @return array|false Tableau de données ou false si non trouvé.
     * @throws Exception En cas d'erreur BDD.
     */
    public function showClientByCourriel($courriel)
    {
        try {
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

        } catch (Exception $e) {
            throw new Exception("Erreur lors de l'affichage de la table : " . $e->getMessage());
        }
    }

    // -------------------------------------------------------
    // Méthodes de mise à jour individuelle du profil.
    // Chacune met à jour un seul champ de la table utilisateurs
    // identifié par le courriel. Utilisées par updateClient.php.
    // -------------------------------------------------------

    /** Met à jour le nom de famille de l'utilisateur. */
    public function updateNom(string $courriel, string $valeur)
    {
        $stmt = $this->client->prepare("UPDATE utilisateurs SET nom = :valeur WHERE courriel = :courriel");
        $stmt->execute([':valeur' => $valeur, ':courriel' => $courriel]);
    }

    /** Met à jour le prénom de l'utilisateur. */
    public function updatePrenom(string $courriel, string $valeur)
    {
        $stmt = $this->client->prepare("UPDATE utilisateurs SET prenom = :valeur WHERE courriel = :courriel");
        $stmt->execute([':valeur' => $valeur, ':courriel' => $courriel]);
    }

    /** Met à jour le pseudonyme de l'utilisateur. */
    public function updateNomUtilisateur(string $courriel, string $valeur)
    {
        $stmt = $this->client->prepare("UPDATE utilisateurs SET nom_utilisateur = :valeur WHERE courriel = :courriel");
        $stmt->execute([':valeur' => $valeur, ':courriel' => $courriel]);
    }

    /** Met à jour l'adresse courriel de l'utilisateur. */
    public function updateEmail(string $courriel, string $valeur)
    {
        $stmt = $this->client->prepare("UPDATE utilisateurs SET courriel = :valeur WHERE courriel = :courriel");
        $stmt->execute([':valeur' => $valeur, ':courriel' => $courriel]);
    }

    /** Met à jour le pays de l'utilisateur. */
    public function updatePays(string $courriel, string $valeur)
    {
        $stmt = $this->client->prepare("UPDATE utilisateurs SET pays = :valeur WHERE courriel = :courriel");
        $stmt->execute([':valeur' => $valeur, ':courriel' => $courriel]);
    }

    /** Met à jour l'adresse postale de l'utilisateur. */
    public function updateAdresse(string $courriel, string $valeur)
    {
        $stmt = $this->client->prepare("UPDATE utilisateurs SET adresse = :valeur WHERE courriel = :courriel");
        $stmt->execute([':valeur' => $valeur, ':courriel' => $courriel]);
    }

    /** Met à jour la ville de l'utilisateur. */
    public function updateVille(string $courriel, string $valeur)
    {
        $stmt = $this->client->prepare("UPDATE utilisateurs SET ville = :valeur WHERE courriel = :courriel");
        $stmt->execute([':valeur' => $valeur, ':courriel' => $courriel]);
    }

    /** Met à jour le numéro de téléphone de l'utilisateur. */
    public function updateTelephone(string $courriel, string $valeur)
    {
        $stmt = $this->client->prepare("UPDATE utilisateurs SET telephone = :valeur WHERE courriel = :courriel");
        $stmt->execute([':valeur' => $valeur, ':courriel' => $courriel]);
    }

    /**
     * Met à jour le solde du wallet de l'utilisateur.
     *
     * @param string $courriel Identifiant de l'utilisateur.
     * @param float  $valeur   Nouveau solde en dollars.
     */
    public function updateArgent(string $courriel, float $valeur)
    {
        $stmt = $this->client->prepare("UPDATE utilisateurs SET argent = :valeur WHERE courriel = :courriel");
        $stmt->execute([':valeur' => $valeur, ':courriel' => $courriel]);
    }
}
?>