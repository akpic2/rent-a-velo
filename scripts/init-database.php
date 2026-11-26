<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Ce script doit être lancé en ligne de commande.');
}

require_once __DIR__ . '/../src/Models/config.php';

if (DB_NAME !== 'rent_a_velo') {
    fwrite(STDERR, "Le script fourni initialise uniquement la base rent_a_velo. Vérifiez la configuration.\n");
    exit(1);
}

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';charset=' . DB_CHARSET,
    DB_USER,
    DB_PASS,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$query = $pdo->prepare('SELECT COUNT(*) FROM information_schema.SCHEMATA WHERE SCHEMA_NAME = :name');
$query->execute(['name' => DB_NAME]);
if ((int) $query->fetchColumn() !== 0) {
    fwrite(STDERR, "La base rent_a_velo existe déjà. Initialisation refusée pour préserver ses données.\n");
    exit(1);
}
$query->closeCursor();

$sql = file_get_contents(__DIR__ . '/../sql/rent_a_velo.sql');
if ($sql === false) {
    throw new RuntimeException('Impossible de lire le script SQL.');
}

// Le script de démonstration contient une suppression : ne jamais l'exécuter ici.
$sql = str_replace('DROP DATABASE IF EXISTS rent_a_velo;', '', $sql, $suppressions);
if ($suppressions !== 1 || preg_match('/\bDROP\s+(DATABASE|TABLE)\b/i', $sql) === 1) {
    throw new RuntimeException('Le script SQL ne correspond pas à une initialisation sans suppression.');
}

$query = $pdo->query($sql);
while ($query->nextRowset()) {
}
$query->closeCursor();

foreach (['users', 'bikes', 'reservations'] as $table) {
    $nombre = (int) $pdo->query("SELECT COUNT(*) FROM `rent_a_velo`.`{$table}`")->fetchColumn();
    echo "{$table} : {$nombre} lignes\n";
}
echo "Base rent_a_velo initialisée. Aucune base existante n'a été supprimée.\n";
