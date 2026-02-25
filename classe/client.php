<?php
/**
 * classe/client.php
 *
 * Modèle de données représentant un client de la boutique VortexVR.
 *
 * Rôle dans le projet :
 *   Encapsule toutes les informations d'un utilisateur (profil, coordonnées, solde).
 *   Utilisé comme objet de transfert de données (DTO) entre les pages PHP
 *   et le gestionnaire ClientManager. Aucune logique BDD ici : la classe
 *   est "pure données + accesseurs".
 *
 * Particularité de set_pass() :
 *   Si le mot de passe reçu est en clair (algo = 0 selon password_get_info()),
 *   il est automatiquement haché avec bcrypt (PASSWORD_DEFAULT).
 *   Si le hash est déjà stocké (algo != 0), il est conservé tel quel.
 *   Cela évite de re-hacher un hash déjà en BDD lors d'une reconstruction
 *   de l'objet depuis les données SQL.
 *
 * Convention des setters :
 *   Tous retournent $this pour permettre le chaînage de méthodes.
 *
 * @project VortexVR – Boutique de casques VR
 */
class Client
{
    // -------------------------------------------------------
    // Attributs privés : encapsulation totale des données.
    // -------------------------------------------------------
    private $idClient;   // Identifiant numérique en BDD (int)
    private $prenom;     // Prénom de l'utilisateur
    private $nom;        // Nom de famille
    private $username;   // Pseudonyme unique utilisé pour l'authentification
    private $courriel;   // Adresse courriel (identifiant alternatif)
    private $pass;       // Mot de passe haché (bcrypt)
    private $pays;       // Pays de résidence (optionnel)
    private $adresse;    // Adresse postale (optionnel)
    private $argent = 0.0; // Solde du wallet en dollars (défaut : 0.00$)
    private $ville;      // Ville de résidence (optionnel)
    private $tel;        // Numéro de téléphone au format 000-000-0000

    /**
     * Constructeur dynamique par tableau associatif.
     *
     * Permet d'instancier facilement un Client depuis un tableau POST
     * ou un tableau retourné par PDO. Chaque clé du tableau est mappée
     * vers le setter correspondant (ex. 'nom' → set_nom()).
     *
     * @param array $params Tableau [nomAttribut => valeur].
     */
    public function __construct(array $params = [])
    {
        foreach ($params as $k => $v) {
            $methodName = "set_" . $k;
            // On vérifie l'existence du setter avant d'appeler pour
            // ignorer silencieusement les clés inconnues.
            if (method_exists($this, $methodName)) {
                $this->$methodName($v);
            }
        }
    }

    // -------------------------------------------------------
    // Accesseurs (getters / setters) – tous publics.
    // Les setters retournent $this pour le chaînage fluent.
    // -------------------------------------------------------

    public function get_idClient()           { return $this->idClient; }
    public function set_idClient($id)        { $this->idClient = (int)$id; return $this; }

    public function get_prenom()             { return $this->prenom; }
    public function set_prenom($prenom)      { $this->prenom = $prenom; return $this; }

    public function get_nom()                { return $this->nom; }
    public function set_nom($nom)            { $this->nom = $nom; return $this; }

    public function get_username()           { return $this->username; }
    public function set_username($username)  { $this->username = $username; return $this; }

    public function get_courriel()           { return $this->courriel; }
    public function set_courriel($courriel)  { $this->courriel = $courriel; return $this; }

    /**
     * Définit le mot de passe en le hachant si nécessaire.
     *
     * Si le mot de passe est en clair (algo = 0), il est haché avec bcrypt.
     * S'il est déjà haché, il est stocké directement.
     *
     * @param string $pass Mot de passe en clair ou déjà haché.
     * @return $this
     */
    public function get_pass()               { return $this->pass; }
    public function set_pass($pass)
    {
        // password_get_info() retourne algo = 0 si la chaîne n'est pas un hash reconnu.
        if (password_get_info($pass)['algo'] === 0) {
            $this->pass = password_hash($pass, PASSWORD_DEFAULT); // Hachage bcrypt
        } else {
            $this->pass = $pass; // Déjà haché, on conserve tel quel
        }
        return $this;
    }

    public function get_pays()               { return $this->pays; }
    public function set_pays($pays)          { $this->pays = $pays; return $this; }

    public function get_adresse()            { return $this->adresse; }
    public function set_adresse($adresse)    { $this->adresse = $adresse; return $this; }

    public function get_argent()             { return $this->argent; }
    /** Cast en float pour garantir la compatibilité avec les opérations financières. */
    public function set_argent($argent)      { $this->argent = (float)$argent; return $this; }

    public function get_ville()              { return $this->ville; }
    public function set_ville($ville)        { $this->ville = $ville; return $this; }

    public function get_tel()                { return $this->tel; }
    public function set_tel($tel)            { $this->tel = $tel; return $this; }
}
?>