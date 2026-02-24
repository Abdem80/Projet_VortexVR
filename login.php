<?php 
include "inc/header.php";
?>

<h1 class="center" style="text-align: center;">Entrez votre utilisateur et mot de passe <br> pour accéder aux fonctionnalités</h1>

<?php if (!empty($_SESSION['login_error'])): ?>
    <p style="color: red; text-align: center;">
        <?= htmlspecialchars($_SESSION['login_error']) ?>
    </p>
    <?php unset($_SESSION['login_error']); ?>
<?php endif; ?>

<div class="login-section">
    <form class="login" action="traitement.php" method="post">
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" name="username" id="username" required>

        <label for="password">Mot de passe :</label>
        <input type="password" name="pass" id="pass" required>

        <input type="hidden" name="action" value="login">

        <button type="submit" class="rectangle-button">Se connecter</button>
    </form>
</div>

<?php
include "inc/footer.php";
?>