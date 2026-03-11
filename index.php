<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$errors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prNumber = trim($_POST['pr_number'] ?? '');
    $poNumber = trim($_POST['po_number'] ?? '');
    $contractReference = trim($_POST['contract_reference'] ?? '');
    $bidderName = trim($_POST['bidder_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $subPrLink = trim($_POST['sub_pr_link'] ?? '');
    $isVariationOrder = isset($_POST['is_variation_order']) ? 1 : 0;

    if ($prNumber === '') {
        $errors[] = 'PR number is required.';
    }
    if ($poNumber === '') {
        $errors[] = 'PO number is required.';
    }
    if ($contractReference === '') {
        $errors[] = 'Contract reference is required.';
    }
    if ($bidderName === '') {
        $errors[] = 'Bidder name is required.';
    }
    if ($description === '') {
        $errors[] = 'Description is required.';
    }

    if ($subPrLink !== '' && filter_var($subPrLink, FILTER_VALIDATE_URL) === false) {
        $errors[] = 'Sub PR link must be a valid URL.';
    }

    if ($errors === []) {
        $sql = 'INSERT INTO pr_po_logs (
                    pr_number,
                    po_number,
                    contract_reference,
                    bidder_name,
                    description,
                    sub_pr_link,
                    is_variation_order
                ) VALUES (
                    :pr_number,
                    :po_number,
                    :contract_reference,
                    :bidder_name,
                    :description,
                    :sub_pr_link,
                    :is_variation_order
                )';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':pr_number' => $prNumber,
            ':po_number' => $poNumber,
            ':contract_reference' => $contractReference,
            ':bidder_name' => $bidderName,
            ':description' => $description,
            ':sub_pr_link' => $subPrLink !== '' ? $subPrLink : null,
            ':is_variation_order' => $isVariationOrder,
        ]);

        $successMessage = 'Entry saved successfully.';
    }
}

$logsStmt = $pdo->query('SELECT * FROM pr_po_logs ORDER BY created_at DESC, id DESC');
$logs = $logsStmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PR to PO Tracking Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <main class="container">
        <h1>PR to PO Tracking Dashboard</h1>
        <p class="intro">Track Purchase Requests (PR) to Purchase Orders (PO), including variation orders linked to the same PO.</p>

        <?php if ($successMessage !== ''): ?>
            <div class="alert success"><?= htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($errors !== []): ?>
            <div class="alert error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <section class="card">
            <h2>Add PR/PO Log</h2>
            <form method="post" action="">
                <div class="grid">
                    <label>
                        PR Number
                        <input type="text" name="pr_number" required>
                    </label>
                    <label>
                        PO Number
                        <input type="text" name="po_number" required>
                    </label>
                    <label>
                        Contract Reference
                        <input type="text" name="contract_reference" required>
                    </label>
                    <label>
                        Bidder Name
                        <input type="text" name="bidder_name" required>
                    </label>
                </div>

                <label>
                    Description
                    <textarea name="description" rows="3" required></textarea>
                </label>

                <label>
                    Sub PR Link (for variation orders)
                    <input type="url" name="sub_pr_link" placeholder="https://example.com/sub-pr/123">
                </label>

                <label class="checkbox">
                    <input type="checkbox" name="is_variation_order" value="1">
                    Variation Order (same PO)
                </label>

                <button type="submit">Save Entry</button>
            </form>
        </section>

        <section class="card">
            <h2>PR to PO Log</h2>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>PR Number</th>
                            <th>PO Number</th>
                            <th>Contract Reference</th>
                            <th>Bidder Name</th>
                            <th>Description</th>
                            <th>Sub PR Link</th>
                            <th>Variation Order</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($logs === []): ?>
                            <tr>
                                <td colspan="8" class="empty">No records yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['pr_number'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['po_number'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['contract_reference'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['bidder_name'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8') ?></td>
                                    <td>
                                        <?php if (!empty($row['sub_pr_link'])): ?>
                                            <a href="<?= htmlspecialchars($row['sub_pr_link'], ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener noreferrer">Open Link</a>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                    <td><?= (int) $row['is_variation_order'] === 1 ? 'Yes' : 'No' ?></td>
                                    <td><?= htmlspecialchars($row['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
