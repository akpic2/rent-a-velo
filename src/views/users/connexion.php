<h2 class="title">Connexion</h2>

<?php if (isset($erreur)): ?>
    <p class="error"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form class="form" action="?url=connexion-verif" method="post">
    <div class="form__field">
        <label for="login">Email</label>
        <input type="email" name="login" id="login" value="<?= htmlspecialchars($login ?? '') ?>" required>
    </div>

    <div class="form__field">
        <label for="password">Mot de passe</label>
        <input type="password" name="password" id="password" required>
    </div>

    <button class="button" type="submit">Se connecter</button>
</form>

<p>Pas encore de compte ? <a href="?url=inscription">Créer un compte</a></p>
