<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_permission('po_add');
require_once __DIR__ . '/../includes/workflow_engine.php';

$types = $pdo->query("SELECT id, type_name FROM types ORDER BY type_name")->fetchAll();
$vendors = $pdo->query("SELECT id, supplier_code, supplier_legal_name FROM suppliers WHERE status=1 ORDER BY supplier_legal_name")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $items = json_decode($_POST['items_json'] ?? '[]', true); if(!is_array($items)) $items=[];
    if (!$items) { $_SESSION['flash_error']='At least one PO item is required.'; header('Location:index.php'); exit; }
    if (empty($_POST['vendor_id'])) { $_SESSION['flash_error']='Supplier is required.'; header('Location:index.php'); exit; }
    $isSubmit = ($_POST['action_mode'] ?? '') === 'submit';
    if ($isSubmit && !has_permission('po_submit')) { $_SESSION['flash_error']='You are not allowed to submit PO.'; header('Location:index.php'); exit; }
    $status = $isSubmit ? 'Submitted' : 'Draft';
    $total=0; foreach($items as $it){$total+=((float)$it['quantity']*(float)$it['unit_price']);}

    $prefix='PO'.date('Ymd');
    $st=$pdo->prepare("SELECT COUNT(*) FROM po_headers WHERE po_number LIKE ?");$st->execute([$prefix.'%']);
    $poNo=$prefix.str_pad((string)((int)$st->fetchColumn()+1),4,'0',STR_PAD_LEFT);

    $h=$pdo->prepare('INSERT INTO po_headers (po_number,po_date,po_type,vendor_id,department,business_unit,company_code,purchasing_organization,purchasing_group,currency,payment_terms,delivery_terms,incoterms,contract_reference,pr_reference,quotation_reference,tender_reference,validity_date,status,remarks,total_amount,created_by,updated_by,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())');
    $h->execute([$poNo,$_POST['po_date'],$_POST['po_type'],(int)$_POST['vendor_id'],trim($_POST['department']),trim($_POST['business_unit']),trim($_POST['company_code']),trim($_POST['purchasing_organization']),trim($_POST['purchasing_group']),trim($_POST['currency']),trim($_POST['payment_terms']),trim($_POST['delivery_terms']),trim($_POST['incoterms']),trim($_POST['contract_reference']),trim($_POST['pr_reference']),trim($_POST['quotation_reference']),trim($_POST['tender_reference']),$_POST['validity_date']?:null,$status,trim($_POST['remarks']),$total,user()['id'],user()['id']]);
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
  <div class="col-md-2"><label class="form-label">PO Type</label><select class="form-select" name="po_type"><?php foreach($types as $t): ?><option value="<?= e($t['type_name']) ?>"><?= e($t['type_name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-4"><label class="form-label">Vendor/Supplier</label><select class="form-select" name="vendor_id" required><option value="">Select supplier</option><?php foreach($vendors as $v): ?><option value="<?= $v['id'] ?>"><?= e($v['supplier_code'].' - '.$v['supplier_legal_name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-2"><label class="form-label">Department</label><input class="form-control" name="department"></div>
  <div class="col-md-2"><label class="form-label">Business Unit</label><input class="form-control" name="business_unit"></div>
  <div class="col-md-2"><label class="form-label">Company Code</label><input class="form-control" name="company_code"></div>
  <div class="col-md-2"><label class="form-label">Purchasing Org</label><input class="form-control" name="purchasing_organization"></div>
  <div class="col-md-2"><label class="form-label">Purchasing Group</label><input class="form-control" name="purchasing_group"></div>
  <div class="col-md-2"><label class="form-label">Currency</label><input class="form-control" name="currency" value="AED"></div>
  <div class="col-md-2"><label class="form-label">Payment Terms</label><input class="form-control" name="payment_terms"></div>
  <div class="col-md-2"><label class="form-label">Delivery Terms</label><input class="form-control" name="delivery_terms"></div>
  <div class="col-md-2"><label class="form-label">Incoterms</label><input class="form-control" name="incoterms"></div>
  <div class="col-md-2"><label class="form-label">Contract Ref.</label><input class="form-control" name="contract_reference"></div>
  <div class="col-md-2"><label class="form-label">PR Reference</label><input class="form-control" name="pr_reference"></div>
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
  document.getElementById('poForm').addEventListener('submit',()=>{json.value=JSON.stringify(rows);});
  rows.push(make()); render();
})();
</script>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
