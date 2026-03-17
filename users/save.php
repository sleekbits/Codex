<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Admin']);
$stmt = $pdo->prepare('INSERT INTO users (full_name,email,username,password,role_id,is_active,created_at,updated_at) VALUES (?,?,?,?,?,?,NOW(),NOW())');
$stmt->execute([
    trim($_POST['full_name']),
    trim($_POST['email']),
    trim($_POST['username']),
    password_hash($_POST['password'], PASSWORD_DEFAULT),
    (int)$_POST['role_id'],
    (int)($_POST['is_active'] ?? 1)
]);
log_activity(user()['id'], 'Add user', 'Added user ' . trim($_POST['username']));
$_SESSION['flash_success'] = 'User added.';
header('Location: index.php');
