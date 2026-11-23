<h2 class="title">Comptes utilisateurs (<?= count($users) ?>)</h2>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <?php if (estAdmin()): ?>
                    <th>Action</th>
                <?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user->getFirstname() . ' ' . $user->getLastname()) ?></td>
                    <td><?= htmlspecialchars($user->getLogin()) ?></td>
                    <td><?= $user->getRoleLabel() ?></td>
                    <?php if (estAdmin()): ?>
                        <td>
                            <?php if ($user->getUserId() !== $_SESSION['user_id']): ?>
                                <form action="?url=compte-supprimer" method="post" onsubmit="return confirm('Supprimer ce compte et ses réservations ?');">
                                    <input type="hidden" name="user_id" value="<?= $user->getUserId() ?>">
                                    <button class="button button--small button--danger" type="submit">Supprimer</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<h3>Créer un compte employé</h3>

<?php if (isset($erreur)): ?>
    <p class="error"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form class="form" action="?url=compte-store" method="post">
    <div class="form__field">
        <label for="firstname">Prénom</label>
        <input type="text" name="firstname" id="firstname" value="<?= htmlspecialchars($saisie['firstname'] ?? '') ?>" required>
    </div>

    <div class="form__field">
        <label for="lastname">Nom</label>
        <input type="text" name="lastname" id="lastname" value="<?= htmlspecialchars($saisie['lastname'] ?? '') ?>" required>
    </div>

    <div class="form__field">
        <label for="login">Email (identifiant)</label>
        <input type="email" name="login" id="login" value="<?= htmlspecialchars($saisie['login'] ?? '') ?>" required>
    </div>

    <div class="form__field">
        <label for="password">Mot de passe (8 caractères minimum)</label>
        <input type="password" name="password" id="password" minlength="8" required>
    </div>

    <?php if (estAdmin()): ?>
        <div class="form__field">
            <label for="role">Rôle</label>
            <select name="role" id="role">
                <option value="employee">Employé</option>
                <option value="admin">Administrateur</option>
            </select>
        </div>
    <?php endif; ?>

    <button class="button" type="submit">Créer le compte</button>
</form>
