<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'GET' && $action === 'me') {
    if (!empty($_SESSION['user_id'])) {
        jsonResponse(['logged_in' => true, 'user' => getCurrentUser()]);
    } else {
        jsonResponse(['logged_in' => false]);
    }
}

if ($method === 'POST' && $action === 'login') {
    $body     = getBody();
    $username = trim($body['username'] ?? '');
    $password = $body['password'] ?? '';

    if (!$username || !$password) {
        jsonResponse(['error' => 'Username si parola sunt obligatorii.'], 400);
    }

    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        sleep(1);
        jsonResponse(['error' => 'Username sau parola incorecta.'], 401);
    }

    $_SESSION['user_id']   = $user['id'];
    $_SESSION['username']  = $user['username'];
    $_SESSION['role']      = $user['role'];
    $_SESSION['full_name'] = $user['full_name'];

    $db->prepare("INSERT INTO session_log (user_id, action, ip_address) VALUES (?, 'login', ?)")
       ->execute([$user['id'], $_SERVER['REMOTE_ADDR']]);

    jsonResponse([
        'success' => true,
        'user'    => [
            'id'        => $user['id'],
            'username'  => $user['username'],
            'role'      => $user['role'],
            'full_name' => $user['full_name'],
        ]
    ]);
}

if ($method === 'POST' && $action === 'logout') {
    if (!empty($_SESSION['user_id'])) {
        $db = getDB();
        $db->prepare("INSERT INTO session_log (user_id, action, ip_address) VALUES (?, 'logout', ?)")
           ->execute([$_SESSION['user_id'], $_SERVER['REMOTE_ADDR']]);
    }
    session_destroy();
    jsonResponse(['success' => true]);
}

jsonResponse(['error' => 'Actiune invalida.'], 400);
?>