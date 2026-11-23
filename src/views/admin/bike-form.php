<h2 class="title">Enregistrer un vélo</h2>

<?php if (isset($erreur)): ?>
    <p class="error"><?= htmlspecialchars($erreur) ?></p>
<?php endif; ?>

<!-- enctype obligatoire pour envoyer un fichier -->
<form class="form" action="?url=velo-store" method="post" enctype="multipart/form-data">
    <div class="form__field">
        <label for="registration_number">Immatriculation</label>
        <input type="text" name="registration_number" id="registration_number" placeholder="VEL-006" required>
    </div>

    <div class="form__field">
        <label for="photo">Photo (JPG, PNG ou WEBP)</label>
        <input type="file" name="photo" id="photo" accept=".jpg,.jpeg,.png,.webp">
    </div>

    <div class="form__check">
        <input type="checkbox" name="availability" id="availability" checked>
        <label for="availability">En service (réservable)</label>
    </div>

    <button class="button" type="submit">Enregistrer</button>
</form>

<a href="?url=admin-velos">← Retour aux vélos</a>
