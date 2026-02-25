<?php
/**
 * checkout.php
 *
 * Page de récapitulatif de commande (étape intermédiaire avant le paiement).
 *
 * Rôle dans le projet :
 *   Affiche le résumé de la commande avec le détail des articles du panier,
 *   le calcul des taxes (TPS 5% + TVQ 9,975%) et les frais de livraison (9,99$).
 *   Lorsque l'utilisateur confirme, les données sont stockées en session et
 *   il est redirigé vers wallet.php pour le débit du solde.
 *
 * Fonctionnement :
 *   1. Vérifie que l'utilisateur est connecté.
 *   2. Charge le panier actif et calcule : sous-total + TPS + TVQ + livraison = total.
 *   3. Affiche un panneau récapitulatif (montants) + la liste des articles.
 *   4. Si POST "aller_wallet" :
 *      - Stocke en session checkout_id_panier, checkout_quantite_totale, checkout_total_final.
 *      - Redirige vers wallet.php.
 *      Ces variables de session permettent à wallet.php de valider que le panier
 *      n'a pas changé entre les deux pages.
 *
 * @project VortexVR – Boutique de casques VR
 */

require_once "inc/header.php";

// Seul un utilisateur connecté peut accéder au checkout.
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: compte.php");
    exit;
}

$idUtilisateur = $_SESSION['id_utilisateur'];
$panierManager = new PanierManager();

// Chargement du panier et initialisation des totaux.
$panier         = $panierManager->getPanierActifPourUtilisateur($idUtilisateur);
$articles       = [];
$nombreTypes    = 0;   // Nombre de types de casques distincts (non utilisé dans l'affichage)
$nombreArticles = 0;   // Nombre total d'articles (somme des quantités)
$montantArticles = 0.0;
$livraison       = 9.99;  // Frais de livraison fixes
$tps             = 0.0;   // Taxe fédérale (5% du montant articles)
$tvq             = 0.0;   // Taxe provinciale (9,975% du montant articles)
$totalFinal      = 0.0;

// Calcul des totaux si le panier contient des articles.
if ($panier !== null) {
    $articles = $panierManager->getArticlesDuPanier($panier['id_panier']);

    if (!empty($articles)) {
        $montantArticles = $panierManager->calculerTotal($articles);

        // Somme des quantités pour afficher "X articles au total".
        foreach ($articles as $article) {
            $nombreArticles += $article['quantite'];
        }

        // Calcul des taxes québécoises (sur le sous-total avant livraison).
        $tps = $montantArticles * 0.05;        // TPS 5%
        $tvq = $montantArticles * 0.09975;     // TVQ 9,975%

        $totalFinal = $montantArticles + $livraison + $tps + $tvq;

    } else {
        // Panier vide : pas de livraison ni de montant total.
        $livraison  = 0.0;
        $totalFinal = 0.0;
    }

} else {
    // Aucun panier trouvé (premier visiteur sans achat).
    $livraison  = 0.0;
    $totalFinal = 0.0;
}

/**
 * Formate un montant avec séparateur de milliers et 2 décimales.
 *
 * @param float $montant Montant à afficher.
 * @return string Ex. "1 299,99"
 */
function fmt($montant)
{
    return number_format($montant, 2, ',', ' ');
}

// --- Passage à la page de paiement (wallet.php) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aller_wallet'])) {

    // Le panier doit contenir des articles pour pouvoir continuer.
    if (empty($articles)) {
        header("Location: checkout.php");
        exit;
    }

    // Stockage des données de commande en session pour wallet.php.
    // wallet.php recalcule et vérifie que le total en session correspond
    // au total réel (sécurité contre la modification du panier entre les pages).
    $_SESSION['checkout_id_panier']       = $panier['id_panier'];
    $_SESSION['checkout_quantite_totale'] = $nombreArticles;
    $_SESSION['checkout_total_final']     = $totalFinal;

    header("Location: wallet.php");
    exit;
}
?>

<!-- =====================================================
     PAGE CHECKOUT : récapitulatif et confirmation de commande
     ===================================================== -->
<section class="checkout">
    <div class="checkout-container">

        <!-- Panneau de résumé (montants, taxes, total) -->
        <section class="checkout-section">
            <div class="checkout-box">

                <div class="Pannel-div">
                    <span class="Pannel-infos">Nombre d'articles :</span>
                    <span class="Pannel-value"><?= $nombreArticles ?></span>
                </div>

                <div class="Pannel-div">
                    <span class="Pannel-infos">Montant des articles :</span>
                    <span class="Pannel-value"><?= fmt($montantArticles) ?> $</span>
                </div>

                <div class="Pannel-div">
                    <span class="Pannel-infos">Frais de livraison :</span>
                    <span class="Pannel-value"><?= fmt($livraison) ?> $</span>
                </div>

                <!-- Taxes québécoises -->
                <div class="Pannel-div">
                    <span class="Pannel-infos">TPS (5 %) :</span>
                    <span class="Pannel-value"><?= fmt($tps) ?> $</span>
                </div>

                <div class="Pannel-div">
                    <span class="Pannel-infos">TVQ (9,975 %) :</span>
                    <span class="Pannel-value"><?= fmt($tvq) ?> $</span>
                </div>

                <!-- Total final (articles + livraison + taxes) -->
                <div class="Pannel-div total">
                    <span class="Pannel-infos">Montant total :</span>
                    <span class="Pannel-value"><?= fmt($totalFinal) ?> $</span>
                </div>

                <!-- Bouton de confirmation : transfère vers wallet.php via POST -->
                <form method="post" class="checkout-action">
                    <button type="submit" name="aller_wallet" class="checkout-confirm-button">
                        Passer la commande
                    </button>
                </form>
            </div>
        </section>

        <!-- Liste des articles du panier (affichage récapitulatif, non modifiable ici) -->
        <section class="section-produits">
            <?php if (empty($articles)): ?>
                <p class="checkout-empty-message">Votre panier est vide pour le moment.</p>
            <?php else: ?>
                <div class="checkout-products-grid">
                    <?php foreach ($articles as $article): ?>
                        <?php $image = 'images/' . $article['image_fichier']; ?>

                        <article class="checkout-product-card">
                            <h3 class="product-name"><?= htmlspecialchars($article['nom_casque']) ?></h3>

                            <div class="product-image-box">
                                <img src="<?= htmlspecialchars($image) ?>"
                                     alt="<?= htmlspecialchars($article['nom_casque']) ?>">
                            </div>

                            <p class="product-quantity">
                                Quantité dans le panier : <?= (int) $article['quantite'] ?>
                            </p>

                            <div class="product-bottom-row">
                                <!-- Boutons de contrôle de quantité (non fonctionnels ici, UI décorative) -->
                                <div class="product-qty-controls">
                                    <button type="button" class="qty-btn">-</button>
                                    <button type="button" class="qty-btn">+</button>
                                </div>
                                <span class="product-price"><?= fmt($article['prix_unitaire']) ?> $</span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</section>

<?php require_once "inc/footer.php"; ?>
