<?php require_once "inc/header.php";

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: compte.php");
    exit;
}

$idUtilisateur = $_SESSION['id_utilisateur'];

$panierManager = new PanierManager();

$panier = $panierManager->getPanierActifPourUtilisateur($idUtilisateur);

$articles = [];
$nombreTypes = 0;
$nombreArticles = 0;
$montantArticles = 0.0;
$livraison = 9.99;        
$tps = 0.0;
$tvq = 0.0;
$totalFinal = 0.0;

if ($panier !== null) {

    $articles = $panierManager->getArticlesDuPanier( $panier['id_panier']);

    if (!empty($articles)) {
        $montantArticles = $panierManager->calculerTotal($articles);

        foreach ($articles as $article) {
            $nombreArticles += $article['quantite'];
        }

        $tps = $montantArticles * 0.05;        
        $tvq = $montantArticles * 0.09975;     

        $totalFinal = $montantArticles + $livraison + $tps + $tvq;

    } else {
        $livraison = 0.0;
        $totalFinal = 0.0;
    }

} else {
    $livraison = 0.0;
    $totalFinal = 0.0;
}

function fmt($montant) {
    return number_format($montant, 2, ',', ' ');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aller_wallet'])) {

    if (empty($articles)) {
        header("Location: checkout.php");
        exit;
    }

    $_SESSION['checkout_id_panier'] =  $panier['id_panier'];
    $_SESSION['checkout_quantite_totale'] = $nombreArticles;
    $_SESSION['checkout_total_final'] = $totalFinal;

    header("Location: wallet.php");
    exit;
}
?>

<section class="checkout">
    <div class="checkout-container">
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

                <div class="Pannel-div">
                    <span class="Pannel-infos">TPS (5 %) :</span>
                    <span class="Pannel-value"><?= fmt($tps) ?> $</span>
                </div>

                <div class="Pannel-div">
                    <span class="Pannel-infos">TVQ (9,975 %) :</span>
                    <span class="Pannel-value"><?= fmt($tvq) ?> $</span>
                </div>

                <div class="Pannel-div total">
                    <span class="Pannel-infos">Montant total :</span>
                    <span class="Pannel-value"><?= fmt($totalFinal) ?> $</span>
                </div>

                <form method="post" class="checkout-action">
                    <button type="submit" name="aller_wallet" class="checkout-confirm-button">
                        Passer la commande
                    </button>
                </form>
            </div>

        </section>

        <section class="section-produits">

            <?php if (empty($articles)): ?>
                <p class="checkout-empty-message">
                    Votre panier est vide pour le moment.
                </p>
            <?php else: ?>

            <div class="checkout-products-grid">

                <?php foreach ($articles as $article): ?>
                    <?php
                        $image = 'images/' . $article['image_fichier'];
                    ?>

                    <article class="checkout-product-card">
                        <h3 class="product-name"><?= htmlspecialchars($article['nom_casque']) ?></h3>

                        <div class="product-image-box">
                            <img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($article['nom_casque']) ?>">
                        </div>

                        <p class="product-quantity">
                            Quantité dans le panier : <?= (int) $article['quantite'] ?>
                        </p>

                        <div class="product-bottom-row">
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
