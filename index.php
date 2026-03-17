<?php
require __DIR__ . '/app/includes/bootstrap.php';
redirect(is_logged_in() ? 'index.php' : 'login.php');
