<?php require_once "inc/header.php"; ?>
<section class="">
    <div class="">
        <h2>Bienvenue sur la page de la creation d'un compte</h2>

        <?php require_once "inc/header.php"; ?>

<section class="site-main">
    <div class="container form-container">

        <div class="form-wrapper">
            <h2>Création de compte</h2>

            <form action="traitement.php" method="post" class="styled-form">

                <div class="form-row">
                    <label for="nom">Nom :</label>
                    <input id="nom" type="text" name="nom" required>
                </div>

                <div class="form-row">
                    <label for="prenom">Prénom :</label>
                    <input id="prenom" type="text" name="prenom" required>
                </div>

                <div class="form-row">
                    <label for="username">Nom d'utilisateur :</label>
                    <input id="username" type="text" name="username" required>
                </div>

                <div class="form-row">
                    <label for="pays">Pays :</label>
                    <input id="pays" type="text" name="pays">
                </div>

                <div class="form-row">
                    <label for="adresse">Adresse :</label>
                    <input id="adresse" type="text" name="adresse">
                </div>

                <div class="form-row">
                    <label for="argent">Argent dans le compte :</label>
                    <input id="argent" type="number" min="0" name="argent" step="0.01">
                </div>

                <div class="form-row">
                    <label for="ville">Ville :</label>
                    <input id="ville" type="text" name="ville">
                </div>

                <div class="form-row">
                    <label for="telephone">Téléphone :</label>
                    <input id="telephone" type="tel" pattern="[0-9]{3}-[0-9]{3}-[0-9]{4}" name="telephone">
                </div>

                <div class="form-row">
                    <label for="email">Email :</label>
                    <input id="email" type="email" name="email" required>
                </div>

                <div class="form-row">
                    <label for="motdepasse">Mot de passe :</label>
                    <input id="motdepasse" type="password" name="motdepasse" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-submit">Créer le compte</button>
                </div>

            </form>

            <div class="login-box">
                <p>Vous avez déjà un compte ?</p>
                <form action="login.php" method="post" style="display:inline;">
                    <button type="submit" class="btn-submit">Se connecter</button>
                </form>
            </div>
        </div>

    </div>
</section>

<?php require_once "inc/footer.php"; ?>

</section>