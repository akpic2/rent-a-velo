<?php $modification = $bike->getBikeId() !== null; ?>
<h2 class="title"><?= $modification ? 'Modifier un vélo' : 'Enregistrer un vélo' ?></h2>

<?php if (isset($erreur)): ?>
    <p class="error"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<!-- enctype obligatoire pour envoyer un fichier -->
<form class="form" action="?url=<?= $modification ? 'velo-update' : 'velo-store' ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf" value="<?= htmlspecialchars(jetonCsrf()) ?>">
    <?php if ($modification): ?>
        <input type="hidden" name="bike_id" value="<?= $bike->getBikeId() ?>">
        <img class="table__img" src="<?= htmlspecialchars($bike->getPhotoUrl()) ?>" alt="Photo actuelle du vélo">
    <?php endif; ?>

    <div class="form__field">
        <label for="registration_number">Immatriculation</label>
        <input type="text" name="registration_number" id="registration_number" placeholder="VEL-006" value="<?= htmlspecialchars($saisie['registration_number'] ?? $bike->getRegistrationNumber()) ?>" maxlength="50" required>
    </div>

    <div class="form__field">
        <label for="photo">Photo (JPG, PNG ou WEBP)</label>
        <input type="file" name="photo" id="photo" accept=".jpg,.jpeg,.png,.webp">
        <?php if ($modification): ?>
            <small>Sans nouvelle photo, la photo actuelle sera conservée.</small>
        <?php endif; ?>
    </div>

    <div class="form__check">
        <input type="checkbox" name="availability" id="availability" <?= ($saisie['availability'] ?? $bike->getAvailability()) === 1 ? 'checked' : '' ?>>
        <label for="availability">En service (réservable)</label>
    </div>

    <button class="button" type="submit"><?= $modification ? 'Enregistrer les modifications' : 'Enregistrer' ?></button>
</form>

<a href="?url=admin-velos">← Retour aux vélos</a>
