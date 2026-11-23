<?php

declare(strict_types=1);

/**
 * Charge une vue, puis l'insère dans le layout.
 */
function render(string $vue, string $title, array $data = []): void
{
    extract($data);

    ob_start();
    require __DIR__ . '/../views/' . $vue . '.php';
    $content = ob_get_clean();

    require __DIR__ . '/../views/layout.php';
}

/**
 * Affiche une vue seule, sans le layout.
 * Sert à la recherche AJAX : le JS ne veut que les lignes du tableau, pas la page entière.
 */
function renderPartiel(string $vue, array $data = []): void
{
    extract($data);

    require __DIR__ . '/../views/' . $vue . '.php';
}
