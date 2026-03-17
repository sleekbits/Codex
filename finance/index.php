<?php
require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_permission('finance_view');

$rows = $pdo->query("SELECT
  po.id,
  po.po_number,
  po.po_date,
  s.supplier_legal_name,
  po.total_amount,
  po.status,
  COALESCE(SUM(inv.invoice_amount + inv.tax_amount),0) invoice_total,
  COALESCE(SUM(pay.paid_amount),0) paid_total
FROM po_headers po
LEFT JOIN suppliers s ON s.id=po.vendor_id
LEFT JOIN finance_ap_invoices inv ON inv.po_header_id=po.id
LEFT JOIN finance_payments pay ON pay.ap_invoice_id=inv.id
GROUP BY po.id, po.po_number, po.po_date, s.supplier_legal_name, po.total_amount, po.status
ORDER BY po.id DESC")->fetchAll();
?>
<h3>Finance & Accounts Integration</h3>
<div class="card p-3">
  <table class="table table-sm table-bordered">
    <thead><tr><th>PO No</th><th>Date</th><th>Supplier</th><th>PO Amount</th><th>Invoice Amount</th><th>Paid Amount</th><th>Outstanding</th><th>Status</th></tr></thead>
    <tbody>
      <?php foreach($rows as $r): $out=(float)$r['invoice_total']-(float)$r['paid_total']; ?>
      <tr>
        <td><?= e($r['po_number']) ?></td>
        <td><?= e($r['po_date']) ?></td>
        <td><?= e($r['supplier_legal_name'] ?? '-') ?></td>
        <td><?= number_format((float)$r['total_amount'],2) ?></td>
        <td><?= number_format((float)$r['invoice_total'],2) ?></td>
        <td><?= number_format((float)$r['paid_total'],2) ?></td>
        <td><?= number_format($out,2) ?></td>
        <td><?= e($r['status']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
