<?php

declare(strict_types=1);

use App\Controllers\BikeController;
use App\Controllers\ReservationController;
use App\Controllers\UserController;
use App\Models\Database;
use App\Models\ReservationRepository;

require_once __DIR__ . '/../vendor/autoload.php';

function verifier(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$cas = [
    'inscription-valide', 'inscription-email-invalide', 'inscription-doublon',
    'inscription-confirmation', 'inscription-mot-de-passe-court', 'inscription-csrf',
    'connexion-usager', 'connexion-employe',
    'usager-tableau-de-bord', 'usager-comptes', 'usager-creation-employe',
    'usager-creation-velo', 'usager-disponibilite', 'usager-validation', 'usager-recherche',
    'employe-modification-interdite', 'velo-formulaire', 'velo-modification',
    'velo-immatriculation-vide', 'velo-immatriculation-longue', 'velo-photo-invalide',
    'velo-csrf', 'velo-introuvable', 'velo-creation-employe', 'reservation-usager',
    'suivi-reservations', 'suivi-vide', 'suivi-sans-connexion',
];

if (!isset($argv[1])) {
    foreach ($cas as $nom) {
        $processus = proc_open([PHP_BINARY, __FILE__, $nom], [
            0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w'],
        ], $pipes);
        verifier(is_resource($processus), 'Impossible de lancer le test.');
        fclose($pipes[0]);
        $sortie = stream_get_contents($pipes[1]);
        $erreurs = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        $code = proc_close($processus);
        verifier($code === 0 && $erreurs === '', "{$nom}: {$sortie}{$erreurs}");
        echo $sortie;
    }
    echo count($cas) . " tests reussis (SQLite en memoire, aucune donnee MAMP modifiee).\n";
    exit;
}

$nom = $argv[1];
verifier(in_array($nom, $cas, true), 'Test inconnu.');

$pdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
$pdo->sqliteCreateFunction('CURDATE', static fn(): string => date('Y-m-d'), 0);
$pdo->exec("CREATE TABLE users (
    user_id INTEGER PRIMARY KEY AUTOINCREMENT, firstname TEXT, lastname TEXT,
    login TEXT UNIQUE COLLATE NOCASE, password TEXT, role TEXT
);
CREATE TABLE bikes (
    bike_id INTEGER PRIMARY KEY AUTOINCREMENT, registration_number TEXT, availability INTEGER, photo TEXT
);
CREATE TABLE reservations (
    res_num INTEGER PRIMARY KEY AUTOINCREMENT, user_id INTEGER, bike_id INTEGER,
    start_date TEXT, end_date TEXT, res_date TEXT DEFAULT CURRENT_TIMESTAMP, status TEXT DEFAULT 'pending'
);
INSERT INTO bikes VALUES (1, 'VEL-001', 1, 'ancienne.png');");
$query = $pdo->prepare('INSERT INTO users VALUES (?, ?, ?, ?, ?, ?)');
foreach (['citizen', 'employee', 'admin'] as $index => $role) {
    $query->execute([$index + 1, 'Test', 'Compte', "{$role}@example.test", password_hash('Password123!', PASSWORD_DEFAULT), $role]);
}

// Injecter une base en memoire sans ouvrir la connexion MySQL de l'application.
$classe = new ReflectionClass(Database::class);
$database = $classe->newInstanceWithoutConstructor();
$classe->getProperty('connexion')->setValue($database, $pdo);
$classe->getProperty('instance')->setValue(null, $database);

session_start();
$_SESSION = [
    'user_id' => 3, 'role' => 'admin', 'firstname' => 'Test', 'lastname' => 'Compte',
    'login' => 'admin@example.test', 'csrf' => 'test-token',
];
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST = ['csrf' => 'test-token', 'bike_id' => '1', 'registration_number' => 'VEL-MODIF'];
$_FILES = [];
$controle = static function (string $html) use ($pdo): void {
    $bike = $pdo->query('SELECT * FROM bikes WHERE bike_id = 1')->fetch();
    verifier($bike['registration_number'] === 'VEL-001' && $bike['photo'] === 'ancienne.png', 'Le velo ne devait pas changer.');
    verifier(str_contains($html, 'class="error"'), 'Une erreur doit etre affichee.');
};
$retour = false;

ob_start();
register_shutdown_function(static function () use (&$controle, &$retour, $nom): void {
    $html = ob_get_clean();
    $fatal = error_get_last();
    if ($fatal !== null && in_array($fatal['type'], [E_ERROR, E_PARSE, E_COMPILE_ERROR, E_USER_ERROR], true)) {
        session_destroy();
        return;
    }
    try {
        $controle($html);
        session_destroy();
        echo "OK {$nom}\n";
    } catch (Throwable $exception) {
        session_destroy();
        fwrite(STDERR, $exception->getMessage() . "\n");
        exit(1);
    }
});

$userController = new UserController();
$bikeController = new BikeController();
$reservationController = new ReservationController();

if (str_starts_with($nom, 'inscription-')) {
    unset($_SESSION['user_id'], $_SESSION['role']);
    $_POST = [
        'csrf' => 'test-token', 'firstname' => ' Lea ', 'lastname' => ' Martin ',
        'login' => 'lea@example.test', 'password' => 'Password123!',
        'password_confirmation' => 'Password123!', 'role' => 'admin',
    ];
    $message = match ($nom) {
        'inscription-email-invalide' => 'adresse email',
        'inscription-doublon' => 'déjà utilisée',
        'inscription-confirmation' => 'ne correspondent pas',
        'inscription-mot-de-passe-court' => 'entre 8 et 72',
        'inscription-csrf' => 'formulaire a expiré',
        default => '',
    };
    match ($nom) {
        'inscription-email-invalide' => $_POST['login'] = 'invalide',
        'inscription-doublon' => $_POST['login'] = 'CITIZEN@example.test',
        'inscription-confirmation' => $_POST['password_confirmation'] = 'autre',
        'inscription-mot-de-passe-court' => $_POST['password'] = 'court',
        'inscription-csrf' => $_POST['csrf'] = 'incorrect',
        default => null,
    };
    $controle = static function (string $html) use ($pdo, $nom, $message, &$retour): void {
        $user = $pdo->query("SELECT * FROM users WHERE login = 'lea@example.test'")->fetch();
        if ($nom === 'inscription-valide') {
            verifier(!$retour && $user !== false, 'Le compte doit etre cree puis redirige.');
            verifier($user['role'] === 'citizen' && $user['firstname'] === 'Lea', 'Role public et saisie normalisee attendus.');
            verifier(password_verify('Password123!', $user['password']), 'Mot de passe hache attendu.');
            verifier(!isset($_SESSION['user_id']), 'La connexion reste explicite apres inscription.');
        } else {
            verifier((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 3, 'Aucun compte ne doit etre cree.');
            verifier(str_contains($html, $message), 'Erreur de validation attendue.');
            verifier(!str_contains($html, 'value="Password123!"'), 'Ne jamais reafficher un mot de passe.');
        }
    };
    $userController->storeRegistration();
} elseif (str_starts_with($nom, 'connexion-')) {
    $_SESSION = [];
    $role = $nom === 'connexion-usager' ? 'citizen' : 'employee';
    $_POST = ['login' => "{$role}@example.test", 'password' => 'Password123!'];
    $controle = static function (string $html) use ($role, &$retour): void {
        verifier(!$retour && $_SESSION['role'] === $role, 'Connexion et redirection attendues.');
        ob_start();
        render('bikes/index', 'Nos velos', ['bikes' => []]);
        $navigation = ob_get_clean();
        verifier(str_contains($navigation, '?url=admin-comptes') === ($role === 'employee'), 'Navigation adaptee au role attendue.');
        verifier(isset($_SESSION['login'], $_SESSION['lastname']), 'Identite necessaire aux reservations attendue.');
    };
    $userController->checkLogin();
} elseif (str_starts_with($nom, 'usager-') || $nom === 'employe-modification-interdite') {
    $_SESSION['role'] = $nom === 'employe-modification-interdite' ? 'employee' : 'citizen';
    $controle = static function (string $html) use ($pdo, &$retour): void {
        verifier(!$retour && $html === '', 'Acces refuse avant toute action ou affichage.');
        verifier(isset($_SESSION['flash']), 'Le refus doit etre explique.');
        verifier((int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn() === 3, 'Aucun compte ajoute.');
        verifier($pdo->query('SELECT registration_number FROM bikes WHERE bike_id = 1')->fetchColumn() === 'VEL-001', 'Velo inchange.');
    };
    match ($nom) {
        'usager-tableau-de-bord' => $reservationController->adminIndex(),
        'usager-comptes' => $userController->index(),
        'usager-creation-employe' => $userController->store(),
        'usager-creation-velo' => $bikeController->store(),
        'usager-disponibilite' => $bikeController->toggleAvailability(),
        'usager-validation' => $reservationController->accept(),
        'usager-recherche' => $reservationController->search('Test'),
        'employe-modification-interdite' => $bikeController->update(),
    };
} elseif ($nom === 'reservation-usager') {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'citizen';
    $_SESSION['login'] = 'citizen@example.test';
    $_POST += [
        'firstname' => ['Autre'], 'lastname' => 'Personne', 'login' => 'admin@example.test',
        'start_date' => date('Y-m-d', strtotime('+2 days')), 'end_date' => date('Y-m-d', strtotime('+3 days')),
    ];
    $controle = static function (string $html) use ($pdo, &$retour): void {
        verifier(!$retour, 'Redirection vers la confirmation attendue.');
        verifier((int) $pdo->query('SELECT user_id FROM reservations')->fetchColumn() === 1, 'La reservation doit utiliser le compte connecte, pas l\'email soumis.');
    };
    $reservationController->store();
} elseif (str_starts_with($nom, 'suivi-')) {
    $_SESSION['user_id'] = 1;
    $_SESSION['role'] = 'citizen';
    if ($nom === 'suivi-sans-connexion') {
        $_SESSION = [];
        $controle = static function (string $html) use (&$retour): void {
            verifier(!$retour && $html === '', 'Le suivi necessite une connexion.');
            verifier(isset($_SESSION['flash']), 'La connexion requise doit etre expliquee.');
        };
        $reservationController->myReservations();
    } elseif ($nom === 'suivi-vide') {
        $controle = static function (string $html): void {
            verifier(str_contains($html, 'pas encore de réservation'), 'Etat vide explicite attendu.');
            verifier(str_contains($html, '?url=velos'), 'Lien pour reserver attendu.');
        };
        $reservationController->myReservations();
    } else {
        $pdo->exec("INSERT INTO reservations (res_num, user_id, bike_id, start_date, end_date, res_date, status) VALUES
            (1001, 1, 1, '2026-12-01', '2026-12-02', '2026-11-20 10:00:00', 'pending'),
            (1002, 1, 1, '2026-12-03', '2026-12-04', '2026-11-21 10:00:00', 'accepted'),
            (1003, 1, 1, '2026-12-05', '2026-12-06', '2026-11-22 10:00:00', 'rejected'),
            (1004, 2, 1, '2026-12-07', '2026-12-08', '2026-11-23 10:00:00', 'pending');");
        $_GET['user_id'] = '2';
        $controle = static function (string $html) use ($reservationController): void {
            verifier(str_contains($html, 'Mes réservations (3)'), 'Uniquement les trois reservations du compte connecte.');
            verifier(!str_contains($html, 'N° 1004'), 'La reservation d\'un autre compte doit rester privee, meme avec un user_id dans l\'URL.');
            verifier(strpos($html, 'N° 1003') < strpos($html, 'N° 1002') && strpos($html, 'N° 1002') < strpos($html, 'N° 1001'), 'Reservations recentes en premier.');
            foreach (['En attente', 'Acceptée', 'Refusée', '01/12/2026', 'VEL-001'] as $texte) {
                verifier(str_contains($html, $texte), 'Statuts et informations de reservation attendus.');
            }
            $repository = new ReservationRepository();
            foreach (['accepted', 'rejected'] as $statut) {
                $repository->updateStatus(1001, $statut);
                ob_start();
                $reservationController->myReservations();
                $suivi = ob_get_clean();
                verifier(!str_contains($suivi, 'badge--pending'), 'Le suivi doit relire la decision en base.');
                verifier(substr_count($suivi, 'badge--' . $statut) === 2, 'Nouvelle decision visible apres actualisation.');
            }
        };
        $reservationController->myReservations();
    }
} else {
    switch ($nom) {
        case 'velo-formulaire':
            $controle = static function (string $html): void {
                verifier(str_contains($html, 'value="VEL-001"') && str_contains($html, 'ancienne.png'), 'Fiche pre-remplie attendue.');
                verifier(str_contains($html, 'velo-update') && str_contains($html, 'name="csrf"'), 'Action et protection du formulaire attendues.');
            };
            $bikeController->edit(1);
            break;
        case 'velo-modification':
        case 'velo-creation-employe':
            $controle = static function (string $html) use ($pdo, $nom, &$retour): void {
                verifier(!$retour, 'Redirection attendue apres enregistrement.');
                $id = $nom === 'velo-modification' ? 1 : 2;
                $bike = $pdo->query("SELECT * FROM bikes WHERE bike_id = {$id}")->fetch();
                verifier($bike['registration_number'] === 'VEL-MODIF' && (int) $bike['availability'] === 0, 'Immatriculation et case decochee enregistrees.');
                verifier($bike['photo'] === ($id === 1 ? 'ancienne.png' : null), 'Photo conservee lors de la modification.');
            };
            if ($nom === 'velo-modification') {
                $bikeController->update();
            } else {
                $_SESSION['role'] = 'employee';
                $bikeController->store();
            }
            break;
        case 'velo-immatriculation-vide':
            $_POST['registration_number'] = ' ';
            $bikeController->update();
            break;
        case 'velo-immatriculation-longue':
            $_POST['registration_number'] = str_repeat('X', 51);
            $bikeController->update();
            break;
        case 'velo-photo-invalide':
            $_FILES['photo'] = ['error' => UPLOAD_ERR_OK, 'name' => 'photo.php', 'tmp_name' => ''];
            $bikeController->update();
            break;
        case 'velo-csrf':
            $_POST['csrf'] = 'incorrect';
            $bikeController->update();
            break;
        case 'velo-introuvable':
            $_POST['bike_id'] = '999';
            $controle = static function (string $html): void {
                verifier(http_response_code() === 404, 'Un velo inconnu doit renvoyer 404.');
            };
            $bikeController->update();
            break;
    }
}
$retour = true;
