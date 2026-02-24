<?php require_once "inc/header.php";

include_once "classe/CasqueManager.php";
include_once "classe/PanierManager.php";


$casqueManager = new CasqueManager();
$panierManager = new PanierManager();

$topTroisCasque = $casqueManager->getTroisCasques();

$message = null;
$erreur = null;

//ici une partie de code emprunter au catalogue de sedrick pour ajouter un produit dans le panier a partir de l'acceuil (william)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_panier'])) {

    if (!isset($_SESSION['id_utilisateur'])) {
        header("Location: compte.php");
        exit;
    }

    $idUtilisateur = (int) $_SESSION['id_utilisateur'];
    $idCasque = (int) ($_POST['id_casque'] ?? 0);

    $casque = $casqueManager->getCasqueParId($idCasque);

    if (!$casque) {
        $erreur = "Casque introuvable.";
    } elseif ((int)$casque['stock'] <= 0) {
        $erreur = "Stock insuffisant.";
    } else {
        $panier = $panierManager->getPanierActifPourUtilisateur($idUtilisateur);

        if ($panier === null) {
            $idPanier = $panierManager->creerPanierPourUtilisateur($idUtilisateur);
        } else {
            $idPanier = (int) $panier['id_panier'];
        }
        $panierManager->ajouterOuIncrementerArticle(
            $idPanier,
            $idCasque,
            (float)$casque['prix']
        );

        $message = "Casque ajouté au panier.";
    }
}

function fmt($m) {
    return number_format((float)$m, 2, ',', ' ');
}

//fin de l'emprunt
?>

<section class="acc-whole">
    <div class="acc-titre">
        <span>Bienvenue sur le meilleur site de ventre de casque Vr</span>
    </div>
    <!-- les six prochaine ligne sont emprunter comme ci-haut sauf pour le css -->
    <?php if ($message): ?>
    <p class="acc-succes"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($erreur): ?>
    <p class="acc-err"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>
    <div class="acc-offre">
        <span>Voici nos meilleurs Offres du moments</span>
    </div>
    <div class="acc-top3">
        <?php foreach($topTroisCasque as $casque){?>
            <div class="acc-produit">
                <div class="acc-img">
                    <img src="images/<?php echo htmlspecialchars($casque['image_fichier'])?>">
                </div>
                <div class="acc-info">
                    <span class="acc-nom"><?php echo htmlspecialchars($casque['nom_casque'])?></span>
                    <span class="acc-prix"><?php echo fmt($casque['prix']) ?></span>
                </div>
                <form method="post" class="acc-panier">
                    <input type="hidden" name="id_casque" value="<?php echo htmlspecialchars($casque['id_casque']) ?>">
                    <button type="submit" name="ajouter_panier" <?= ((int)$casque['stock'] <= 0) ? 'disabled' : '' ?>>Ajouter au panier</button>
                </form>
            </div>
        <?php }?>
    </div>
    
    <div class="acc-switch-casque">
        <button id="avant">◄</button>
        <button id="apres">►</button>
    </div>

    <div class="acc-catalogue">
        <a href="catalogue.php" class="acc-btn-catalogue">Explorer le catalogue</a>
    </div>
</section>
<?php require_once "inc/footer.php"; ?>
