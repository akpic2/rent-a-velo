<?php if (count($reservations) === 0): ?>
    <tr>
        <td colspan="7">Aucune réservation.</td>
    </tr>
<?php else: ?>
    <?php foreach ($reservations as $reservation): ?>
        <tr>
            <td><?= $reservation->getResNum() ?></td>
            <td><?= date('d/m/Y H:i', strtotime($reservation->getResDate())) ?></td>
            <td>
                <?= htmlspecialchars($reservation->getFirstname() . ' ' . $reservation->getLastname()) ?><br>
                <small><?= htmlspecialchars($reservation->getLogin()) ?></small>
            </td>
            <td><?= htmlspecialchars($reservation->getRegistrationNumber()) ?></td>
            <td><?= date('d/m/Y', strtotime($reservation->getStartDate())) ?> → <?= date('d/m/Y', strtotime($reservation->getEndDate())) ?></td>
            <td><span class="badge badge--<?= $reservation->getStatus() ?>"><?= $reservation->getStatusLabel() ?></span></td>
            <td class="table__actions">
                <?php if ($reservation->getStatus() === 'pending'): ?>
                    <form action="?url=reservation-valider" method="post">
                        <input type="hidden" name="res_num" value="<?= $reservation->getResNum() ?>">
                        <button class="button button--small" type="submit">Valider</button>
                    </form>
                    <form action="?url=reservation-refuser" method="post">
                        <input type="hidden" name="res_num" value="<?= $reservation->getResNum() ?>">
                        <button class="button button--small button--ghost" type="submit">Refuser</button>
                    </form>
                <?php endif; ?>

                <?php if (estAdmin()): ?>
                    <form action="?url=reservation-supprimer" method="post" onsubmit="return confirm('Supprimer définitivement cette réservation ?');">
                        <input type="hidden" name="res_num" value="<?= $reservation->getResNum() ?>">
                        <button class="button button--small button--danger" type="submit">Supprimer</button>
                    </form>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
<?php endif; ?>
