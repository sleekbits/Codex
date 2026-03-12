<?php
require_once __DIR__ . '/../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $_SESSION['flash_success'] = 'Please contact admin to reset your password.';
    header('Location: login.php');
    exit;
}
?><!doctype html><html><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><div class="container py-5"><div class="card col-md-4 mx-auto"><div class="card-body">
<h5>Forgot Password</h5><p>Enter your username to request reset.</p>
<form method="post"><input class="form-control mb-2" name="username" required><button class="btn btn-primary">Submit</button></form>
<a href="login.php" class="d-block mt-2">Back to login</a></div></div></div></body></html>
