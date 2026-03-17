<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Admin']);
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('UPDATE tracking_records SET deleted_at = NOW(), updated_by = ?, updated_at = NOW() WHERE id = ?');
$stmt->execute([user()['id'], $id]);
log_activity(user()['id'], 'Delete record', 'Soft deleted record ID ' . $id);
$_SESSION['flash_success'] = 'Record deleted.';
header('Location: index.php');
