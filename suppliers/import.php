<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_role(['Admin']);
if($_SERVER['REQUEST_METHOD']==='POST' && !empty($_FILES['import_file']['tmp_name'])){
  $fp=fopen($_FILES['import_file']['tmp_name'],'r');
  $headers=fgetcsv($fp);
  $expected=['supplier_code','supplier_legal_name','supplier_license_number','supplier_email','supplier_phone','supplier_mobile','supplier_address','supplier_vat_number','status','remarks'];
  if($headers!==$expected){$_SESSION['flash_error']='Header mismatch.'; header('Location: import.php'); exit;}
  $count=0;$skip=0;
  while(($row=fgetcsv($fp))!==false){$d=array_combine($expected,$row); if(!$d['supplier_code']||!$d['supplier_legal_name']){$skip++; continue;}
    $exists=$pdo->prepare('SELECT id FROM suppliers WHERE supplier_code=? LIMIT 1');$exists->execute([$d['supplier_code']]);$id=$exists->fetchColumn();
    if($id){$pdo->prepare('UPDATE suppliers SET supplier_legal_name=?,supplier_license_number=?,supplier_email=?,supplier_phone=?,supplier_mobile=?,supplier_address=?,supplier_vat_number=?,status=?,remarks=?,updated_by=?,updated_at=NOW() WHERE id=?')->execute([$d['supplier_legal_name'],$d['supplier_license_number'],$d['supplier_email'],$d['supplier_phone'],$d['supplier_mobile'],$d['supplier_address'],$d['supplier_vat_number'],(int)$d['status'],$d['remarks'],user()['id'],$id]);}
    else {$pdo->prepare('INSERT INTO suppliers(supplier_code,supplier_legal_name,supplier_license_number,supplier_email,supplier_phone,supplier_mobile,supplier_address,supplier_vat_number,status,remarks,created_by,updated_by,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?,?, ?,NOW(),NOW())')->execute([$d['supplier_code'],$d['supplier_legal_name'],$d['supplier_license_number'],$d['supplier_email'],$d['supplier_phone'],$d['supplier_mobile'],$d['supplier_address'],$d['supplier_vat_number'],(int)$d['status'],$d['remarks'],user()['id'],user()['id']]);}
    $count++;
  }
  fclose($fp);
  $_SESSION['flash_success']="Supplier import complete. Processed: $count, Skipped: $skip"; header('Location:index.php'); exit;
}
?>
<h3>Import Suppliers</h3><div class="card p-3"><p class="text-muted-custom">CSV headers: supplier_code,supplier_legal_name,supplier_license_number,supplier_email,supplier_phone,supplier_mobile,supplier_address,supplier_vat_number,status,remarks</p><form method="post" enctype="multipart/form-data"><input type="file" class="form-control mb-2" name="import_file" required><button class="btn btn-primary">Import CSV</button></form><a class='btn btn-link mt-2 ps-0' href='/Codex/database/sample_supplier_import_template.csv'>Download supplier import template</a></div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
