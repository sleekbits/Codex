<?php require_once __DIR__ . '/../includes/auth.php'; ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e(app_setting('app_name', $env['app_name'])) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="/Codex/assets/css/app.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container-fluid">
        <a class="navbar-brand" href="/Codex/dashboard/index.php"><?= e(app_setting('app_name', $env['app_name'])) ?></a>
        <div class="ms-auto text-white">Logged in as <?= e(user()['full_name']) ?> (<?= e(user()['role_name']) ?>)</div>
    </div>
</nav>
<div class="container-fluid">
    <div class="row">
