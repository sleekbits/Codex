<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_permission('po_add');
require_once __DIR__ . '/../includes/workflow_engine.php';

$types = $pdo->query("SELECT id, type_name FROM types ORDER BY type_name")->fetchAll();
$vendors = $pdo->query("SELECT id, supplier_code, supplier_legal_name FROM suppliers WHERE status=1 ORDER BY supplier_legal_name")->fetchAll();
$departments = $pdo->query("SELECT id, department_name FROM departments WHERE is_active=1 ORDER BY department_name")->fetchAll();
$businessUnits = $pdo->query("SELECT id, business_unit_name FROM business_units WHERE is_active=1 ORDER BY business_unit_name")->fetchAll();
$purchasingGroups = $pdo->query("SELECT id, group_code FROM purchasing_groups WHERE is_active=1 ORDER BY group_code")->fetchAll();
$currencies = $pdo->query("SELECT id, currency_code FROM currencies WHERE is_active=1 ORDER BY currency_code")->fetchAll();
$prRefs = $pdo->query("SELECT id, pr_number FROM pr_headers ORDER BY id DESC LIMIT 200")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $items = json_decode($_POST['items_json'] ?? '[]', true); if(!is_array($items)) $items=[];
    if (!$items) { $_SESSION['flash_error']='At least one PO item is required.'; header('Location:index.php'); exit; }
    if (empty($_POST['vendor_id'])) { $_SESSION['flash_error']='Supplier is required.'; header('Location:index.php'); exit; }
    $isSubmit = ($_POST['action_mode'] ?? '') === 'submit';
    if ($isSubmit && !has_permission('po_submit')) { $_SESSION['flash_error']='You are not allowed to submit PO.'; header('Location:index.php'); exit; }
    $status = $isSubmit ? 'Submitted' : 'Draft';
    $total=0; foreach($items as $it){$total+=((float)$it['quantity']*(float)$it['unit_price']);}

    $departmentId = (int)($_POST['department_id'] ?? 0);
    $businessUnitId = (int)($_POST['business_unit_id'] ?? 0);
    $purchasingGroupId = (int)($_POST['purchasing_group_id'] ?? 0);
    $currencyId = (int)($_POST['currency_id'] ?? 0);
    $typeId = (int)($_POST['po_type_id'] ?? 0);
    $sourcePrId = (int)($_POST['source_pr_header_id'] ?? 0);

    $departmentName = '';
    if ($departmentId) { $st=$pdo->prepare('SELECT department_name FROM departments WHERE id=?'); $st->execute([$departmentId]); $departmentName=(string)($st->fetchColumn() ?: ''); }
    $businessUnitName = '';
    if ($businessUnitId) { $st=$pdo->prepare('SELECT business_unit_name FROM business_units WHERE id=?'); $st->execute([$businessUnitId]); $businessUnitName=(string)($st->fetchColumn() ?: ''); }
    $purchasingGroupCode = '';
    if ($purchasingGroupId) { $st=$pdo->prepare('SELECT group_code FROM purchasing_groups WHERE id=?'); $st->execute([$purchasingGroupId]); $purchasingGroupCode=(string)($st->fetchColumn() ?: ''); }
    $currencyCode = '';
    if ($currencyId) { $st=$pdo->prepare('SELECT currency_code FROM currencies WHERE id=?'); $st->execute([$currencyId]); $currencyCode=(string)($st->fetchColumn() ?: 'AED'); }

    $prefix='PO'.date('Ymd');
    $st=$pdo->prepare("SELECT COUNT(*) FROM po_headers WHERE po_number LIKE ?");$st->execute([$prefix.'%']);
    $poNo=$prefix.str_pad((string)((int)$st->fetchColumn()+1),4,'0',STR_PAD_LEFT);

    $h=$pdo->prepare('INSERT INTO po_headers (po_number,po_date,po_type,po_type_id,vendor_id,source_pr_header_id,department,department_id,business_unit,business_unit_id,company_code,purchasing_organization,purchasing_group,purchasing_group_id,currency,currency_id,payment_terms,delivery_terms,incoterms,contract_reference,pr_reference,quotation_reference,tender_reference,validity_date,status,remarks,total_amount,created_by,updated_by,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
    $h->execute([$poNo,$_POST['po_date'],$_POST['po_type'],$typeId,(int)$_POST['vendor_id'],$sourcePrId ?: null,$departmentName,$departmentId ?: null,$businessUnitName,$businessUnitId ?: null,trim($_POST['company_code']),trim($_POST['purchasing_organization']),$purchasingGroupCode,$purchasingGroupId ?: null,$currencyCode,$currencyId ?: null,trim($_POST['payment_terms']),trim($_POST['delivery_terms']),trim($_POST['incoterms']),trim($_POST['contract_reference']),trim($_POST['pr_reference']),trim($_POST['quotation_reference']),trim($_POST['tender_reference']),$_POST['validity_date']?:null,$status,trim($_POST['remarks']),$total,user()['id'],user()['id']]);
    $headerId=(int)$pdo->lastInsertId();

    $q=$pdo->prepare('INSERT INTO po_items (po_header_id,item_no,material_service_code,short_description,detailed_description,quantity,uom,unit_price,total_amount,delivery_date,delivery_location,account_assignment,cost_center,gl_account,tax_code,pr_reference_item,contract_reference,status,remarks,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
    $itemNo=10;
    foreach($items as $it){$lt=((float)$it['quantity']*(float)$it['unit_price']);$q->execute([$headerId,$itemNo,$it['material_service_code'],$it['short_description'],$it['detailed_description'],(float)$it['quantity'],$it['uom'],(float)$it['unit_price'],$lt,$it['delivery_date']?:null,$it['delivery_location'],$it['account_assignment'],$it['cost_center'],$it['gl_account'],$it['tax_code'],$it['pr_reference_item'],$it['contract_reference'],$status,$it['remarks']]);$itemNo+=10;}

    if ($isSubmit) {
      $wf = workflow_start_transaction('PO', 'PO', $headerId, $poNo, [
        'amount' => $total,
        'type' => $_POST['po_type'] ?? '',
        'department' => trim($_POST['department'] ?? ''),
        'business_unit' => trim($_POST['business_unit'] ?? '')
      ], (int)user()['id']);
      if ($wf['ok']) {
        $status = $wf['status'];
        $pdo->prepare('UPDATE po_headers SET status=?, updated_at=NOW() WHERE id=?')->execute([$status, $headerId]);
      } else {
        $pdo->prepare("UPDATE po_headers SET status='Draft', updated_at=NOW() WHERE id=?")->execute([$headerId]);
        $status='Draft';
        $_SESSION['flash_error'] = $wf['message'];
      }
    }

    $_SESSION['flash_success']="PO {$poNo} saved as {$status}."; header('Location:index.php'); exit;
}
?>
<div class="d-flex justify-content-between align-items-center mb-2"><h3>Create Purchase Order (PO)</h3><span class="text-muted-custom">SAP ME21N-inspired enterprise layout</span></div>
<form method="post" id="poForm">
<div class="card p-3 mb-3"><h6>Header Data</h6><div class="row g-2">
  <div class="col-md-2"><label class="form-label">PO Date</label><input type="date" class="form-control" name="po_date" value="<?= date('Y-m-d') ?>" required></div>
  <div class="col-md-2"><label class="form-label">PO Type</label><select class="form-select" name="po_type_id"><?php foreach($types as $t): ?><option value="<?= $t['id'] ?>"><?= e($t['type_name']) ?></option><?php endforeach; ?></select><input type="hidden" name="po_type" id="po_type_text"></div>
  <div class="col-md-4"><label class="form-label">Vendor/Supplier</label><select class="form-select" name="vendor_id" required><option value="">Select supplier</option><?php foreach($vendors as $v): ?><option value="<?= $v['id'] ?>"><?= e($v['supplier_code'].' - '.$v['supplier_legal_name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><label class="form-label">Department</label><select class="form-select" name="department_id"><option value="">Select</option><?php foreach($departments as $d): ?><option value="<?= $d['id'] ?>"><?= e($d['department_name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><label class="form-label">Business Unit</label><select class="form-select" name="business_unit_id"><option value="">Select</option><?php foreach($businessUnits as $b): ?><option value="<?= $b['id'] ?>"><?= e($b['business_unit_name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><label class="form-label">Company Code</label><input class="form-control" name="company_code"></div>
  <div class="col-md-2"><label class="form-label">Purchasing Org</label><input class="form-control" name="purchasing_organization"></div>
  <div class="col-md-2"><label class="form-label">Purchasing Group</label><select class="form-select" name="purchasing_group_id"><option value="">Select</option><?php foreach($purchasingGroups as $g): ?><option value="<?= $g['id'] ?>"><?= e($g['group_code']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><label class="form-label">Currency</label><select class="form-select" name="currency_id"><?php foreach($currencies as $c): ?><option value="<?= $c['id'] ?>" <?= $c['currency_code']=='AED' ? "selected" : "" ?>><?= e($c['currency_code']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><label class="form-label">Payment Terms</label><input class="form-control" name="payment_terms"></div>
  <div class="col-md-2"><label class="form-label">Delivery Terms</label><input class="form-control" name="delivery_terms"></div>
  <div class="col-md-2"><label class="form-label">Incoterms</label><input class="form-control" name="incoterms"></div>
  <div class="col-md-2"><label class="form-label">Contract Ref.</label><input class="form-control" name="contract_reference"></div>
  <div class="col-md-2"><label class="form-label">PR Reference</label><input class="form-control" name="pr_reference"></div>
  <div class="col-md-2"><label class="form-label">Source PR</label><select class="form-select" name="source_pr_header_id"><option value="">Select PR</option><?php foreach($prRefs as $r): ?><option value="<?= $r['id'] ?>"><?= e($r['pr_number']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><label class="form-label">Quotation Ref.</label><input class="form-control" name="quotation_reference"></div>
  <div class="col-md-2"><label class="form-label">Tender Ref.</label><input class="form-control" name="tender_reference"></div>
  <div class="col-md-2"><label class="form-label">Validity Date</label><input type="date" class="form-control" name="validity_date"></div>
  <div class="col-md-4"><label class="form-label">Remarks</label><input class="form-control" name="remarks"></div>
</div></div>

<div class="card p-3 mb-3"><div class="d-flex justify-content-between"><h6>Item Overview</h6><button type="button" class="btn btn-sm btn-outline-primary" id="addPoItem">Add Item</button></div>
<table class="table table-sm mt-2" id="poItemsTable"><thead><tr><th>Item</th><th>Code</th><th>Short Description</th><th>Qty</th><th>UOM</th><th>Unit Price</th><th>Total</th><th>Delivery</th><th>Action</th></tr></thead><tbody></tbody></table>
<div class="text-end"><strong>PO Total: AED <span id="poHeaderTotal">0.00</span></strong></div></div>

<div class="card p-3 mb-3"><h6>Item Detail Tabs</h6>
<ul class="nav nav-tabs"><li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#po-basic" type="button">Material/Service</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#po-delivery" type="button">Delivery</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#po-account" type="button">Account Assignment</button></li><li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#po-approval" type="button">Approval/Signature Preview</button></li></ul>
<div class="tab-content p-2 border border-top-0"><div class="tab-pane fade show active" id="po-basic">PO line data supports material/service, pricing, tax and references.</div><div class="tab-pane fade" id="po-delivery">Delivery schedule placeholders ready.</div><div class="tab-pane fade" id="po-account">Account assignment placeholders ready.</div><div class="tab-pane fade" id="po-approval">Future DOA/POA routing preview placeholder.</div></div>
</div>

<input type="hidden" name="items_json" id="poItemsJson">
<div class="d-flex gap-2 mb-4"><button class="btn btn-secondary" name="action_mode" value="draft">Save Draft</button><button class="btn btn-primary" name="action_mode" value="submit">Submit PO</button><button type="button" class="btn btn-outline-secondary" onclick="window.location.reload()">Clear Form</button></div>
</form>

<script>
(function(){
  const rows=[];const tb=document.querySelector('#poItemsTable tbody');const json=document.getElementById('poItemsJson');const totalEl=document.getElementById('poHeaderTotal');
  const make=()=>({material_service_code:'',short_description:'',detailed_description:'',quantity:1,uom:'EA',unit_price:0,delivery_date:'',delivery_location:'',account_assignment:'',cost_center:'',gl_account:'',tax_code:'',pr_reference_item:'',contract_reference:'',remarks:''});
  const render=()=>{tb.innerHTML='';let sum=0;rows.forEach((r,i)=>{const t=(Number(r.quantity)||0)*(Number(r.unit_price)||0);sum+=t;const tr=document.createElement('tr');tr.innerHTML=`<td>${(i+1)*10}</td><td><input class='form-control form-control-sm' value='${r.material_service_code}' data-k='material_service_code' data-i='${i}'></td><td><input class='form-control form-control-sm' value='${r.short_description}' data-k='short_description' data-i='${i}'></td><td><input type='number' step='0.01' class='form-control form-control-sm' value='${r.quantity}' data-k='quantity' data-i='${i}'></td><td><input class='form-control form-control-sm' value='${r.uom}' data-k='uom' data-i='${i}'></td><td><input type='number' step='0.01' class='form-control form-control-sm' value='${r.unit_price}' data-k='unit_price' data-i='${i}'></td><td>${t.toFixed(2)}</td><td><input type='date' class='form-control form-control-sm' value='${r.delivery_date}' data-k='delivery_date' data-i='${i}'></td><td><button type='button' class='btn btn-sm btn-outline-danger' data-del='${i}'>Delete</button></td>`;tb.appendChild(tr);});json.value=JSON.stringify(rows);totalEl.textContent=sum.toFixed(2);}
  document.getElementById('addPoItem').addEventListener('click',()=>{rows.push(make());render();});
  tb.addEventListener('input',e=>{const i=e.target.dataset.i,k=e.target.dataset.k;if(i!==undefined&&k){rows[i][k]=e.target.value;render();}});
  tb.addEventListener('click',e=>{const d=e.target.dataset.del;if(d!==undefined){rows.splice(Number(d),1);render();}});
  document.getElementById('poForm').addEventListener('submit',()=>{json.value=JSON.stringify(rows);const typeSel=document.querySelector('[name="po_type_id"]');const typeTxt=document.getElementById('po_type_text');if(typeSel&&typeTxt){typeTxt.value=typeSel.options[typeSel.selectedIndex]?.text||'';}});
  rows.push(make()); render();
})();
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
