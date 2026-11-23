<h2 class="title">Nos vélos (<?= count($bikes) ?>)</h2>

<p>Vélos gratuits pour les habitants. Choisissez un vélo, puis vos dates : un employé valide ensuite votre demande.</p>

<?php if (count($bikes) === 0): ?>
    <p>Aucun vélo pour le moment.</p>
<?php else: ?>
    <div class="grid">
        <?php foreach ($bikes as $bike): ?>
            <article class="card">
                <img class="card__img" src="<?= htmlspecialchars($bike->getPhotoUrl()) ?>" alt="Vélo <?= htmlspecialchars($bike->getRegistrationNumber()) ?>">
                <h3 class="card__title"><?= htmlspecialchars($bike->getRegistrationNumber()) ?></h3>

                <?php if ($bike->getAvailability() === 1): ?>
                    <p class="badge badge--ok">Disponible</p>
                    <a class="button" href="?url=reserver&id=<?= $bike->getBikeId() ?>">Réserver</a>
                <?php else: ?>
                    <p class="badge badge--ko">Hors service</p>
                <?php endif; ?>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
