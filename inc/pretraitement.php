<?php
include_once __DIR__ . "/../classe/clientManager.php";
include_once __DIR__ . "/../classe/client.php";

$clientManager = new ClientManager();

if (isset($_REQUEST['action'])) {
    $action = $_REQUEST['action'];

    switch ($action) {

        case "login":
            $username = trim($_REQUEST['username'] ?? '');
            $password = $_REQUEST['pass'] ?? '';

            if ($username && $password) {
                try {
                    $client = $clientManager->clientExists($username, $password);

                    if ($client) {
                        $_SESSION['courriel']   = $client->get_courriel();
                        $_SESSION['id_utilisateur']   = $clientManager->getIdByCourriel($_SESSION['courriel']);
                        $_SESSION['nomComplet'] = $client->get_prenom() . ' ' . $client->get_nom();
                        $_SESSION['nom_utilisateur']   = $client->get_username();

                        header("Location: index.php");
                        exit();
                    } else {
                        $_SESSION['login_error'] = "Nom d'utilisateur ou mot de passe incorrect.";
                        header("Location: login.php");
                        exit();
                    }

                } catch (Exception $e) {
                    $_SESSION['login_error'] = "Il y a une erreur lors de la conexion : " . $e->getMessage();
                    header("Location: login.php");
                    exit();
                }
            } else {
                $_SESSION['login_error'] = "Veuillez remplir tous les champs.";
                header("Location: login.php");
                exit();
            }

        case "logout":
            session_unset();
            session_destroy();
            header("Location: index.php");
            exit();
    }
}
?>
