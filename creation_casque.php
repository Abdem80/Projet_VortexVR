<?php
declare(strict_types=1);

require_once "inc/header.php";
require_once "classe/CreationManager.php";

$idUtilisateur = $_SESSION['id_utilisateur'] ?? null;
if (!$idUtilisateur) {
    echo "<p class='center message-error'>Vous devez être connecté pour créer un casque.</p>";
    require_once "inc/footer.php";
    exit;
}

$creationManager = new CreationManager();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['formCasque']) && $_POST['formCasque'] === "TRUE") {

    $creationManager->creeCasque(
        $_POST['nom_casque'] ?? '',
        $_POST['id_marque'] ?? '',
        $_POST['prix'] ?? '',
        $_POST['stock'] ?? '',
        $_POST['description'] ?? '',
        $_POST['image_fichier'] ?? ''
    );

    header("Location: creation_casque.php?reussi=1");
    exit;
}
?>

<?php if (isset($_GET['reussi'])): ?>
    <section class="crea-section-titre">
        <span>Vous avez réussi à créer votre casque ! Merci !</span>
    </section>
    <?php require_once "inc/footer.php"; ?>
    <?php exit; ?>
<?php endif; ?>

<section class="crea-whole">
    <div class="crea-section-titre">
        <span>Bienvenue dans la création de casque</span>
    </div>

    <form method="POST" class="crea-form">
        <div class="crea-nom">
            <label class="produit-bold">Nom du casque :</label>
            <input type="text" id="Creation-nom" name="nom_casque" required>
        </div>

        <div class="crea-marque">
            <label class="produit-bold">Marque du casque :</label>
            <select name="id_marque" required>
                <?php
                $marques = $creationManager->getMarques();
                foreach ($marques as $marque): ?>
                    <option value="<?= htmlspecialchars((string)($marque['id_marque'] ?? '')) ?>">
                        <?= htmlspecialchars((string)($marque['nom_marque'] ?? '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="crea-prix">
            <label class="produit-bold">Prix du casque :</label>
            <input type="number" min="200" step="0.01" name="prix" required>
        </div>

        <div class="crea-inv">
            <label class="produit-bold">Inventaire du casque :</label>
            <input type="number" min="1" name="stock" required>
        </div>

        <div class="crea-desc">
            <label class="produit-bold">Description du casque :</label>
            <textarea name="description" required></textarea>
        </div>

        <div class="crea-img">
            <label class="produit-bold">Image du casque :</label>
            <select name="image_fichier" required>
                <?php
                $images = $creationManager->getImages();
                foreach ($images as $image): ?>
                    <option value="<?= htmlspecialchars((string)($image['image_fichier'] ?? '')) ?>">
                        <?= htmlspecialchars((string)($image['image_fichier'] ?? '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <section class="crea-section-cree">
            <input type="hidden" name="formCasque" value="TRUE">
            <button type="submit" id="commandePanier">Créer</button>
        </section>
    </form>
</section>

<?php require_once "inc/footer.php"; ?>