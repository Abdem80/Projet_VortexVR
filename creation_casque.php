<?php require_once "inc/header.php"; 
include_once "classe/CreationManager.php";

$idUtilisateur = $_SESSION['id_utilisateur'];

$creationManager = new CreationManager();
$fini = false;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_REQUEST['formCasque'])){
    if($_REQUEST['formCasque'] == "TRUE" && !$fini){
        $creationManager->creeCasque(
            $_POST['nom_casque'],
            $_POST['id_marque'],
            $_POST['prix'],
            $_POST['stock'],
            $_POST['description'],
            $_POST['image_fichier']
        );



        header("Location: creation_casque.php?reussi=1");
        exit(1);
    }
   
}?>

<?php if(isset($_REQUEST['reussi'])){
    ?>
    <section class="crea-section-titre">
        <span>Vous avez reussi à crée votre casque ! Merci !</span>
    </section>
    <?php
    exit(1);
} 
    
?>


<section class="crea-whole">
    <div class="crea-section-titre">
        <span>Bienvenue dans la creation de Casque</span>
    </div>
        <form method="POST" class="crea-form">
            <div class="crea-nom">
                <label class="produit-bold">Nom du casque: </label>
                <input type="text" id="Creation-nom" name="nom_casque" required>
            </div>

            <div class="crea-marque">
            <label class="produit-bold">Marque du casque:</label>
            <select name="id_marque">
                <?php 
                $marques = $creationManager->getMarques();
                foreach($marques as $marque){ ?>
                    <option value="<?php echo $marque['id_marque']?>"><?php echo htmlspecialchars($marque['nom_marque']); ?></option>
                <?php } ?>
            </select>
            </div>

            <div class="crea-prix">
                <label class="produit-bold">Prix du casque:</label>
                <input type="number" min="200" name="prix" required>
            </div>

            <div class="crea-inv">
                <label class="produit-bold">Inventaire du casque:</label>
                <input type="number" min="1" name="stock" required>
            </div>

            <div class="crea-desc">
                <label class="produit-bold">Description du casque:</label>
                <textarea name="description" required></textarea>
            </div>

            <div class="crea-img">
                <label class="produit-bold">Image du casque:</label>
                <select name="image_fichier">
                    <?php 
                    $images = $creationManager->getImages();
                    foreach($images as $image){ ?>
                        <option value="<?php echo $image['image_fichier']?>"><?php echo htmlspecialchars($image['image_fichier']); ?></option>
                    <?php } ?>
                </select>
            </div>

            <section class="crea-section-cree">
                <input type="hidden" name="formCasque" value="TRUE">
                <button type="submit" id="commandePanier">Crée</button>
            </section>
        </form>
    </section>
</section>

<?php require_once "inc/footer.php"; ?>
