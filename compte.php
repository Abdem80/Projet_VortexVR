<?php require_once "inc/header.php"; ?>
<?php require_once "classe/clientManager.php"; ?>
<?php require_once "classe/client.php"; ?>
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
<div id="protectedContent" style="display:none;">

<h2>Bienvenue sur la page se modifier les informations du compte</h2>
        
<?php
$courriel= $_SESSION['courriel'];

if (!$courriel) {
   echo "Aucun client connecté.";
    exit;
}

$clientManager = new ClientManager();
     $client = $clientManager->showClientByCourriel($courriel);
?>

<p><strong>Nom :</strong> <?= htmlspecialchars($client['nom']) ?></p>
<form method="post" action="updateClient.php">
    <input type="hidden" name="champ" value="nom">
    <input type="text" name="nouvelle_valeur" placeholder="Nouveau nom">
    <button type="submit">Envoyer</button>
</form><br>

<p><strong>Prenom :</strong> <?= htmlspecialchars($client['prenom']) ?></p>
<form method="post" action="updateClient.php">
    <input type="hidden" name="champ" value="prenom">
    <input type="text" name="nouvelle_valeur" placeholder="Nouveau prenom">
    <button type="submit">Envoyer</button>
</form><br>

<p><strong>Nom d'utilisateur :</strong> <?= htmlspecialchars($client['nom_utilisateur']) ?></p>
<form method="post" action="updateClient.php">
    <input type="hidden" name="champ" value="nom_utilisateur">
    <input type="text" name="nouvelle_valeur" placeholder="Nouveau nom d'utilisateur">
    <button type="submit">Envoyer</button>
</form><br>

<p><strong>Email :</strong> <?= htmlspecialchars($client['courriel']) ?></p>
<form method="post" action="updateClient.php">
    <input type="hidden" name="champ" value="courriel">
    <input type="email" name="nouvelle_valeur" placeholder="Nouvelle email">
    <button type="submit">Envoyer</button>
</form><br>

<p><strong>Pays :</strong> <?= htmlspecialchars($client['pays']) ?></p>
<form method="post" action="updateClient.php">
    <input type="hidden" name="champ" value="pays">
    <input type="text" name="nouvelle_valeur" placeholder="Nouveau pays">
    <button type="submit">Envoyer</button>
</form><br>

<p><strong>Adresse :</strong> <?= htmlspecialchars($client['adresse']) ?></p>
<form method="post" action="updateClient.php">
    <input type="hidden" name="champ" value="adresse">
    <input type="text" name="nouvelle_valeur" placeholder="Nouvelle addresse">
    <button type="submit">Envoyer</button>
</form><br>

<p><strong>Ville :</strong> <?= htmlspecialchars($client['ville']) ?></p>
<form method="post" action="updateClient.php">
    <input type="hidden" name="champ" value="ville">
    <input type="text" name="nouvelle_valeur" placeholder="Nouvelle ville">
    <button type="submit">Envoyer</button>
</form><br>

<p><strong>Téléphone :</strong> <?= htmlspecialchars($client['telephone']) ?></p>
<form method="post" action="updateClient.php">
    <input type="hidden" name="champ" value="telephone">
    <input type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" name="nouvelle_valeur" placeholder="Nouveau telephone">
    <button type="submit">Envoyer</button>
</form><br>

<p><strong>Argent :</strong> <?= htmlspecialchars($client['argent']) ?> $</p>
<form method="post" action="updateClient.php">
    <input type="hidden" name="champ" value="argent">
    <input type="number" min="0" name="nouvelle_valeur" placeholder="Nouveau montant">
    <button type="submit">Envoyer</button>
</form><br>

</div>
</section>
<?php require_once "inc/footer.php"; ?>
