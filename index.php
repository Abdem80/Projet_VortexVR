<?php
/**
 * index.php – Page d'accueil de la boutique VortexVR
 *
 * Rôle dans le projet :
 *   Point d'entrée principal du site. Affiche les 3 meilleurs casques du catalogue
 *   (premier tri alphabétique) et permet de les ajouter directement au panier
 *   sans passer par le catalogue.
 *
 * Fonctionnement :
 *   1. Chargement des 3 premiers casques via CasqueManager::getTroisCasques().
 *   2. Si une requête POST contient "ajouter_panier" :
 *      a. Vérifie que l'utilisateur est connecté (sinon → compte.php).
 *      b. Récupère le casque et vérifie le stock.
 *      c. Crée le panier si nécessaire (premier achat).
 *      d. Ajoute ou incrémente l'article via PanierManager.
 *   3. Affiche les 3 casques avec image, nom, prix et bouton "Ajouter au panier".
 *   4. Bouton de navigation vers le catalogue complet.
 *
 * Note : la logique d'ajout au panier a été empruntée de catalogue.php (Sèdrick)
 * et adaptée pour l'accueil par William.
 *
 * @project VortexVR – Boutique de casques VR
 */

require_once "inc/header.php";

// Chargement des classes nécessaires.
include_once "classe/CasqueManager.php";
include_once "classe/PanierManager.php";

$casqueManager = new CasqueManager();
$panierManager = new PanierManager();

// Récupération des 3 premiers casques pour la vitrine de l'accueil.
$topTroisCasque = $casqueManager->getTroisCasques();

// Variables de feedback pour l'utilisateur (succès ou erreur d'ajout au panier).
$message = null;
$erreur  = null;

// --- Traitement de l'ajout au panier depuis l'accueil ---
// Emprunté de catalogue.php (Sèdrick) et adapté par William.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_panier'])) {

    // Seul un utilisateur connecté peut ajouter au panier.
    if (!isset($_SESSION['id_utilisateur'])) {
        header("Location: compte.php");
        exit;
    }

    $idUtilisateur = (int) $_SESSION['id_utilisateur'];
    $idCasque      = (int) ($_POST['id_casque'] ?? 0);

    // Validation de l'existence du casque et de son stock.
    $casque = $casqueManager->getCasqueParId($idCasque);

    if (!$casque) {
        $erreur = "Casque introuvable.";
    } elseif ((int)$casque['stock'] <= 0) {
        $erreur = "Stock insuffisant.";
    } else {
        // Récupération ou création du panier de l'utilisateur.
        $panier = $panierManager->getPanierActifPourUtilisateur($idUtilisateur);

        if ($panier === null) {
            // Premier achat : le panier n'existe pas encore, on le crée.
            $idPanier = $panierManager->creerPanierPourUtilisateur($idUtilisateur);
        } else {
            $idPanier = (int) $panier['id_panier'];
        }

        // Ajout ou incrémentation de l'article dans le panier.
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
 * @return string Montant formaté (ex. "1 299,99").
 */
function fmt($m)
{
    return number_format((float)$m, 2, ',', ' ');
}
?>

<!-- =====================================================
     PAGE D'ACCUEIL : vitrine des 3 meilleurs casques
     ===================================================== -->
<section class="acc-whole">

    <!-- Bannière de bienvenue -->
    <div class="acc-titre">
        <span>Bienvenue sur le meilleur site de vente de casques VR</span>
    </div>

    <!-- Messages de feedback (succès ou erreur d'ajout au panier) -->
    <?php if ($message): ?>
        <p class="acc-succes"><?= htmlspecialchars($message) ?></p>
    <?php endif; ?>

    <?php if ($erreur): ?>
        <p class="acc-err"><?= htmlspecialchars($erreur) ?></p>
    <?php endif; ?>

    <!-- Bandeau "meilleures offres" -->
    <div class="acc-offre">
        <span>Voici nos meilleures offres du moment</span>
    </div>

    <!-- Affichage des 3 casques en vitrine -->
    <div class="acc-top3">
        <?php foreach ($topTroisCasque as $casque): ?>
            <div class="acc-produit">

                <!-- Image du casque -->
                <div class="acc-img">
                    <img src="images/<?= htmlspecialchars($casque['image_fichier']) ?>" alt="<?= htmlspecialchars($casque['nom_casque']) ?>">
                </div>

                <!-- Nom et prix -->
                <div class="acc-info">
                    <span class="acc-nom"><?= htmlspecialchars($casque['nom_casque']) ?></span>
                    <span class="acc-prix"><?= fmt($casque['prix']) ?> $</span>
                </div>

                <!-- Formulaire d'ajout au panier (désactivé si stock = 0) -->
                <form method="post" class="acc-panier">
                    <input type="hidden" name="id_casque" value="<?= htmlspecialchars($casque['id_casque']) ?>">
                    <button type="submit" name="ajouter_panier" <?= ((int)$casque['stock'] <= 0) ? 'disabled' : '' ?>>
                        Ajouter au panier
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Boutons de navigation entre les casques (contrôlés par script.js) -->
    <div class="acc-switch-casque">
        <button id="avant">◄</button>
        <button id="apres">►</button>
    </div>

    <!-- Bouton de navigation vers le catalogue complet -->
    <div class="acc-catalogue">
        <a href="catalogue.php" class="acc-btn-catalogue">Explorer le catalogue</a>
    </div>
</section>

<?php require_once "inc/footer.php"; ?>
