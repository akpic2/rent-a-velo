<h2 class="title">Réserver le vélo <?= htmlspecialchars($bike->getRegistrationNumber()) ?></h2>

<div class="split">
    <img class="split__img" src="<?= htmlspecialchars($bike->getPhotoUrl()) ?>" alt="Vélo <?= htmlspecialchars($bike->getRegistrationNumber()) ?>">

    <div>
        <h3>Déjà réservé</h3>
        <?php if (count($periodesPrises) === 0): ?>
            <p>Ce vélo est libre à toutes les dates.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($periodesPrises as $periode): ?>
                    <li>Du <?= date('d/m/Y', strtotime($periode->getStartDate())) ?> au <?= date('d/m/Y', strtotime($periode->getEndDate())) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php if (isset($erreur)): ?>
    <p class="error"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<form class="form" action="?url=reservation-store" method="post">
    <input type="hidden" name="bike_id" value="<?= $bike->getBikeId() ?>">

    <div class="form__field">
        <label for="firstname">Prénom</label>
        <input type="text" name="firstname" id="firstname" value="<?= htmlspecialchars($saisie['firstname'] ?? '') ?>" required>
    </div>

    <div class="form__field">
        <label for="lastname">Nom</label>
        <input type="text" name="lastname" id="lastname" value="<?= htmlspecialchars($saisie['lastname'] ?? '') ?>" required>
    </div>

    <div class="form__field">
        <label for="login">Email</label>
        <input type="email" name="login" id="login" value="<?= htmlspecialchars($saisie['login'] ?? '') ?>" required>
    </div>

    <div class="form__field">
        <label for="start_date">Du</label>
        <input type="date" name="start_date" id="start_date" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($saisie['start_date'] ?? '') ?>" required>
    </div>

    <div class="form__field">
        <label for="end_date">Au (inclus)</label>
        <input type="date" name="end_date" id="end_date" min="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($saisie['end_date'] ?? '') ?>" required>
    </div>

    <button class="button" type="submit">Envoyer ma demande</button>
</form>

<a href="?url=velos">← Retour aux vélos</a>
