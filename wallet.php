<?php require_once "inc/header.php"; 

$_SESSION['id_utilisateur'] = 1;

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: compte.php");
    exit;
}

if (!isset($_SESSION['checkout_id_panier'], $_SESSION['checkout_quantite_totale'], $_SESSION['checkout_total_final'])) {
    header("Location: checkout.php");
    exit;
}

$idUtilisateur = (int) $_SESSION['id_utilisateur'];

$idPanierSession = (int) $_SESSION['checkout_id_panier'];
$qteSession = (int) $_SESSION['checkout_quantite_totale'];
$totalSession = (float) $_SESSION['checkout_total_final'];

$utilisateurManager = new UtilisateurManager();
$panierManager = new PanierManager();

$solde = $utilisateurManager->getSolde($idUtilisateur);

$message = null;
$erreur = null;

function fmt($m) {
     return number_format($m, 2, ',', ' ');
     }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_achat'])) {

    $panierActif = $panierManager->getPanierActifPourUtilisateur($idUtilisateur);

    if ($panierActif === null) {
        $erreur = "Aucun panier actif.";
    } else {

        $idPanierReel = $panierActif['id_panier'];

        if ($idPanierReel !== $idPanierSession) {
            $erreur = "Votre panier a changé. Retournez au checkout.";
        } else {

            $articles = $panierManager->getArticlesDuPanier($idPanierReel);

            if (empty($articles)) {
                $erreur = "Votre panier est vide.";
            } else {
                $montantArticles = $panierManager->calculerTotal($articles);

                $livraison = 9.99;
                $tps = $montantArticles * 0.05;
                $tvq = $montantArticles * 0.09975;
                $totalReel = $montantArticles + $livraison + $tps + $tvq;

                $solde = $utilisateurManager->getSolde($idUtilisateur);

                if ($solde < $totalReel) {
                    $erreur = "Solde insuffisant.";
                } else {

                    try {
                        $utilisateurManager->debiterSolde($idUtilisateur, $totalReel);

                        $panierManager->creerCommande($idUtilisateur, $idPanierReel, $totalReel);

                        $panierManager->viderPanier($idPanierReel);

                        unset(
                            $_SESSION['checkout_id_panier'],
                            $_SESSION['checkout_quantite_totale'],
                            $_SESSION['checkout_total_final']
                        );

                        $solde = $utilisateurManager->getSolde($idUtilisateur);
                        $qteSession = 0;
                        $totalSession = 0;
                        $message = "Achat confirmé. Merci !";

                    } catch (Exception $e) {
                        $erreur = "Erreur lors de la validation.";
                    }
                }
            }
        }
    }
}
?>

<section class="wallet">
    <div class="wallet-container">

        <section class="wallet-header">
            <h2>Wallet</h2>
        </section>

        <?php if ($message): ?>
            <p class="wallet-success"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($erreur): ?>
            <p class="wallet-error"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <div class="wallet-box">
            <div class="wallet-row">
                <span>Solde actuel :</span>
                <span><?= fmt($solde) ?> $</span>
            </div>

            <div class="wallet-row">
                <span>Quantité totale d'articles :</span>
                <span><?= $qteSession ?></span>
            </div>

            <div class="wallet-row wallet-total">
                <span>Montant total du panier :</span>
                <span><?= fmt($totalSession) ?> $</span>
            </div>

            <div class="wallet-actions">
                <a class="wallet-btn-secondary" href="checkout.php">Retour au checkout</a>

                <form method="post">
                    <button class="wallet-btn-primary" type="submit" name="valider_achat">
                        Valider l'achat
                    </button>
                </form>
            </div>
        </div>

    </div>
</section>


<?php require_once "inc/footer.php"; ?>
