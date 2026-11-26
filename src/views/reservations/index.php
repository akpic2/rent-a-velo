<h2 class="title">Mes réservations (<?= count($reservations) ?>)</h2>

<p>Retrouvez ici l'avancement de vos demandes. Actualisez le suivi pour voir la dernière décision d'un employé.</p>
<a class="button button--ghost" href="?url=mes-reservations">Actualiser le suivi</a>

<?php if (count($reservations) === 0): ?>
    <p>Vous n'avez pas encore de réservation.</p>
<?php else: ?>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Réservation</th>
                    <th>Vélo</th>
                    <th>Demande envoyée le</th>
                    <th>Période</th>
                    <th>Statut</th>
                    <th>Avancement</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $reservation): ?>
                    <tr>
                        <td>N° <?= $reservation->getResNum() ?></td>
                        <td><?= htmlspecialchars($reservation->getRegistrationNumber()) ?></td>
                        <td><?= date('d/m/Y à H:i', strtotime($reservation->getResDate())) ?></td>
                        <td>Du <?= date('d/m/Y', strtotime($reservation->getStartDate())) ?> au <?= date('d/m/Y', strtotime($reservation->getEndDate())) ?></td>
                        <td><span class="badge badge--<?= htmlspecialchars($reservation->getStatus()) ?>"><?= $reservation->getStatusLabel() ?></span></td>
                        <td>
                            <?= match ($reservation->getStatus()) {
                                'accepted' => 'Votre demande a été acceptée. Gardez votre numéro pour récupérer le vélo aux dates réservées.',
                                'rejected' => 'Votre demande a été refusée. Vous pouvez faire une nouvelle demande pour un autre vélo ou une autre période.',
                                default => 'Votre demande a été enregistrée et attend la décision d\'un employé.',
                            } ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<p><a href="?url=velos">Voir les vélos et faire une réservation</a></p>
