<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Bike;
use App\Models\BikeRepository;

class BikeController
{
    private const EXTENSIONS_AUTORISEES = ['jpg', 'jpeg', 'png', 'webp'];

    // Page d'accueil publique : tous les vélos avec leur disponibilité
    public function index(): void
    {
        $repository = new BikeRepository();
        $bikes = $repository->findAll();

        render('bikes/index', 'Nos vélos', ['bikes' => $bikes]);
    }

    public function adminIndex(): void
    {
        exigerConnexion();

        $repository = new BikeRepository();
        $bikes = $repository->findAll();

        render('admin/bikes', 'Gestion des vélos', ['bikes' => $bikes]);
    }

    public function create(): void
    {
        exigerConnexion();

        render('admin/bike-form', 'Enregistrer un vélo');
    }

    public function store(): void
    {
        exigerConnexion();

        // On n'accepte que l'envoi du formulaire, pas un accès direct par l'URL
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        $registrationNumber = trim($_POST['registration_number'] ?? '');
        // Une case non cochée n'est pas envoyée du tout : d'où le isset
        $availability = isset($_POST['availability']) ? 1 : 0;

        if ($registrationNumber === '') {
            render('admin/bike-form', 'Enregistrer un vélo', [
                'erreur' => 'L\'immatriculation est obligatoire.',
            ]);
            return;
        }

        // La photo est facultative : UPLOAD_ERR_NO_FILE veut dire « aucun fichier choisi »
        $photo = null;
        $fichier = $_FILES['photo'] ?? null;

        if ($fichier !== null && $fichier['error'] !== UPLOAD_ERR_NO_FILE) {
            $extension = strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION));

            if ($fichier['error'] !== UPLOAD_ERR_OK || !in_array($extension, self::EXTENSIONS_AUTORISEES, true)) {
                render('admin/bike-form', 'Enregistrer un vélo', [
                    'erreur' => 'La photo doit être une image JPG, PNG ou WEBP.',
                ]);
                return;
            }

            // Nom unique : deux photos « velo.jpg » ne s'écrasent pas
            $photo = uniqid('velo-') . '.' . $extension;
            move_uploaded_file($fichier['tmp_name'], __DIR__ . '/../../public/img/velos/' . $photo);
        }

        $bike = new Bike();
        $bike->setRegistrationNumber($registrationNumber);
        $bike->setAvailability($availability);
        $bike->setPhoto($photo);

        $repository = new BikeRepository();
        $repository->insert($bike);

        $_SESSION['flash'] = "Le vélo {$registrationNumber} a été enregistré.";
        header('Location: index.php?url=admin-velos');
        exit;
    }

    // Met un vélo en service ou hors service (réparation, vol…)
    public function toggleAvailability(): void
    {
        exigerConnexion();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        $repository = new BikeRepository();
        $bike = $repository->findById((int) ($_POST['bike_id'] ?? 0));

        if ($bike === null) {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        $nouvelleDispo = $bike->getAvailability() === 1 ? 0 : 1;
        $repository->updateAvailability($bike->getBikeId(), $nouvelleDispo);

        $_SESSION['flash'] = "Disponibilité du vélo {$bike->getRegistrationNumber()} modifiée.";
        header('Location: index.php?url=admin-velos');
        exit;
    }
}
