<?php
/**
 * creation_casque.php
 *
 * Permet à un utilisateur connecté d'ajouter un nouveau casque VR au catalogue.
 *
 * Flux :
 *   1. Vérifie que l'utilisateur est connecté (id_utilisateur en session).
 *      Si non connecté : message d'erreur + exit.
 *   2. Si une requête POST valide est reçue (formCasque = TRUE) :
 *      - Appelle CreationManager::creeCasque() pour insérer en BDD.
 *      - Redirige vers creation_casque.php?reussi=1 (patron PRG :
 *        Post/Redirect/Get pour éviter la re-soumission du formulaire).
 *   3. Si ?reussi=1 est présent en GET : affiche le message de succès puis exit.
 *   4. Sinon : affiche le formulaire de création.
 *
 * Champs du formulaire :
 *   - nom_casque    : nom commercial du casque
 *   - id_marque     : clé étrangère vers la table des marques
 *   - prix          : prix en dollars (min 200$)
 *   - stock         : quantité en inventaire (min 1)
 *   - description   : texte descriptif du casque
 *   - image_fichier : nom du fichier image (sélectionné dans une liste)
 *
 * @project VortexVR – Boutique de casques VR
 */

declare(strict_types=1);

// Chargement de l'en-tête HTML commun (session, autoloader, CSS, nav).
require_once "inc/header.php";
require_once "classe/CreationManager.php";

// --- Vérification de l'authentification ---
// id_utilisateur doit être présent en session pour autoriser la création.
$idUtilisateur = $_SESSION['id_utilisateur'] ?? null;
if (!$idUtilisateur) {
    echo "<p class='center message-error'>Vous devez être connecté pour créer un casque.</p>";
    require_once "inc/footer.php";
    exit; // Arrêt immédiat : inutile d'afficher le formulaire.
}

// Gestionnaire responsable des insertions dans le catalogue.
$creationManager = new CreationManager();

// --- Traitement du formulaire (patron PRG) ---
// On vérifie que la méthode est POST et que le jeton formCasque est correct
// pour éviter les soumissions accidentelles ou les requêtes malveillantes.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['formCasque']) && $_POST['formCasque'] === "TRUE") {

    // Insertion du nouveau casque en base de données.
    // Les valeurs brutes de $_POST sont passées directement à creeCasque()
    // qui doit utiliser des requêtes préparées pour se protéger des injections SQL.
    $creationManager->creeCasque(
        $_POST['nom_casque']    ?? '',
        $_POST['id_marque']     ?? '',
        $_POST['prix']          ?? '',
        $_POST['stock']         ?? '',
        $_POST['description']   ?? '',
        $_POST['image_fichier'] ?? ''
    );

    // Redirection Post/Redirect/Get : évite la re-soumission si l'utilisateur
    // recharge la page après la création. exit() est obligatoire après header().
    header("Location: creation_casque.php?reussi=1");
    exit;
}
?>

<?php
// Affichage du message de succès après redirection (GET ?reussi=1).
if (isset($_GET['reussi'])): ?>
    <section class="crea-section-titre">
        <span>Vous avez réussi à créer votre casque ! Merci !</span>
    </section>
    <?php require_once "inc/footer.php"; ?>
    <?php exit; /* Arrêt pour ne pas afficher le formulaire vide en dessous. */ ?>
<?php endif; ?>

<!-- =====================================================
     FORMULAIRE DE CRÉATION DE CASQUE
     ===================================================== -->
<section class="crea-whole">
    <div class="crea-section-titre">
        <span>Bienvenue dans la création de casque</span>
    </div>

    <!-- Formulaire POST vers lui-même (action absente = page courante) -->
    <form method="POST" class="crea-form">

        <!-- Nom du casque -->
        <div class="crea-nom">
            <label class="produit-bold">Nom du casque :</label>
            <input type="text" id="Creation-nom" name="nom_casque" required>
        </div>

        <!-- Sélection de la marque : les options sont chargées dynamiquement depuis la BDD -->
        <div class="crea-marque">
            <label class="produit-bold">Marque du casque :</label>
            <select name="id_marque" required>
                <?php
                // getMarques() retourne un tableau associatif [id_marque, nom_marque].
                $marques = $creationManager->getMarques();
                foreach ($marques as $marque): ?>
                    <option value="<?= htmlspecialchars((string)($marque['id_marque'] ?? '')) ?>">
                        <?= htmlspecialchars((string)($marque['nom_marque'] ?? '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Prix (minimum 200$ selon la règle métier, pas de 0.01$) -->
        <div class="crea-prix">
            <label class="produit-bold">Prix du casque :</label>
            <input type="number" min="200" step="0.01" name="prix" required>
        </div>

        <!-- Inventaire (stock, minimum 1 unité) -->
        <div class="crea-inv">
            <label class="produit-bold">Inventaire du casque :</label>
            <input type="number" min="1" name="stock" required>
        </div>

        <!-- Description textuelle du casque -->
        <div class="crea-desc">
            <label class="produit-bold">Description du casque :</label>
            <textarea name="description" required></textarea>
        </div>

        <!-- Sélection du fichier image : liste des images disponibles dans le dossier images/ -->
        <div class="crea-img">
            <label class="produit-bold">Image du casque :</label>
            <select name="image_fichier" required>
                <?php
                // getImages() retourne un tableau des noms de fichiers disponibles.
                $images = $creationManager->getImages();
                foreach ($images as $image): ?>
                    <option value="<?= htmlspecialchars((string)($image['image_fichier'] ?? '')) ?>">
                        <?= htmlspecialchars((string)($image['image_fichier'] ?? '')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Bouton de soumission + jeton de contrôle -->
        <section class="crea-section-cree">
            <!-- formCasque = TRUE sert de jeton pour distinguer une vraie soumission
                 d'un accès direct à la page par GET. -->
            <input type="hidden" name="formCasque" value="TRUE">
            <button type="submit" id="commandePanier">Créer</button>
        </section>
    </form>
</section>

<?php
// Fermeture du <main>, affichage du pied de page HTML.
require_once "inc/footer.php";
?>