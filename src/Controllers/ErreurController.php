<?php

declare(strict_types=1);

namespace App\Controllers;

class ErreurController
{
    public function pageErreur(): void
    {
        http_response_code(404);
        render('errors/404', 'Page non trouvée');
    }
}
