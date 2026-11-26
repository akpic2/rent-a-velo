<h2 class="title">Créer un compte</h2>

<?php if (isset($erreur)): ?>
    <p class="error"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form class="form" action="?url=inscription-store" method="post">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(jetonCsrf()) ?>">

    <div class="form__field">
        <label for="firstname">Prénom</label>
        <input type="text" name="firstname" id="firstname" value="<?= htmlspecialchars($saisie['firstname'] ?? '') ?>" maxlength="255" autocomplete="given-name" required>
    </div>

    <div class="form__field">
        <label for="lastname">Nom</label>
        <input type="text" name="lastname" id="lastname" value="<?= htmlspecialchars($saisie['lastname'] ?? '') ?>" maxlength="255" autocomplete="family-name" required>
    </div>

    <div class="form__field">
        <label for="login">Email</label>
        <input type="email" name="login" id="login" value="<?= htmlspecialchars($saisie['login'] ?? '') ?>" maxlength="100" autocomplete="email" required>
    </div>

    <div class="form__field">
        <label for="password">Mot de passe (8 caractères minimum, 72 octets maximum)</label>
        <input type="password" name="password" id="password" minlength="8" maxlength="72" autocomplete="new-password" required>
    </div>

    <div class="form__field">
        <label for="password_confirmation">Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation" id="password_confirmation" minlength="8" maxlength="72" autocomplete="new-password" required>
    </div>

    <button class="button" type="submit">Créer mon compte</button>
</form>

<p>Déjà un compte ? <a href="?url=connexion">Se connecter</a></p>
