<?php
require_once "inc/header.php";
require_once "classe/client.php";
require_once "classe/clientManager.php";
include_once "inc/pretraitement.php"; 
?>

<h1 class="center">Traitement de l'inscription</h1>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom       = $_POST['nom'] ?? '';
    $prenom    = $_POST['prenom'] ?? '';
    $username  = $_POST['username'] ?? '';
    $pays      = $_POST['pays'] ?? '';
    $adresse   = $_POST['adresse'] ?? '';
    $argent    = $_POST['argent'] ?? 0;
    $ville     = $_POST['ville'] ?? '';
    $telephone = $_POST['telephone'] ?? '';
    $courriel  = $_POST['email'] ?? '';
    $password  = $_POST['motdepasse'] ?? '';

    $client = new Client([
        'nom'      => $nom,
        'prenom'   => $prenom,
        'username' => $username,
        'pays'     => $pays,
        'adresse'  => $adresse,
        'argent'   => $argent,
        'ville'    => $ville,
        'tel'      => $telephone,
        'courriel' => $courriel,
        'pass'     => $password
    ]);

    $clientManager = new ClientManager();

    try {
        $idClient = $clientManager->addClient($client);

        //$_SESSION['id_utilisateur'] = $idClient;
        $_SESSION['nom_utilisateur'] = $client->get_username();
        $_SESSION['prenom'] = $client->get_prenom();
        $_SESSION['argent'] = $client->get_argent();

        echo "<h2 class='center'>Compte créé avec succès</h2>";
        echo "<p class='center'>ID client : " . htmlspecialchars($idClient) . "</p>";
        ?>

        <ul class="traitement">
            <li><strong>Profil</strong>
                <ul>
                    <li>Prénom : <?= htmlspecialchars($client->get_prenom()) ?></li>
                    <li>Nom : <?= htmlspecialchars($client->get_nom()) ?></li>
                    <li>Nom d'utilisateur : <?= htmlspecialchars($client->get_username()) ?></li>
                    <li>Courriel : <?= htmlspecialchars($client->get_courriel()) ?></li>
                </ul>
            </li>

            <li><strong>Coordonnées</strong>
                <ul>
                    <li>Pays : <?= htmlspecialchars($client->get_pays()) ?></li>
                    <li>Adresse : <?= htmlspecialchars($client->get_adresse()) ?></li>
                    <li>Ville : <?= htmlspecialchars($client->get_ville()) ?></li>
                    <li>Téléphone : <?= htmlspecialchars($client->get_tel()) ?></li>
                </ul>
            </li>

            <li><strong>Compte</strong>
                <ul>
                    <li>Argent disponible : <?= number_format($client->get_argent(), 2) ?> $</li>
                </ul>
            </li>
        </ul>

        <?php

    } catch (Exception $e) {
        echo "<h2 class='center erreur'>Erreur lors de la création du compte</h2>";
        echo "<p class='center'>" . htmlspecialchars($e->getMessage()) . "</p>";
    }

} else {
    echo "<p class='center'>Aucune donnée reçue.</p>";
}

require_once "inc/footer.php";