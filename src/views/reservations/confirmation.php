<h2 class="title">Demande enregistrée</h2>

<div class="ticket">
    <p class="ticket__label">Votre numéro de réservation</p>
    <p class="ticket__num">N° <?= $reservation->getResNum() ?></p>
    <p>Gardez ce numéro : il vous sera demandé pour récupérer le vélo.</p>
</div>

<p>
    Vélo : <?= htmlspecialchars($reservation->getRegistrationNumber()) ?><br>
    Du <?= date('d/m/Y', strtotime($reservation->getStartDate())) ?> au <?= date('d/m/Y', strtotime($reservation->getEndDate())) ?><br>
    Au nom de : <?= htmlspecialchars($reservation->getFirstname() . ' ' . $reservation->getLastname()) ?><br>
    Statut : <span class="badge badge--<?= $reservation->getStatus() ?>"><?= $reservation->getStatusLabel() ?></span>
</p>

<?php if (estConnecte() && $reservation->getUserId() === $_SESSION['user_id']): ?>
    <p><a class="button" href="?url=mes-reservations">Suivre mes réservations</a></p>
<?php endif; ?>

<a href="?url=velos">← Retour aux vélos</a>
