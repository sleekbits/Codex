<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Admin', 'Manager']);

if (empty($_FILES['import_file']['tmp_name'])) {
    $_SESSION['flash_error'] = 'No file uploaded'; header('Location: index.php'); exit;
}
$path = $_FILES['import_file']['tmp_name'];
$ext = strtolower(pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION));
$headers = ['S NO.','PR Receival Date','PR No.','Assigned to','Brief Description','WO/DWO/VO Ref.','Amount (AED)','Contract Reference',"Contractor's Name",'PO No.','PO Status','PO Release Date','Remarks','Type'];
$rows = [];
if ($ext === 'csv') {
    $fp = fopen($path, 'r');
    $fileHeaders = fgetcsv($fp);
    if ($fileHeaders !== $headers) { $_SESSION['flash_error'] = 'Header mismatch'; header('Location:index.php'); exit; }
    while (($row = fgetcsv($fp)) !== false) { $rows[] = array_combine($headers, $row); }
    fclose($fp);
} else {
    $_SESSION['flash_error'] = 'Only CSV supported unless PhpSpreadsheet is configured.'; header('Location:index.php'); exit;
}

$imported = $updated = $skipped = 0;
foreach ($rows as $r) {
    $assigned = $pdo->prepare('SELECT id FROM users WHERE full_name=? LIMIT 1'); $assigned->execute([$r['Assigned to']]); $assignedId = $assigned->fetchColumn();
    $cont = $pdo->prepare('SELECT id FROM contractors WHERE contractor_name=? LIMIT 1'); $cont->execute([$r["Contractor's Name"]]); $contractorId = $cont->fetchColumn();
    $status = $pdo->prepare('SELECT id FROM po_statuses WHERE status_name=? LIMIT 1'); $status->execute([$r['PO Status']]); $statusId = $status->fetchColumn();
    $type = $pdo->prepare('SELECT id FROM types WHERE type_name=? LIMIT 1'); $type->execute([$r['Type']]); $typeId = $type->fetchColumn();
    if (!$assignedId || !$contractorId || !$statusId || !$typeId) { $skipped++; continue; }
    $exists = $pdo->prepare('SELECT id FROM tracking_records WHERE pr_no=? LIMIT 1'); $exists->execute([$r['PR No.']]); $existingId = $exists->fetchColumn();
    if ($existingId && !empty($_POST['update_existing'])) {
        $stmt = $pdo->prepare('UPDATE tracking_records SET assigned_to_user_id=?, brief_description=?, amount_aed=?, contractor_id=?, po_status_id=?, type_id=?, updated_by=?, updated_at=NOW() WHERE id=?');
        $stmt->execute([$assignedId,$r['Brief Description'],$r['Amount (AED)'] ?: null,$contractorId,$statusId,$typeId,user()['id'],$existingId]);
        $updated++;
    } elseif ($existingId) {
        $skipped++;
    } else {
        $stmt = $pdo->prepare('INSERT INTO tracking_records (s_no,pr_receival_date,pr_no,assigned_to_user_id,brief_description,wo_dwo_vo_ref,amount_aed,contract_reference,contractor_id,po_no,po_status_id,po_release_date,remarks,type_id,created_by,updated_by,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
        $stmt->execute([$r['S NO.'],$r['PR Receival Date'],$r['PR No.'],$assignedId,$r['Brief Description'],$r['WO/DWO/VO Ref.'],$r['Amount (AED)'] ?: null,$r['Contract Reference'],$contractorId,$r['PO No.'],$statusId,$r['PO Release Date'] ?: null,$r['Remarks'],$typeId,user()['id'],user()['id']]);
        $imported++;
    }
}
$pdo->prepare('INSERT INTO import_logs (user_id,file_name,imported_count,updated_count,skipped_count,created_at) VALUES (?,?,?,?,?,NOW())')->execute([user()['id'], $_FILES['import_file']['name'], $imported, $updated, $skipped]);
log_activity(user()['id'], 'Import file', "Imported file {$_FILES['import_file']['name']}");
$_SESSION['flash_success'] = "Import complete. Imported: $imported, Updated: $updated, Skipped: $skipped";
header('Location: index.php');
