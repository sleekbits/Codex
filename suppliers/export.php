<?php
require_once __DIR__ . '/../includes/auth.php';
$format=$_GET['format']??'csv';
$rows=$pdo->query('SELECT supplier_code,supplier_legal_name,supplier_license_number,supplier_email,supplier_phone,supplier_mobile,supplier_address,supplier_vat_number,status,remarks FROM suppliers ORDER BY id DESC')->fetchAll();
$headers=['supplier_code','supplier_legal_name','supplier_license_number','supplier_email','supplier_phone','supplier_mobile','supplier_address','supplier_vat_number','status','remarks'];
if($format==='xlsx' && class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')){
  $ss=new PhpOffice\PhpSpreadsheet\Spreadsheet();$sh=$ss->getActiveSheet();$sh->fromArray($headers,null,'A1');$sh->fromArray(array_map('array_values',$rows),null,'A2');
  header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');header('Content-Disposition: attachment; filename="suppliers.xlsx"');(new PhpOffice\PhpSpreadsheet\Writer\Xlsx($ss))->save('php://output');
}else{header('Content-Type:text/csv');header('Content-Disposition: attachment; filename="suppliers.csv"');$o=fopen('php://output','w');fputcsv($o,$headers);foreach($rows as $r)fputcsv($o,$r);fclose($o);}exit;
