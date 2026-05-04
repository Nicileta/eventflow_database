<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

if ($method === 'GET') {
    $stmt = $db->prepare("SELECT * FROM wiki_articles WHERE is_published = 1 ORDER BY created_at DESC");
    $stmt->execute();
    jsonResponse(['articles' => $stmt->fetchAll()]);
}

if ($method === 'POST') {
    requireAdmin();
    $b = getBody();
    if (empty(trim($b['title'] ?? ''))) jsonResponse(['error' => 'Titlul este obligatoriu.'], 400);
    $stmt = $db->prepare("INSERT INTO wiki_articles (title,content,category,icon,is_published,created_by) VALUES (?,?,?,?,?,?)");
    $stmt->execute([trim($b['title']),trim($b['content']??''),trim($b['category']??''),trim($b['icon']??'📄'),(int)($b['is_published']??1),$_SESSION['user_id']]);
    jsonResponse(['success' => true, 'id' => (int)$db->lastInsertId()], 201);
}

if ($method === 'PUT') {
    requireAdmin();
    $b = getBody(); $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID invalid.'], 400);
    $stmt = $db->prepare("UPDATE wiki_articles SET title=?,content=?,category=?,icon=?,is_published=?,updated_at=NOW() WHERE id=?");
    $stmt->execute([trim($b['title']),trim($b['content']??''),trim($b['category']??''),trim($b['icon']??'📄'),(int)($b['is_published']??1),$id]);
    jsonResponse(['success' => true]);
}

if ($method === 'DELETE') {
    requireAdmin();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID invalid.'], 400);
    $db->prepare("DELETE FROM wiki_articles WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true]);
}

jsonResponse(['error' => 'Metoda nu este permisa.'], 405);
?>