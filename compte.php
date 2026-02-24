<?php
declare(strict_types=1);

require_once "inc/header.php";
require_once "classe/clientManager.php";
require_once "classe/client.php";


$courriel = $_SESSION['courriel'] ?? '';
if ($courriel === '') {
    echo "<p class='center message-error'>Aucun client connecté.</p>";
    require_once "inc/footer.php";
    exit;
}

$clientManager = new ClientManager();
$client = $clientManager->showClientByCourriel($courriel);


function h($v): string {
    return htmlspecialchars((string)($v ?? ''));
}
?>

<section class="form-container">
    
    <form onsubmit="return checkform(this);" class="formmargin">
        <div class="capbox">
            <div id="CaptchaDiv"></div>

            <div class="capbox-inner">
                Type the number:<br>
                <input type="hidden" id="txtCaptcha">
                <input type="text" name="CaptchaInput" id="CaptchaInput" size="15">
            </div>
        </div>

        <br>
        <input type="submit" value="Valider le Captcha" class="subbutx3">
    </form>

    <hr>

    
    <div id="protectedContent" class="is-hidden">
        <h2 class="center">Modifier les informations du compte</h2>

        <p><strong>Nom :</strong> <?= h($client['nom'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="nom">
                <input type="text" name="nouvelle_valeur" placeholder="Nouveau nom">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <p><strong>Prénom :</strong> <?= h($client['prenom'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="prenom">
                <input type="text" name="nouvelle_valeur" placeholder="Nouveau prénom">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <p><strong>Nom d'utilisateur :</strong> <?= h($client['nom_utilisateur'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="nom_utilisateur">
                <input type="text" name="nouvelle_valeur" placeholder="Nouveau nom d'utilisateur">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <p><strong>Email :</strong> <?= h($client['courriel'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="courriel">
                <input type="email" name="nouvelle_valeur" placeholder="Nouvel email">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <p><strong>Pays :</strong> <?= h($client['pays'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="pays">
                <input type="text" name="nouvelle_valeur" placeholder="Nouveau pays">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <p><strong>Adresse :</strong> <?= h($client['adresse'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="adresse">
                <input type="text" name="nouvelle_valeur" placeholder="Nouvelle adresse">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <p><strong>Ville :</strong> <?= h($client['ville'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="ville">
                <input type="text" name="nouvelle_valeur" placeholder="Nouvelle ville">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <p><strong>Téléphone :</strong> <?= h($client['telephone'] ?? '') ?></p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="telephone">
                <input type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" name="nouvelle_valeur" placeholder="000-000-0000">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>

        <p><strong>Argent :</strong> <?= h($client['argent'] ?? '') ?> $</p>
        <form method="post" action="updateClient.php" class="styled-form">
            <div class="form-row">
                <input type="hidden" name="champ" value="argent">
                <input type="number" min="0" step="0.01" name="nouvelle_valeur" placeholder="Nouveau montant">
                <button type="submit" class="btn-submit">Envoyer</button>
            </div>
        </form>
    </div>
</section>

<?php require_once "inc/footer.php"; ?>