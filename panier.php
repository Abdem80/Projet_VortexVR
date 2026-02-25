<?php
/**
 * panier.php
 *
 * Page de gestion du panier d'achat de la boutique VortexVR.
 *
 * Rôle dans le projet :
 *   Affiche les articles dans le panier de l'utilisateur connecté,
 *   avec les options de modification de quantité et de suppression.
 *   Permet également de passer à l'étape suivante (checkout.php).
 *
 * Fonctionnement :
 *   1. Vérifie que l'utilisateur est connecté (sinon → compte.php).
 *   2. Traitement des actions POST (avant tout affichage HTML) :
 *      - "supprimerArticle" : supprime un article via PanierManager.
 *      - "plusQte"          : incrémente la quantité d'un article (+1).
 *      - "moinsQte"         : décrémente la quantité d'un article (-1, min 1).
 *      Toutes ces actions redirigent (PRG) pour éviter la re-soumission.
 *   3. Chargement du panier actif et calcul du sous-total.
 *   4. Affichage de chaque article avec cases à cocher, image, nom, prix,
 *      contrôles de quantité et bouton de suppression.
 *   5. Si le panier n'est pas vide : bouton "Passer à la commande" (→ checkout.php).
 *
 * @author  William (ajout des actions de quantité et suppression par article)
 * @project VortexVR – Boutique de casques VR
 */

require_once "inc/header.php";

// Seul un utilisateur connecté peut accéder à son panier.
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: compte.php");
    exit;
}

$idUtilisateur = $_SESSION['id_utilisateur'];
$panierManager = new PanierManager();

// Récupération du panier actif (peut être null si aucun achat précédent).
$panier = $panierManager->getPanierActifPourUtilisateur($idUtilisateur);

// Initialisation des variables d'affichage.
$produits        = [];
$Soustotal       = 0;
$idArticlePanier = 0;

// --- Traitement des actions POST (PRG : toutes redirigent après action) ---

// Suppression d'un article du panier (bouton "Supprimer").
if (isset($_POST['supprimerArticle'])) {
    $panierManager->supprimerArticleUtilisateur($idUtilisateur, $idArticlePanier = $_POST['id_article_panier']);
    header("Location: panier.php");
    exit(1);
}

// Augmentation de la quantité d'un article (+1).
if (isset($_POST['plusQte'])) {
    $panierManager->addQteCasque($idUtilisateur, $idArticlePanier = $_POST['id_article_panier']);
    header("Location: panier.php");
    exit(1);
}

// Diminution de la quantité d'un article (-1, ne descend pas sous 1).
if (isset($_POST['moinsQte'])) {
    $panierManager->rmvQteCasque($idUtilisateur, $idArticlePanier = $_POST['id_article_panier']);
    header("Location: panier.php");
    exit(1);
}

// --- Chargement des articles et calcul du sous-total ---
if ($panier !== NULL) {
    $produits = $panierManager->getArticlesDuPanier($panier['id_panier']);

    if (!empty($produits)) {
        // calculerTotal() enrichit aussi chaque article d'une clé 'sous_total'.
        $Soustotal = $panierManager->calculerTotal($produits);
    }
}
?>

<!-- =====================================================
     PAGE PANIER : affichage et gestion des articles
     ===================================================== -->
<section class="panier">

    <!-- Affichage du sous-total avant taxes -->
    <section class="section-Total">
        <div class="panier-Total produit-bold">
            <span>Sous Total :
                <?php if (isset($Soustotal)) {
                    echo htmlspecialchars($Soustotal) . ' $';
                } else {
                    echo "0 $";
                } ?>
            </span>
        </div>
    </section>

    <!-- Grille des articles du panier -->
    <section class="section-produit">
        <?php if (!empty($produits)) {
            foreach ($produits as $produit): ?>

            <div class="produit">

                <!-- Case à cocher de sélection de l'article -->
                <div class="produit-box">
                    <input type="checkbox" name="articleSelectionner[]"
                           value="<?= $produit['id_article_panier'] ?>"
                           class="produit-checkbox" checked>
                </div>

                <!-- Nom du casque -->
                <div class="produit-nom produit-bold">
                    <span><?= htmlspecialchars($produit['nom_casque']) ?></span>
                </div>

                <!-- Image du casque -->
                <div class="produit-img">
                    <img src="images/<?= htmlspecialchars($produit['image_fichier']) ?>"
                         alt="<?= htmlspecialchars($produit['nom_casque']) ?>">
                </div>

                <!-- Description du casque -->
                <div class="produit-desc produit-bold">
                    <span><?= htmlspecialchars($produit['description']) ?></span>
                </div>

                <!-- Prix unitaire -->
                <div class="produit-prix produit-bold">
                    <span>Prix: <?= htmlspecialchars($produit['prix_unitaire']) . "$" ?></span>
                </div>

                <!-- Contrôles de quantité : boutons - et + avec formulaires POST séparés -->
                <div class="produit-qte">
                    <!-- Bouton diminuer la quantité (-1) -->
                    <form method="POST">
                        <input type="hidden" name="id_article_panier" value="<?= $produit['id_article_panier'] ?>">
                        <button type="submit" class="produit-button" name="moinsQte">- 1</button>
                    </form>

                    <div>
                        <span>quantité: <?= htmlspecialchars($produit['quantite']) ?></span>
                    </div>

                    <!-- Bouton augmenter la quantité (+1) -->
                    <form method="POST">
                        <input type="hidden" name="id_article_panier" value="<?= $produit['id_article_panier'] ?>">
                        <button type="submit" class="produit-button" name="plusQte">+ 1</button>
                    </form>
                </div>

                <!-- Bouton de suppression de l'article du panier -->
                <div class="produit-suppr">
                    <form method="POST">
                        <input type="hidden" name="id_article_panier" value="<?= $produit['id_article_panier'] ?>">
                        <button type="submit" name="supprimerArticle" class="supprimerArticle">Supprimer</button>
                    </form>
                </div>

            </div>

        <?php endforeach;
        } ?>
    </section>

    <!-- Bouton "Passer à la commande" : visible uniquement si le panier n'est pas vide -->
    <?php if (!empty($produits)): ?>
        <section class="section-commande">
            <form method="POST">
                <!-- Note : le bouton redirige vers checkout.php (via JS dans script.js) -->
                <button type="button" id="commandePanier">Passer à la commande</button>
            </form>
        </section>
    <?php endif; ?>

</section>

<?php require_once "inc/footer.php"; ?>
