<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Admin']);
$roles=['Admin','ERP/IT','Chief Executive Officer (CEO)','Chief Procurement Officer (CPO)','Executive Director Procurement','Director Procurement','Associate Director Procurement','Senior Manager Procurement','Manager Procurement','Assistant Manager Procurement','Senior Buyer','Buyer','Senior Procurement Officer','Procurement Officer'];
foreach($roles as $role){
  $pdo->prepare('INSERT INTO roles(role_name,created_at,updated_at) VALUES(?,NOW(),NOW()) ON DUPLICATE KEY UPDATE role_name=VALUES(role_name), updated_at=NOW()')->execute([$role]);
}
$_SESSION['flash_success']='Required roles seeded.';
header('Location:index.php');
