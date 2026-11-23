<h2 class="title">Réservations en attente (<?= count($reservations) ?>)</h2>

<div class="form__field">
    <label for="recherche">Rechercher une demande (n°, nom, email, immatriculation)</label>
    <input type="search" id="recherche" placeholder="Ex. : 1001, Durand, VEL-002…">
</div>
<p class="hint">Sans recherche : les demandes en attente, de la plus ancienne à la plus récente. Avec une recherche : toutes les réservations.</p>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th>N°</th>
                <th>Demandée le</th>
                <th>Usager</th>
                <th>Vélo</th>
                <th>Période</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <!-- Le JS remplace le contenu de ce tbody à chaque recherche -->
        <tbody id="reservations-lignes">
            <?php require __DIR__ . '/partials/reservation-lignes.php'; ?>
        </tbody>
    </table>
</div>

<script src="js/recherche.js"></script>
