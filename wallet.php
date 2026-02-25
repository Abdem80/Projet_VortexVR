<?php
/**
 * wallet.php
 *
 * Page de paiement par portefeuille électronique (wallet) de la boutique VortexVR.
 *
 * Rôle dans le projet :
 *   Dernière étape du tunnel d'achat. Affiche le solde disponible de l'utilisateur,
 *   le montant total à débiter (récupéré depuis la session checkout_*) et permet
 *   de confirmer l'achat. En cas de confirmation :
 *   1. Recalcule le total réel depuis la BDD pour éviter toute manipulation.
 *   2. Vérifie que le panier n'a pas changé depuis checkout.php.
 *   3. Débite le solde, crée la commande, vide le panier, nettoie la session.
 *
 * Variables de session requises (initialisées par checkout.php) :
 *   - checkout_id_panier       : ID du panier de la commande
 *   - checkout_quantite_totale : nombre total d'articles
 *   - checkout_total_final     : montant total calculé côté serveur
 *
 * ⚠️ Note développement : la ligne $_SESSION['id_utilisateur'] = 1;
 *    (ligne 3) est un court-circuit de développement à retirer en production.
 *
 * @project VortexVR – Boutique de casques VR
 */

require_once "inc/header.php";

// Seul un utilisateur connecté peut accéder au wallet.
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: compte.php");
    exit;
}

// Vérification que les données de session du checkout sont présentes.
// Si manquantes, l'utilisateur a accédé directement à wallet.php sans passer par checkout.
if (!isset($_SESSION['checkout_id_panier'], $_SESSION['checkout_quantite_totale'], $_SESSION['checkout_total_final'])) {
    header("Location: checkout.php");
    exit;
}

$idUtilisateur = (int) $_SESSION['id_utilisateur'];

// Récupération des données de commande stockées par checkout.php.
$idPanierSession = (int)   $_SESSION['checkout_id_panier'];
$qteSession      = (int)   $_SESSION['checkout_quantite_totale'];
$totalSession    = (float) $_SESSION['checkout_total_final'];

$utilisateurManager = new UtilisateurManager();
$panierManager      = new PanierManager();

// Lecture du solde actuel du wallet de l'utilisateur.
$solde   = $utilisateurManager->getSolde($idUtilisateur);
$message = null;
$erreur  = null;

/**
 * Formate un montant avec séparateur de milliers et 2 décimales.
 *
 * @param float $m Montant à formater.
 * @return string Ex. "1 299,99"
 */
function fmt($m)
{
    return number_format($m, 2, ',', ' ');
}

// --- Traitement de la validation de l'achat ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['valider_achat'])) {

    $panierActif = $panierManager->getPanierActifPourUtilisateur($idUtilisateur);

    if ($panierActif === null) {
        $erreur = "Aucun panier actif.";
    } else {
        $idPanierReel = $panierActif['id_panier'];

        // Sécurité : vérification que le panier n'a pas changé depuis checkout.php.
        if ($idPanierReel !== $idPanierSession) {
            $erreur = "Votre panier a changé. Retournez au checkout.";
        } else {
            $articles = $panierManager->getArticlesDuPanier($idPanierReel);

            if (empty($articles)) {
                $erreur = "Votre panier est vide.";
            } else {
                // Recalcul du total réel depuis la BDD (sécurité anti-manipulation).
                $montantArticles = $panierManager->calculerTotal($articles);
                $livraison  = 9.99;
                $tps        = $montantArticles * 0.05;
                $tvq        = $montantArticles * 0.09975;
                $totalReel  = $montantArticles + $livraison + $tps + $tvq;

                // Lecture du solde en temps réel (peut avoir changé depuis l'affichage).
                $solde = $utilisateurManager->getSolde($idUtilisateur);

                if ($solde < $totalReel) {
                    $erreur = "Solde insuffisant.";
                } else {
                    try {
                        // 1. Débit du solde.
                        $utilisateurManager->debiterSolde($idUtilisateur, $totalReel);

                        // 2. Enregistrement de la commande en BDD.
                        $panierManager->creerCommande($idUtilisateur, $idPanierReel, $totalReel);

                        // 3. Vidage du panier (les articles sont supprimés, le panier lui-même reste).
                        $panierManager->viderPanier($idPanierReel);

                        // 4. Nettoyage des variables de session du checkout.
                        unset(
                            $_SESSION['checkout_id_panier'],
                            $_SESSION['checkout_quantite_totale'],
                            $_SESSION['checkout_total_final']
                        );

                        // 5. Rafraîchissement du solde affiché et réinitialisation des totaux.
                        $solde       = $utilisateurManager->getSolde($idUtilisateur);
                        $qteSession  = 0;
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

<!-- =====================================================
     PAGE WALLET : confirmation et débit du solde
     ===================================================== -->
<section class="wallet">
    <div class="wallet-container">

        <section class="wallet-header">
            <h2>Wallet</h2>
        </section>

        <!-- Messages de feedback -->
        <?php if ($message): ?>
            <p class="wallet-success"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($erreur): ?>
            <p class="wallet-error"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <!-- Résumé du paiement -->
        <div class="wallet-box">

            <!-- Solde disponible dans le wallet (mis à jour après l'achat) -->
            <div class="wallet-row">
                <span>Solde actuel :</span>
                <span><?= fmt($solde) ?> $</span>
            </div>

            <!-- Quantité totale d'articles dans la commande -->
            <div class="wallet-row">
                <span>Quantité totale d'articles :</span>
                <span><?= $qteSession ?></span>
            </div>

            <!-- Montant total à débiter (taxes et livraison inclus) -->
            <div class="wallet-row wallet-total">
                <span>Montant total du panier :</span>
                <span><?= fmt($totalSession) ?> $</span>
            </div>

            <!-- Actions : retour au checkout ou validation finale de l'achat -->
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
