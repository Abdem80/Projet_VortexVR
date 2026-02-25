<?php
/**
 * catalogue.php
 *
 * Catalogue interactif de casques VR de la boutique VortexVR.
 *
 * Rôle dans le projet :
 *   Affiche l'ensemble du catalogue avec un système de filtres dynamiques
 *   (recherche textuelle, marque, prix min/max, tri). Les filtres sont
 *   envoyés en GET (URL partageable). L'ajout au panier est fait en POST.
 *
 * Fonctionnement :
 *   1. Lecture des paramètres de filtre depuis $_GET.
 *   2. Récupération des casques filtrés via CasqueManager::getCasquesFiltres().
 *   3. Si POST "ajouter_panier" : ajout de l'article au panier (même logique que index.php).
 *   4. Affichage des filtres avec mémorisation des valeurs saisies (selected/value).
 *   5. Affichage des cartes de casques en grille.
 *
 * @author  Sèdrick (logique de filtrage et affichage)
 * @project VortexVR – Boutique de casques VR
 */

require_once "inc/header.php";
include_once "classe/CasqueManager.php";

$casqueManager = new CasqueManager();
$panierManager = new PanierManager();

// --- Lecture des filtres depuis la querystring (GET) ---
// L'utilisation de GET permet de partager l'URL filtrée et de rafraîchir sans re-POSTer.
$q        = trim($_GET['q']        ?? '');  // Recherche textuelle
$idMarque = $_GET['id_marque']     ?? '';   // Filtre par marque
$prixMin  = $_GET['prix_min']      ?? '';   // Prix minimum
$prixMax  = $_GET['prix_max']      ?? '';   // Prix maximum
$sort     = $_GET['sort']          ?? 'nom_asc'; // Ordre de tri (défaut : nom A→Z)

// Tableau de filtres passé à getCasquesFiltres() — correspondance directe avec les paramètres GET.
$filtres = [
    'q'          => $q,
    'id_marque'  => $idMarque,
    'prix_min'   => $prixMin,
    'prix_max'   => $prixMax,
    'sort'       => $sort
];

// Chargement des données pour les listes déroulantes et la grille.
$marques = $casqueManager->getMarques();
$casques = $casqueManager->getCasquesFiltres($filtres);

// Variables de feedback pour l'ajout au panier.
$message = null;
$erreur  = null;

