<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'eventflow');
define('DB_PORT', 3306);

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT
                 . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Conexiune esuata la baza de date.']);
            exit();
        }
    }
    return $pdo;
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit();
}

function getBody() {
    return json_decode(file_get_contents('php://input'), true) ?? [];
}

session_start();

function requireAdmin() {
    if (empty($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        jsonResponse(['error' => 'Acces interzis.'], 401);
    }
}

function requireAuth() {
    if (empty($_SESSION['user_id'])) {
        jsonResponse(['error' => 'Neautentificat.'], 401);
    }
}

function getCurrentUser() {
    return [
        'id'        => $_SESSION['user_id']  ?? null,
        'role'      => $_SESSION['role']      ?? null,
        'full_name' => $_SESSION['full_name'] ?? null,
        'username'  => $_SESSION['username']  ?? null,
    ];
}
?>