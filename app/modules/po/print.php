<?php
$id = (int)($_GET['id'] ?? 1);
$stmt = db()->prepare('SELECT * FROM po_headers WHERE id=?'); $stmt->execute([$id]); $po = $stmt->fetch();
if(!$po){ echo '<div class="alert alert-warning">No PO found.</div>'; return; }
?>
<div class="card p-4" id="printArea"><h3>Purchase Order</h3><p><strong>PO #:</strong> <?=e($po['po_number'])?> | <strong>Date:</strong> <?=e($po['po_date'])?></p><p><strong>Vendor:</strong> <?=e($po['vendor_name'])?> (<?=e($po['supplier_code'])?>)</p><p><strong>Payment Terms:</strong> <?=e($po['payment_terms'])?> | <strong>Delivery Terms:</strong> <?=e($po['delivery_terms'])?></p><table class="table"><tr><th>Description</th><th>Total</th></tr><tr><td>Items as per attached schedule</td><td>AED <?=number_format((float)$po['total_amount'],2)?></td></tr></table><p><strong>Remarks:</strong> <?=e($po['remarks'])?></p><div class="row"><div class="col">Prepared By: __________</div><div class="col">Approved By: __________</div></div></div><button class="btn btn-dark mt-3" onclick="window.print()">Print</button>
