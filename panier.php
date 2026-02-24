<?php require_once "inc/header.php";

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: compte.php");
    exit;
}

$idUtilisateur = $_SESSION['id_utilisateur'];

$panierManager = new PanierManager();

$panier = $panierManager->getPanierActifPourUtilisateur($idUtilisateur);

$produits = [];
$Soustotal = 0;
$idArticlePanier = 0;


if(isset($_POST['supprimerArticle'])){
    $panierManager->supprimerArticleUtilisateur($idUtilisateur, $idArticlePanier = $_POST['id_article_panier']);
    header("Location: panier.php");
    exit(1);
}

if(isset($_POST['plusQte'])){
    $panierManager->addQteCasque($idUtilisateur, $idArticlePanier = $_POST['id_article_panier']);
    header("Location: panier.php");
    exit(1);
}

if(isset($_POST['moinsQte'])){
    $panierManager->rmvQteCasque($idUtilisateur, $idArticlePanier = $_POST['id_article_panier']);
    header("Location: panier.php");
    exit(1);
}

if($panier !== NULL){

    $produits = $panierManager->getArticlesDuPanier( $panier['id_panier']);

    if(!empty($produits)) {

        $Soustotal = $panierManager->calculerTotal($produits);

    }
}



?>
<section class="panier">
    <!-- section qui couvre toute le panier-->
    <!-- Voici ma section pour montrer le total $ ainsi que de mettre le filtre-->
    <section class="section-Total">
        <div class="panier-Total produit-bold">
            <span>Sous Total : <?php if(isset($Soustotal)){echo htmlspecialchars($Soustotal). ' $';}else{echo "0 $";}?> </span>
        </div>
        <!-- <div>
            <select>
                <option>Tout</option>
                <option>Croissant</option>
                <option>Décroissant</option>
                <option>Marque</option>
            </select>
        </div> -->
    </section>

    <!-- Voici ma section pour montrer chaque article dans le panier avec php + aura besoin de java script pour l'incrementation ou decrementation du nombre acheter-->
    <section class="section-produit">

        <?php if(!empty($produits)){foreach($produits as $produit){ ?>

            <div class="produit">
            
                <div class="produit-box">
                    <input type="checkbox" name="articleSelectionner[]" value="<?php echo $produit['id_article_panier'] ?>" class="produit-checkbox" checked>
                </div>
                

                <div class="produit-nom produit-bold">
                    <span><?php echo htmlspecialchars($produit['nom_casque']) ?></span>
                </div>

                <div class="produit-img">
                    <img src="images/<?php echo htmlspecialchars($produit['image_fichier'])?>">    
                </div>
                
                <div class="produit-desc produit-bold">
                    <span><?php echo htmlspecialchars($produit['description']) ?></span>
                </div>
                
                <div class="produit-prix produit-bold">
                    <span>Prix: <?php echo htmlspecialchars($produit['prix_unitaire']) . "$" ?></span>
                </div>
                
                <div class="produit-qte">
                    <form method="POST">
                        <input type="hidden" name="id_article_panier" value="<?php echo $produit['id_article_panier'] ?>">
                        <button type="submit" class="produit-button" name="moinsQte">- 1</button>
                    </form>
                    <div>
                        <span>quantité: <?php echo htmlspecialchars($produit['quantite']) ?></span>
                    </div>
                    <form method="POST">
                        <input type="hidden" name="id_article_panier" value="<?php echo $produit['id_article_panier'] ?>">
                        <button type="submit" class="produit-button" name="plusQte">+ 1</button>
                    </form>
                </div>

                <div class="produit-suppr">
                    <form method="POST">
                        <input type="hidden" name="id_article_panier" value="<?php echo $produit['id_article_panier'] ?>">
                        <button type="submit" name="supprimerArticle" class="supprimerArticle">Supprimer</button>
                    </form>
                </div>

            </div>

        <?php }} ?>
        
    </section>

    <!-- Voici ma section pour un bouton passer votre commande-->
        <?php if(!empty($produits)) { ?>
    <section class="section-commande">
        <form method="POST">
            <button type="button" id="commandePanier">Passer à la commande</button>
        </form>
    </section>
    <?php } ?>
</section>
<?php require_once "inc/footer.php"; ?>
