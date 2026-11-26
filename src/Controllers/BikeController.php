<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\Bike;
use App\Models\BikeRepository;

class BikeController
{
    private const TYPES_PHOTO = [
        'jpg' => IMAGETYPE_JPEG,
        'jpeg' => IMAGETYPE_JPEG,
        'png' => IMAGETYPE_PNG,
        'webp' => IMAGETYPE_WEBP,
    ];

    // Page d'accueil publique : tous les vélos avec leur disponibilité
    public function index(): void
    {
        $repository = new BikeRepository();
        $bikes = $repository->findAll();

        render('bikes/index', 'Nos vélos', ['bikes' => $bikes]);
    }

    public function adminIndex(): void
    {
        exigerEmploye();

        $repository = new BikeRepository();
        $bikes = $repository->findAll();

        render('admin/bikes', 'Gestion des vélos', ['bikes' => $bikes]);
    }

    public function create(): void
    {
        exigerEmploye();

        render('admin/bike-form', 'Enregistrer un vélo', ['bike' => new Bike()]);
    }

    public function store(): void
    {
        exigerEmploye();

        // On n'accepte que l'envoi du formulaire, pas un accès direct par l'URL
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        if (!verifierCsrf()) {
            return;
        }

        $this->enregistrer(new Bike(), false);
    }

    public function edit(int $bikeId): void
    {
        exigerAdmin();

        $repository = new BikeRepository();
        $bike = $repository->findById($bikeId);

        if ($bike === null) {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        render('admin/bike-form', 'Modifier un vélo', ['bike' => $bike]);
    }

    public function update(): void
    {
        exigerAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        if (!verifierCsrf()) {
            return;
        }

        $bikeId = filter_var($_POST['bike_id'] ?? null, FILTER_VALIDATE_INT);
        $repository = new BikeRepository();
        $bike = $repository->findById($bikeId === false ? 0 : $bikeId);

        if ($bike === null) {
            http_response_code(404);
            render('errors/404', 'Page non trouvée');
            return;
        }

        $this->enregistrer($bike, true);
    }

    private function enregistrer(Bike $bike, bool $modification): void
    {
        $title = $modification ? 'Modifier un vélo' : 'Enregistrer un vélo';
        $registrationNumber = trim(is_string($_POST['registration_number'] ?? null) ? $_POST['registration_number'] : '');
        $availability = isset($_POST['availability']) ? 1 : 0;
        $saisie = [
            'registration_number' => $registrationNumber,
            'availability' => $availability,
        ];
        $erreur = null;
        $photo = $bike->getPhoto();
        $fichier = $_FILES['photo'] ?? null;

        if ($registrationNumber === '' || strlen($registrationNumber) > 50) {
            $erreur = 'L\'immatriculation est obligatoire et limitée à 50 octets.';
        } elseif ($fichier !== null && ($fichier['error'] ?? null) !== UPLOAD_ERR_NO_FILE) {
            $extension = is_string($fichier['name'] ?? null) ? strtolower(pathinfo($fichier['name'], PATHINFO_EXTENSION)) : '';
            if (($fichier['error'] ?? null) !== UPLOAD_ERR_OK || !isset(self::TYPES_PHOTO[$extension])) {
                $erreur = 'La photo doit être une image JPG, PNG ou WEBP correctement envoyée.';
            } elseif (!is_string($fichier['tmp_name'] ?? null) || !is_uploaded_file($fichier['tmp_name'])) {
                $erreur = 'Le fichier envoyé n\'est pas une photo valide.';
            } else {
                // Une extension seule ne prouve pas que le fichier est une image.
                $image = @getimagesize($fichier['tmp_name']);
                if ($image === false || $image[2] !== self::TYPES_PHOTO[$extension]) {
                    $erreur = 'Le contenu de la photo ne correspond pas au format JPG, PNG ou WEBP choisi.';
                } else {
                    $nouvellePhoto = bin2hex(random_bytes(16)) . '.' . $extension;
                    if (!move_uploaded_file($fichier['tmp_name'], __DIR__ . '/../../public/img/velos/' . $nouvellePhoto)) {
                        $erreur = 'Impossible d\'enregistrer la photo. Vérifiez les droits du dossier des images.';
                    } else {
                        $photo = $nouvellePhoto;
                    }
                }
            }
        }

        if ($erreur !== null) {
            render('admin/bike-form', $title, compact('bike', 'saisie', 'erreur'));
            return;
        }

        $bike->setRegistrationNumber($registrationNumber);
        $bike->setAvailability($availability);
        $bike->setPhoto($photo);

        $repository = new BikeRepository();
        if ($modification) {
            $repository->update($bike);
        } else {
            $repository->insert($bike);
        }

        $_SESSION['flash'] = $modification
            ? "Le vélo {$registrationNumber} a été modifié."
            : "Le vélo {$registrationNumber} a été enregistré.";
        header('Location: index.php?url=admin-velos');
        exit;
    }

    // Met un vélo en service ou hors service (réparation, vol…)
    public function toggleAvailability(): void
    {
        exigerEmploye();

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
