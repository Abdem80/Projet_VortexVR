<?php require_once "inc/header.php"; 
include_once "classe/CasqueManager.php";

$casqueManager = new CasqueManager();
$panierManager = new PanierManager();

$q = trim($_GET['q'] ?? '');
$idMarque = $_GET['id_marque'] ?? '';
$prixMin = $_GET['prix_min'] ?? '';
$prixMax = $_GET['prix_max'] ?? '';
$sort = $_GET['sort'] ?? 'nom_asc';

$filtres = [
    'q' => $q,
    'id_marque' => $idMarque,
    'prix_min' => $prixMin,
    'prix_max' => $prixMax,
    'sort' => $sort
];

$marques = $casqueManager->getMarques();
$casques = $casqueManager->getCasquesFiltres($filtres);

$message = null;
$erreur = null;

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

?>
<section class="catalogue">
    <div class="catalogue-container">

        <section class="catalogue-header">
            <h2>Catalogue</h2>
        </section>

        <?php if ($message): ?>
            <p class="catalogue-success"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($erreur): ?>
            <p class="catalogue-error"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <form method="get" class="catalogue-filters">

            <div class="filter-group">
                <label for="q">Recherche</label>
                <input type="text" id="q" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nom ou description">
            </div>

            <div class="filter-group">
                <label for="id_marque">Marque</label>
                <select id="id_marque" name="id_marque">
                    <option value="">Toutes</option>
                    <?php foreach ($marques as $m): ?>
                        <option value="<?= (int)$m['id_marque'] ?>" <?= ((string)$idMarque === (string)$m['id_marque']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['nom_marque']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="prix_min">Prix min</label>
                <input type="number"  min=0 step="0.01" id="prix_min" name="prix_min" value="<?= htmlspecialchars((string)$prixMin) ?>">
            </div>

            <div class="filter-group">
                <label for="prix_max">Prix max</label>
                <input type="number" min=0 step="0.01" id="prix_max" name="prix_max" value="<?= htmlspecialchars((string)$prixMax) ?>">
            </div>

            <div class="filter-group">
                <label for="sort">Tri</label>
                <select id="sort" name="sort">
                    <option value="nom_asc"  <?= ($sort === 'nom_asc') ? 'selected' : '' ?>>Nom (A→Z)</option>
                    <option value="nom_desc" <?= ($sort === 'nom_desc') ? 'selected' : '' ?>>Nom (Z→A)</option>
                    <option value="prix_asc" <?= ($sort === 'prix_asc') ? 'selected' : '' ?>>Prix (croissant)</option>
                    <option value="prix_desc"<?= ($sort === 'prix_desc') ? 'selected' : '' ?>>Prix (décroissant)</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="filter-btn">Filtrer</button>
                <a class="filter-link" href="catalogue.php">Réinitialiser</a>
            </div>

        </form>

        <?php if (empty($casques)): ?>
            <p class="catalogue-empty">Aucun casque ne correspond à votre recherche.</p>
        <?php else: ?>

            <div class="catalogue-grid">
                <?php foreach ($casques as $c): ?>
                    <?php $img = "images/" . $c['image_fichier']; ?>

                    <article class="catalogue-card">

                        <h3 class="catalogue-title"><?= htmlspecialchars($c['nom_casque']) ?></h3>

                        <p class="catalogue-brand"><?= htmlspecialchars($c['nom_marque']) ?></p>

                        <div class="catalogue-imgbox">
                            <img class="catalogue-img"
                                src="<?= htmlspecialchars($img) ?>"
                                alt="<?= htmlspecialchars($c['nom_casque']) ?>">
                        </div>

                        <p class="catalogue-desc"><?= htmlspecialchars($c['description']) ?></p>

                        <div class="catalogue-meta">
                            <span class="catalogue-price"><?= fmt($c['prix']) ?> $</span>
                            <span class="catalogue-stock">Stock : <?= (int)$c['stock'] ?></span>
                        </div>

                        <form method="post" class="catalogue-actions">
                            <input type="hidden" name="id_casque" value="<?= (int)$c['id_casque'] ?>">

                            <button type="submit"
                                    name="ajouter_panier"
                                    class="catalogue-btn"
                                    <?= ((int)$c['stock'] <= 0) ? 'disabled' : '' ?>>
                                Ajouter au panier
                            </button>
                        </form>

                    </article>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </div>
</section>
<?php require_once "inc/footer.php"; ?>