<?php
require_once 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$db     = getDB();

if ($method === 'GET') {
    $where = "1=1"; $params = [];

    if (!empty($_GET['category'])) { $where .= " AND category = ?"; $params[] = $_GET['category']; }
    if (!empty($_GET['status']))   { $where .= " AND status = ?";   $params[] = $_GET['status'];   }
    if (!empty($_GET['priority'])) { $where .= " AND priority = ?"; $params[] = $_GET['priority']; }
    if (!empty($_GET['search'])) {
        $where .= " AND (title LIKE ? OR description LIKE ? OR location LIKE ?)";
        $s = '%' . $_GET['search'] . '%';
        $params[] = $s; $params[] = $s; $params[] = $s;
    }

    $allowed = ['title','category','priority','status','created_at','event_date'];
    $sort    = in_array($_GET['sort'] ?? '', $allowed) ? $_GET['sort'] : 'event_date';
    $dir     = ($_GET['dir'] ?? 'asc') === 'desc' ? 'DESC' : 'ASC';
    $limit   = min((int)($_GET['limit'] ?? 50), 200);
    $off     = max((int)($_GET['offset'] ?? 0), 0);

    $stmt = $db->prepare("SELECT * FROM events WHERE $where ORDER BY $sort $dir LIMIT $limit OFFSET $off");
    $stmt->execute($params);

    $cStmt = $db->prepare("SELECT COUNT(*) FROM events WHERE $where");
    $cStmt->execute($params);

    jsonResponse(['events' => $stmt->fetchAll(), 'total' => (int)$cStmt->fetchColumn()]);
}

if ($method === 'POST') {
    requireAdmin();
    $b = getBody();
    foreach (['title','category','priority','event_date','status'] as $f) {
        if (empty($b[$f])) jsonResponse(['error' => "Campul '$f' este obligatoriu."], 400);
    }
    $stmt = $db->prepare("INSERT INTO events (title,category,priority,event_date,event_time,location,status,description,is_favorite,created_by) VALUES (?,?,?,?,?,?,?,?,?,?)");
    $stmt->execute([trim($b['title']),$b['category'],$b['priority'],$b['event_date'],$b['event_time']??null,trim($b['location']??''),$b['status'],trim($b['description']??''),(int)($b['is_favorite']??0),$_SESSION['user_id']]);
    jsonResponse(['success' => true, 'id' => (int)$db->lastInsertId()], 201);
}

if ($method === 'PUT') {
    requireAdmin();
    $b = getBody(); $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID invalid.'], 400);
    $stmt = $db->prepare("UPDATE events SET title=?,category=?,priority=?,event_date=?,event_time=?,location=?,status=?,description=?,is_favorite=?,updated_at=NOW() WHERE id=?");
    $stmt->execute([trim($b['title']),$b['category'],$b['priority'],$b['event_date'],$b['event_time']??null,trim($b['location']??''),$b['status'],trim($b['description']??''),(int)($b['is_favorite']??0),$id]);
    jsonResponse(['success' => true]);
}

if ($method === 'DELETE') {
    requireAdmin();
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'ID invalid.'], 400);
    $db->prepare("DELETE FROM events WHERE id = ?")->execute([$id]);
    jsonResponse(['success' => true]);
}

jsonResponse(['error' => 'Metoda nu este permisa.'], 405);
?>