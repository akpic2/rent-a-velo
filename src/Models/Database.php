<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

require_once __DIR__ . "/config.php";

class Database {
    private static ?Database $instance = null;
    private PDO $connexion;

    private function __construct()
    {
        $host = DB_HOST;
        $dbname = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;
        $charset = DB_CHARSET;
        $port = DB_PORT;

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset;port=$port";
        $this->connexion = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }

    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnexion(): PDO
    {
        return $this->connexion;
    }
}
