<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_permission('pr_add');
require_once __DIR__ . '/../includes/workflow_engine.php';

$users = $pdo->query("SELECT id, full_name FROM users WHERE is_active=1 ORDER BY full_name")->fetchAll();
$types = $pdo->query("SELECT id, type_name FROM types ORDER BY type_name")->fetchAll();
$departments = $pdo->query("SELECT id, department_name FROM departments WHERE is_active=1 ORDER BY department_name")->fetchAll();
$businessUnits = $pdo->query("SELECT id, business_unit_name FROM business_units WHERE is_active=1 ORDER BY business_unit_name")->fetchAll();
$purchasingGroups = $pdo->query("SELECT id, group_code, group_name FROM purchasing_groups WHERE is_active=1 ORDER BY group_code")->fetchAll();
$currencies = $pdo->query("SELECT id, currency_code FROM currencies WHERE is_active=1 ORDER BY currency_code")->fetchAll();
$statusOptions=['Draft','Submitted','Under Endorsement','Under Approval','Approved','Rejected','Cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $items = json_decode($_POST['items_json'] ?? '[]', true);
    if (!is_array($items)) $items = [];
    $isSubmit = ($_POST['action_mode'] ?? '') === 'submit';
    if ($isSubmit && !has_permission('pr_submit')) { $_SESSION['flash_error'] = 'You are not allowed to submit PR.'; header('Location: index.php'); exit; }
    $status = $isSubmit ? 'Submitted' : 'Draft';
    if (count($items) === 0) {
        $_SESSION['flash_error'] = 'At least one PR item is required.';
        header('Location: index.php'); exit;
    }
    $total = 0;
    foreach ($items as $it) { $total += ((float)$it['quantity'] * (float)$it['estimated_price']); }

    $departmentId = (int)($_POST['department_id'] ?? 0);
    $businessUnitId = (int)($_POST['business_unit_id'] ?? 0);
    $purchasingGroupId = (int)($_POST['purchasing_group_id'] ?? 0);
    $currencyId = (int)($_POST['currency_id'] ?? 0);
    $typeId = (int)($_POST['pr_type_id'] ?? 0);

    $departmentName = '';
    if ($departmentId) { $st=$pdo->prepare('SELECT department_name FROM departments WHERE id=?'); $st->execute([$departmentId]); $departmentName=(string)($st->fetchColumn() ?: ''); }
    $businessUnitName = '';
    if ($businessUnitId) { $st=$pdo->prepare('SELECT business_unit_name FROM business_units WHERE id=?'); $st->execute([$businessUnitId]); $businessUnitName=(string)($st->fetchColumn() ?: ''); }
    $purchasingGroupCode = '';
    if ($purchasingGroupId) { $st=$pdo->prepare('SELECT group_code FROM purchasing_groups WHERE id=?'); $st->execute([$purchasingGroupId]); $purchasingGroupCode=(string)($st->fetchColumn() ?: ''); }
    $currencyCode = '';
    if ($currencyId) { $st=$pdo->prepare('SELECT currency_code FROM currencies WHERE id=?'); $st->execute([$currencyId]); $currencyCode=(string)($st->fetchColumn() ?: 'AED'); }

    $prefix='PR'.date('Ymd');
    $st=$pdo->prepare("SELECT COUNT(*) FROM pr_headers WHERE pr_number LIKE ?");$st->execute([$prefix.'%']);
    $seq=(int)$st->fetchColumn()+1;
    $prNo=$prefix.str_pad((string)$seq,4,'0',STR_PAD_LEFT);

    $stmt=$pdo->prepare('INSERT INTO pr_headers (pr_number,pr_date,pr_type,pr_type_id,requestor_id,department,department_id,business_unit,business_unit_id,company_code,plant_location,purchasing_group,purchasing_group_id,currency,currency_id,required_delivery_date,priority,status,justification,remarks,total_amount,created_by,updated_by,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
    $stmt->execute([$prNo,$_POST['pr_date'],$_POST['pr_type'],$typeId,(int)$_POST['requestor_id'],$departmentName,$departmentId ?: null,$businessUnitName,$businessUnitId ?: null,trim($_POST['company_code']),trim($_POST['plant_location']),$purchasingGroupCode,$purchasingGroupId ?: null,$currencyCode,$currencyId ?: null,$_POST['required_delivery_date'] ?: null,trim($_POST['priority']),$status,trim($_POST['justification']),trim($_POST['remarks']),$total,user()['id'],user()['id']]);
    $headerId=(int)$pdo->lastInsertId();

    $itemNo=10;
    $q=$pdo->prepare('INSERT INTO pr_items (pr_header_id,item_no,material_service_code,short_description,detailed_description,quantity,uom,estimated_price,total_amount,delivery_date,plant_location,cost_center,gl_account,wbs_project_code,account_assignment_category,purchasing_group,suggested_vendor_id,status,remarks,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
    foreach($items as $it){
      $lineTotal=((float)$it['quantity']*(float)$it['estimated_price']);
      $q->execute([$headerId,$itemNo,$it['material_service_code'],$it['short_description'],$it['detailed_description'],(float)$it['quantity'],$it['uom'],(float)$it['estimated_price'],$lineTotal,$it['delivery_date']?:null,$it['plant_location'],$it['cost_center'],$it['gl_account'],$it['wbs_project_code'],$it['account_assignment_category'],$it['purchasing_group'],null,$status,$it['remarks']]);
      $itemNo += 10;
    }
    if ($isSubmit) {
      $wf = workflow_start_transaction('PR', 'PR', $headerId, $prNo, [
        'amount' => $total,
        'type' => $_POST['pr_type'] ?? '',
        'department' => $departmentName,
        'business_unit' => $businessUnitName
      ], (int)user()['id']);
      if ($wf['ok']) {
        $status = $wf['status'];
        $pdo->prepare('UPDATE pr_headers SET status=?, updated_at=NOW() WHERE id=?')->execute([$status, $headerId]);
      } else {
        $pdo->prepare("UPDATE pr_headers SET status='Draft', updated_at=NOW() WHERE id=?")->execute([$headerId]);
        $status = 'Draft';
        $_SESSION['flash_error'] = $wf['message'];
      }
    }
    $_SESSION['flash_success']="PR {$prNo} saved as {$status}.";
    header('Location: index.php'); exit;
}
?>
<div class="d-flex justify-content-between align-items-center mb-2"><h3>Create Purchase Requisition (PR)</h3><span class="text-muted-custom">SAP ME52N-inspired enterprise layout</span></div>
<form method="post" id="prForm">
<div class="card p-3 mb-3">
  <h6>Header Data</h6>
  <div class="row g-2">
    <div class="col-md-2"><label class="form-label">PR Date</label><input type="date" class="form-control" name="pr_date" value="<?= date('Y-m-d') ?>" required></div>
    <div class="col-md-2"><label class="form-label">PR Type</label><select class="form-select" name="pr_type_id" required><?php foreach($types as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['type_name']) ?></option><?php endforeach; ?></select><input type="hidden" name="pr_type" id="pr_type_text"></div>
    <div class="col-md-3"><label class="form-label">Requestor</label><select class="form-select" name="requestor_id" required><?php foreach($users as $u): ?><option value="<?= $u['id'] ?>" <?= $u['id']==user()['id']?'selected':'' ?>><?= e($u['full_name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label">Department</label><select class="form-select" name="department_id"><option value="">Select</option><?php foreach($departments as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['department_name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label">Business Unit</label><select class="form-select" name="business_unit_id"><option value="">Select</option><?php foreach($businessUnits as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['business_unit_name']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label">Company Code</label><input class="form-control" name="company_code"></div>
    <div class="col-md-2"><label class="form-label">Plant/Location</label><input class="form-control" name="plant_location"></div>
    <div class="col-md-2"><label class="form-label">Purchasing Group</label><select class="form-select" name="purchasing_group_id"><option value="">Select</option><?php foreach($purchasingGroups as $g): ?><option value="<?= $g['id'] ?>"><?= e($g['group_code']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label">Currency</label><select class="form-select" name="currency_id"><?php foreach($currencies as $c): ?><option value="<?= $c['id'] ?>" <?= $c['currency_code']=='AED' ? "selected" : "" ?>><?= e($c['currency_code']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-2"><label class="form-label">Required Delivery Date</label><input type="date" class="form-control" name="required_delivery_date"></div>
    <div class="col-md-2"><label class="form-label">Priority</label><select class="form-select" name="priority"><option>Low</option><option selected>Medium</option><option>High</option></select></div>
    <div class="col-md-3"><label class="form-label">Justification</label><input class="form-control" name="justification"></div>
    <div class="col-md-3"><label class="form-label">Remarks</label><input class="form-control" name="remarks"></div>
  </div>
</div>

<div class="card p-3 mb-3">
  <div class="d-flex justify-content-between align-items-center"><h6>Item Overview</h6><div><button type="button" class="btn btn-sm btn-outline-primary" id="addPrItem">Add Item</button></div></div>
  <table class="table table-sm mt-2" id="prItemsTable"><thead><tr><th>Item</th><th>Code</th><th>Short Description</th><th>Qty</th><th>UOM</th><th>Est. Price</th><th>Total</th><th>Delivery</th><th>Actions</th></tr></thead><tbody></tbody></table>
  <div class="text-end"><strong>Header Total: AED <span id="prHeaderTotal">0.00</span></strong></div>
</div>

<div class="card p-3 mb-3">
  <h6>Item Detail (Selected Row / SAP-style detail pane)</h6>
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pr-basic" type="button">Basic Data</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pr-delivery" type="button">Delivery</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pr-account" type="button">Account Assignment</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pr-notes" type="button">Notes</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#pr-approval" type="button">Approval Preview</button></li>
  </ul>
  <div class="tab-content p-2 border border-top-0">
    <div class="tab-pane fade show active" id="pr-basic">Item details are captured in line rows and prepared for future ME52N-style expansion.</div>
    <div class="tab-pane fade" id="pr-delivery">Delivery, source of supply and schedule placeholders ready.</div>
    <div class="tab-pane fade" id="pr-account">Cost center / GL / account assignment data per line supported.</div>
    <div class="tab-pane fade" id="pr-notes">Notes and attachment placeholders ready for extension.</div>
    <div class="tab-pane fade" id="pr-approval">Workflow preview placeholder for DOA/POA routing integration.</div>
  </div>
</div>

<input type="hidden" name="items_json" id="prItemsJson">
<div class="d-flex gap-2 mb-4">
  <button class="btn btn-secondary" name="action_mode" value="draft">Save Draft</button>
  <button class="btn btn-primary" name="action_mode" value="submit">Submit PR</button>
  <button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">Clear Form</button>
</div>
</form>

<script>
(function(){
  const rows=[];
  const tbody=document.querySelector('#prItemsTable tbody');
  const jsonInput=document.getElementById('prItemsJson');
  const totalEl=document.getElementById('prHeaderTotal');
  function newRow(){return {material_service_code:'',short_description:'',detailed_description:'',quantity:1,uom:'EA',estimated_price:0,delivery_date:'',plant_location:'',cost_center:'',gl_account:'',wbs_project_code:'',account_assignment_category:'',purchasing_group:'',remarks:''};}
  function render(){
    tbody.innerHTML='';
    let sum=0;
    rows.forEach((r,i)=>{const t=(Number(r.quantity)||0)*(Number(r.estimated_price)||0);sum+=t;const tr=document.createElement('tr');tr.innerHTML=`<td>${(i+1)*10}</td><td><input class='form-control form-control-sm' value='${r.material_service_code}' data-k='material_service_code' data-i='${i}'></td><td><input class='form-control form-control-sm' value='${r.short_description}' data-k='short_description' data-i='${i}'></td><td><input type='number' step='0.01' class='form-control form-control-sm' value='${r.quantity}' data-k='quantity' data-i='${i}'></td><td><input class='form-control form-control-sm' value='${r.uom}' data-k='uom' data-i='${i}'></td><td><input type='number' step='0.01' class='form-control form-control-sm' value='${r.estimated_price}' data-k='estimated_price' data-i='${i}'></td><td>${t.toFixed(2)}</td><td><input type='date' class='form-control form-control-sm' value='${r.delivery_date}' data-k='delivery_date' data-i='${i}'></td><td><button type='button' class='btn btn-sm btn-outline-danger' data-del='${i}'>Delete</button></td>`;tbody.appendChild(tr);});
    jsonInput.value=JSON.stringify(rows); totalEl.textContent=sum.toFixed(2);
  }
  document.getElementById('addPrItem').addEventListener('click',()=>{rows.push(newRow());render();});
  tbody.addEventListener('input',e=>{const i=e.target.dataset.i,k=e.target.dataset.k;if(i!==undefined&&k){rows[i][k]=e.target.value;render();}});
  tbody.addEventListener('click',e=>{const d=e.target.dataset.del;if(d!==undefined){rows.splice(Number(d),1);render();}});
  document.getElementById('prForm').addEventListener('submit',()=>{jsonInput.value=JSON.stringify(rows);});
  rows.push(newRow()); render();
})();
document.getElementById('prForm').addEventListener('submit',()=>{const typeSel=document.querySelector('[name="pr_type_id"]');const typeTxt=document.getElementById('pr_type_text'); if(typeSel&&typeTxt){typeTxt.value=typeSel.options[typeSel.selectedIndex]?.text||'';}});
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
