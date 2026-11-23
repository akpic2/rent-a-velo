<?php

declare(strict_types=1);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent a Vélo | <?= htmlspecialchars($title) ?></title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header class="header">
        <h1 class="header__logo"><a href="index.php">Rent a Vélo</a></h1>

        <nav class="nav">
            <?php if (estConnecte()): ?>
                <a class="nav__link" href="?url=admin">Réservations</a>
                <a class="nav__link" href="?url=admin-velos">Vélos</a>
                <a class="nav__link" href="?url=admin-comptes">Comptes</a>
                <a class="nav__link" href="?url=velos">Site public</a>
                <a class="nav__link" href="?url=deconnexion">Déconnexion (<?= htmlspecialchars($_SESSION['firstname']) ?>)</a>
            <?php else: ?>
                <a class="nav__link" href="?url=velos">Nos vélos</a>
                <a class="nav__link" href="?url=connexion">Espace employés</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="main">
        <?php if (!empty($_SESSION['flash'])): ?>
            <p class="flash"><?= htmlspecialchars($_SESSION['flash']) ?></p>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <?= $content ?>
    </main>

    <footer class="footer">
        <p>&copy; <?= date('Y') ?> Rent a Vélo. Service de location gratuit de la commune.</p>
    </footer>
</body>
</html>
