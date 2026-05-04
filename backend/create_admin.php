<?php
require_once 'config.php';


$username  = 'adminhost';      
$password  = 'Smarald6767';  
      


if (strlen(trim($username)) < 3)
    jsonResponse(['error' => 'Username prea scurt. Minim 3 caractere.'], 400);

if (strlen($password) < 8)
    jsonResponse(['error' => 'Parola prea scurta. Minim 8 caractere.'], 400);

if ($username === 'nicoleta' || $password === 'parola123')
    jsonResponse(['error' => 'Completeaza cu datele tale reale!'], 400);

$db = getDB();

$check = $db->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
$check->execute();
if ((int)$check->fetchColumn() > 0)
    jsonResponse(['error' => 'Admin exista deja! Sterge acest fisier imediat.'], 409);

$checkUser = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
$checkUser->execute([trim($username)]);
if ((int)$checkUser->fetchColumn() > 0)
    jsonResponse(['error' => 'Username-ul este deja folosit.'], 409);

$hash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
$stmt = $db->prepare("INSERT INTO users (username, password_hash, role, full_name) VALUES (?, ?, 'admin', ?)");
$stmt->execute([trim($username), $hash, trim($full_name)]);

jsonResponse([
    'success'   => true,
    'message'   => 'Admin creat cu succes! STERGE ACEST FISIER ACUM!',
    'username'  => trim($username),
    'full_name' => trim($full_name),
]);
?>