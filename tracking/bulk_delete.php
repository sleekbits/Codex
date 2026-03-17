<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Admin']);
$ids = $_POST['ids'] ?? [];
if (!$ids || !is_array($ids)) {
    $_SESSION['flash_error'] = 'No records selected.';
    header('Location: index.php'); exit;
}
$ids = array_map('intval', $ids);
$placeholders = implode(',', array_fill(0, count($ids), '?'));
$params = array_merge([user()['id']], $ids);
$sql = "UPDATE tracking_records SET deleted_at=NOW(), updated_by=?, updated_at=NOW() WHERE id IN ($placeholders)";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
log_activity(user()['id'], 'Bulk delete record', 'Soft deleted '.count($ids).' records');
$_SESSION['flash_success'] = count($ids) . ' records deleted.';
header('Location: index.php');
