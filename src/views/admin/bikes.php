<h2 class="title">Gestion des vélos (<?= count($bikes) ?>)</h2>

<a class="button" href="?url=velo-create">+ Enregistrer un vélo</a>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>Photo</th>
                <th>Immatriculation</th>
                <th>État</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($bikes as $bike): ?>
                <tr>
                    <td><img class="table__img" src="<?= htmlspecialchars($bike->getPhotoUrl()) ?>" alt="Vélo <?= htmlspecialchars($bike->getRegistrationNumber()) ?>"></td>
                    <td><?= htmlspecialchars($bike->getRegistrationNumber()) ?></td>
                    <td>
                        <?php if ($bike->getAvailability() === 1): ?>
                            <span class="badge badge--ok">En service</span>
                        <?php else: ?>
                            <span class="badge badge--ko">Hors service</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if (estAdmin()): ?>
                            <a class="button button--small" href="?url=velo-edit&amp;id=<?= $bike->getBikeId() ?>">Modifier</a>
                        <?php endif; ?>
                        <form action="?url=velo-dispo" method="post">
                            <input type="hidden" name="bike_id" value="<?= $bike->getBikeId() ?>">
                            <button class="button button--small button--ghost" type="submit">
                                <?= $bike->getAvailability() === 1 ? 'Mettre hors service' : 'Remettre en service' ?>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
