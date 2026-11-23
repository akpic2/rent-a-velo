<?php

declare(strict_types=1);

function hydrate(string $classe, array $ligne) : object {
    $objet = new $classe();

    foreach ($ligne as $colonne => $valeur) {
        $setter = 'set' . str_replace('_', '', ucwords($colonne, '_'));

        if (method_exists($objet, $setter)) {
            $objet->$setter($valeur);
        }
    }

    return $objet;
}
