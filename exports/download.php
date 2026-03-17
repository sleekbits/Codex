<?php
require_once __DIR__ . '/../includes/auth.php';
$format = $_GET['format'] ?? 'csv';
$sql = "SELECT tr.s_no 'S NO.', tr.pr_receival_date 'PR Receival Date', tr.pr_no 'PR No.', u.full_name 'Assigned to', tr.brief_description 'Brief Description', tr.wo_dwo_vo_ref 'WO/DWO/VO Ref.', tr.amount_aed 'Amount (AED)', tr.contract_reference 'Contract Reference', c.contractor_name \"Contractor's Name\", tr.po_no 'PO No.', ps.status_name 'PO Status', tr.po_release_date 'PO Release Date', tr.remarks 'Remarks', t.type_name 'Type' FROM tracking_records tr
LEFT JOIN users u ON u.id=tr.assigned_to_user_id
LEFT JOIN contractors c ON c.id=tr.contractor_id
LEFT JOIN po_statuses ps ON ps.id=tr.po_status_id
LEFT JOIN types t ON t.id=tr.type_id
WHERE tr.deleted_at IS NULL";
$params=[];
$multiMap=['year'=>'YEAR(tr.pr_receival_date)','month'=>'MONTH(tr.pr_receival_date)','assigned_to'=>'tr.assigned_to_user_id','contractor_id'=>'tr.contractor_id','po_status_id'=>'tr.po_status_id','type_id'=>'tr.type_id'];
foreach($multiMap as $k=>$col){
  $vals=get_multi_filter($k);
  if($vals){$sql.=' AND '.where_in_clause($col,$vals,$params);} 
}
if(!empty($_GET['pr_no'])){$sql.=' AND tr.pr_no LIKE ?';$params[]='%'.$_GET['pr_no'].'%';}
if(!empty($_GET['po_no'])){$sql.=' AND tr.po_no LIKE ?';$params[]='%'.$_GET['po_no'].'%';}
if(!empty($_GET['date_from'])){$sql.=' AND tr.pr_receival_date >= ?';$params[]=$_GET['date_from'];}
if(!empty($_GET['date_to'])){$sql.=' AND tr.pr_receival_date <= ?';$params[]=$_GET['date_to'];}
if(!empty($_GET['status_group'])){
  if($_GET['status_group']==='released') $sql.=" AND ps.status_name='Released'";
  if($_GET['status_group']==='rejected') $sql.=" AND ps.status_name='Rejected'";
  if($_GET['status_group']==='pending') $sql.=" AND ps.status_name LIKE 'Pending%'";
}
$stmt = $pdo->prepare($sql); $stmt->execute($params); $rows = $stmt->fetchAll();
$headers = ['S NO.','PR Receival Date','PR No.','Assigned to','Brief Description','WO/DWO/VO Ref.','Amount (AED)','Contract Reference',"Contractor's Name",'PO No.','PO Status','PO Release Date','Remarks','Type'];
if ($format === 'xlsx' && class_exists('PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
    $spreadsheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray($headers, null, 'A1');
    $sheet->fromArray(array_map(fn($r)=>array_values($r), $rows), null, 'A2');
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="tracking_export.xlsx"');
    (new PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
} else {
    header('Content-Type: text/csv'); header('Content-Disposition: attachment; filename="tracking_export.csv"');
    $out = fopen('php://output', 'w'); fputcsv($out, $headers);
    foreach ($rows as $row) { fputcsv($out, $row); }
    fclose($out);
}
$pdo->prepare('INSERT INTO export_logs (user_id,format,filters,record_count,created_at) VALUES (?,?,?,?,NOW())')->execute([user()['id'],$format,json_encode($_GET),count($rows)]);
log_activity(user()['id'], 'Export file', 'Exported '.count($rows).' records');
exit;
