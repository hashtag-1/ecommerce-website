<?php
// Seed2Greens Database Configuration
// PDO MySQL Connection

function loadEnv($path) {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        $parts = explode('=', $line, 2);
        if (count($parts) === 2) {
            $key = trim($parts[0]);
            $value = trim($parts[1]);
            if (!getenv($key)) {
                putenv("$key=$value");
            }
        }
    }
}

loadEnv(__DIR__ . '/../.env');

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'seed2greens');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_PORT', getenv('DB_PORT') ?: '3306');

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        try {
            $dsn = 'mysql:host=' . DB_HOST .
                   ';port=' . DB_PORT .
                   ';dbname=' . DB_NAME .
                   ';charset=utf8mb4';

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];

            $this->connection = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                $options
            );

        } catch (PDOException $e) {
            error_log('Database Connection Failed');
            die('Database Connection Failed. Please try again later.');
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
}

// FIX: Random logout bug (Vercel's serverless functions are stateless --
// PHP's default file-based sessions don't survive between requests when
// they land on different instances). This switches session storage to
// the shared MySQL database instead, and starts the session right here,
// once, before any page logic runs.
require_once __DIR__ . '/../includes/session-handler.php';

if (session_status() === PHP_SESSION_NONE) {
    register_db_session_handler(Database::getInstance()->getConnection());
    session_start();
}