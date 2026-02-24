<?php
class Client {
    private $idClient, $prenom,$nom, $username, $courriel, $pass, $pays, $adresse, $argent = 0.0, $ville, $tel;

    public function __construct(array $params = []) {
        foreach ($params as $k => $v) {
            $methodName = "set_" . $k;
            if (method_exists($this, $methodName)) {
                $this->$methodName($v);
            }
        }
    }

    public function get_idClient() { return $this->idClient; }
    public function set_idClient($id) { $this->idClient = (int)$id; return $this; }

    public function get_prenom() { return $this->prenom; }
    public function set_prenom($prenom) { $this->prenom = $prenom; return $this; }

    public function get_nom() { return $this->nom; }
    public function set_nom($nom) { $this->nom = $nom; return $this; }

    public function get_username() { return $this->username; }
    public function set_username($username) { $this->username = $username; return $this; }

    public function get_courriel() { return $this->courriel; }
    public function set_courriel($courriel) { $this->courriel = $courriel; return $this; }

    public function get_pass() { return $this->pass; }
    public function set_pass($pass) {
        if (password_get_info($pass)['algo'] === 0) {
            $this->pass = password_hash($pass, PASSWORD_DEFAULT);
        } else {
            $this->pass = $pass;
        }
        return $this;
    }

    public function get_pays() { return $this->pays; }
    public function set_pays($pays) { $this->pays = $pays; return $this; }

    public function get_adresse() { return $this->adresse; }
    public function set_adresse($adresse) { $this->adresse = $adresse; return $this; }

    public function get_argent() { return $this->argent; }
    public function set_argent($argent) { $this->argent = (float)$argent; return $this; }

    public function get_ville() { return $this->ville; }
    public function set_ville($ville) { $this->ville = $ville; return $this; }

    public function get_tel() { return $this->tel; }
    public function set_tel($tel) { $this->tel = $tel; return $this; }
}
?>