// --- Traitement de l'ajout au panier (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_panier'])) {

    if (!isset($_SESSION['id_utilisateur'])) {
        header("Location: compte.php");
        exit;
    }

    $idUtilisateur = (int) $_SESSION['id_utilisateur'];
    $idCasque      = (int) ($_POST['id_casque'] ?? 0);

    $casque = $casqueManager->getCasqueParId($idCasque);

    if (!$casque) {
        $erreur = "Casque introuvable.";
    } elseif ((int)$casque['stock'] <= 0) {
        $erreur = "Stock insuffisant.";
    } else {
        // Création du panier si nécessaire, puis ajout/incrément de l'article.
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

/**
 * Formate un montant en dollars avec séparateur de milliers et 2 décimales.
 *
 * @param float|string $m Montant à formater.
 * @return string Ex. "1 299,99"
 */
function fmt($m)
{
    return number_format((float)$m, 2, ',', ' ');
}
?>

<section class="catalogue">
    <div class="catalogue-container">

        <!-- En-tête du catalogue -->
        <section class="catalogue-header">
            <h2>Catalogue</h2>
        </section>

        <!-- Messages de feedback (succès ou erreur d'ajout au panier) -->
        <?php if ($message): ?>
            <p class="catalogue-success"><?= htmlspecialchars($message) ?></p>
        <?php endif; ?>

        <?php if ($erreur): ?>
            <p class="catalogue-error"><?= htmlspecialchars($erreur) ?></p>
        <?php endif; ?>

        <!-- =====================================================
             FORMULAIRE DE FILTRES (méthode GET pour URL partageable)
             ===================================================== -->
        <form method="get" class="catalogue-filters">

            <!-- Filtre texte : recherche dans nom et description -->
            <div class="filter-group">
                <label for="q">Recherche</label>
                <input type="text" id="q" name="q" value="<?= htmlspecialchars($q) ?>" placeholder="Nom ou description">
            </div>

            <!-- Filtre marque : <select> peuplé depuis la BDD -->
            <div class="filter-group">
                <label for="id_marque">Marque</label>
                <select id="id_marque" name="id_marque">
                    <option value="">Toutes</option>
                    <?php foreach ($marques as $m): ?>
                        <!-- "selected" maintenu si c'est la marque actuellement filtrée -->
                        <option value="<?= (int)$m['id_marque'] ?>" <?= ((string)$idMarque === (string)$m['id_marque']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($m['nom_marque']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filtre prix minimum -->
            <div class="filter-group">
                <label for="prix_min">Prix min</label>
                <input type="number" min=0 step="0.01" id="prix_min" name="prix_min" value="<?= htmlspecialchars((string)$prixMin) ?>">
            </div>

            <!-- Filtre prix maximum -->
            <div class="filter-group">
                <label for="prix_max">Prix max</label>
                <input type="number" min=0 step="0.01" id="prix_max" name="prix_max" value="<?= htmlspecialchars((string)$prixMax) ?>">
            </div>

            <!-- Sélection du tri -->
            <div class="filter-group">
                <label for="sort">Tri</label>
                <select id="sort" name="sort">
                    <option value="nom_asc"  <?= ($sort === 'nom_asc')  ? 'selected' : '' ?>>Nom (A→Z)</option>
                    <option value="nom_desc" <?= ($sort === 'nom_desc') ? 'selected' : '' ?>>Nom (Z→A)</option>
                    <option value="prix_asc" <?= ($sort === 'prix_asc') ? 'selected' : '' ?>>Prix (croissant)</option>
                    <option value="prix_desc"<?= ($sort === 'prix_desc')? 'selected' : '' ?>>Prix (décroissant)</option>
                </select>
            </div>

            <div class="filter-actions">
                <button type="submit" class="filter-btn">Filtrer</button>
                <!-- Lien "Réinitialiser" : recharge catalogue.php sans paramètres GET -->
                <a class="filter-link" href="catalogue.php">Réinitialiser</a>
            </div>
        </form>

        <!-- =====================================================
             GRILLE DES CASQUES
             ===================================================== -->
        <?php if (empty($casques)): ?>
            <p class="catalogue-empty">Aucun casque ne correspond à votre recherche.</p>
        <?php else: ?>
            <div class="catalogue-grid">
                <?php foreach ($casques as $c): ?>
                    <?php $img = "images/" . $c['image_fichier']; ?>

                    <article class="catalogue-card">

                        <h3 class="catalogue-title"><?= htmlspecialchars($c['nom_casque']) ?></h3>
                        <p class="catalogue-brand"><?= htmlspecialchars($c['nom_marque']) ?></p>

                        <!-- Image du casque -->
                        <div class="catalogue-imgbox">
                            <img class="catalogue-img"
                                 src="<?= htmlspecialchars($img) ?>"
                                 alt="<?= htmlspecialchars($c['nom_casque']) ?>">
                        </div>

                        <p class="catalogue-desc"><?= htmlspecialchars($c['description']) ?></p>

                        <!-- Prix et stock -->
                        <div class="catalogue-meta">
                            <span class="catalogue-price"><?= fmt($c['prix']) ?> $</span>
                            <span class="catalogue-stock">Stock : <?= (int)$c['stock'] ?></span>
                        </div>

                        <!-- Formulaire d'ajout au panier (bouton désactivé si stock = 0) -->
                        <form method="post" class="catalogue-actions">
                            <input type="hidden" name="id_casque" value="<?= (int)$c['id_casque'] ?>">
                            <button type="submit" name="ajouter_panier" class="catalogue-btn"
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