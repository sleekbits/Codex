<?php
require_once __DIR__ . '/../includes/auth.php';
require_role(['Admin', 'Manager']);

$id = (int)($_POST['id'] ?? 0);
$fields = ['s_no','pr_receival_date','pr_no','assigned_to_user_id','brief_description','wo_dwo_vo_ref','amount_aed','contract_reference','contractor_id','po_no','po_status_id','po_release_date','remarks','type_id'];
$data = [];
foreach ($fields as $field) {
    $data[$field] = trim((string)($_POST[$field] ?? ''));
}
if ($data['pr_receival_date']==='' || $data['pr_no']==='' || $data['assigned_to_user_id']==='' || $data['brief_description']==='' || $data['contractor_id']==='' || $data['po_status_id']==='' || $data['type_id']==='') {
    $_SESSION['flash_error'] = 'Please fill all required fields.';
    header('Location: form.php' . ($id ? '?id=' . $id : ''));
    exit;
}
if ($id) {
    $sql = "UPDATE tracking_records SET s_no=?, pr_receival_date=?, pr_no=?, assigned_to_user_id=?, brief_description=?, wo_dwo_vo_ref=?, amount_aed=?, contract_reference=?, contractor_id=?, po_no=?, po_status_id=?, po_release_date=?, remarks=?, type_id=?, updated_by=?, updated_at=NOW() WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['s_no'],$data['pr_receival_date'],$data['pr_no'],$data['assigned_to_user_id'],$data['brief_description'],$data['wo_dwo_vo_ref'],$data['amount_aed'] ?: null,$data['contract_reference'],$data['contractor_id'],$data['po_no'],$data['po_status_id'],$data['po_release_date'] ?: null,$data['remarks'],$data['type_id'],user()['id'],$id]);
    log_activity(user()['id'], 'Edit record', 'Updated PR No. ' . $data['pr_no']);
    $_SESSION['flash_success'] = 'Record updated.';
} else {
    $sql = "INSERT INTO tracking_records (s_no,pr_receival_date,pr_no,assigned_to_user_id,brief_description,wo_dwo_vo_ref,amount_aed,contract_reference,contractor_id,po_no,po_status_id,po_release_date,remarks,type_id,created_by,updated_by,created_at,updated_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['s_no'],$data['pr_receival_date'],$data['pr_no'],$data['assigned_to_user_id'],$data['brief_description'],$data['wo_dwo_vo_ref'],$data['amount_aed'] ?: null,$data['contract_reference'],$data['contractor_id'],$data['po_no'],$data['po_status_id'],$data['po_release_date'] ?: null,$data['remarks'],$data['type_id'],user()['id'],user()['id']]);
    log_activity(user()['id'], 'Add record', 'Added PR No. ' . $data['pr_no']);
    $_SESSION['flash_success'] = 'Record added.';
}
header('Location: index.php');
