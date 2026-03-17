<?php

require_once __DIR__ . '/functions.php';

function workflow_sync_document_status(string $documentCategory, int $documentId, string $status): void
{
    global $pdo;
    if ($documentCategory === 'PR') {
        $pdo->prepare('UPDATE pr_headers SET status=?, updated_at=NOW() WHERE id=?')->execute([$status, $documentId]);
    } elseif ($documentCategory === 'PO') {
        $pdo->prepare('UPDATE po_headers SET status=?, updated_at=NOW() WHERE id=?')->execute([$status, $documentId]);
    }
}

function workflow_log(int $transactionId, string $documentCategory, int $documentId, string $actionType, int $actionBy, ?int $actionTo, string $comments = '', array $metadata = []): void
{
    global $pdo;
    $actionRole = user()['role_name'] ?? null;
    $pdo->prepare('INSERT INTO workflow_audit_logs (workflow_transaction_id,document_type,document_id,action_type,action_by,action_role,action_to,comments,metadata_json,created_at) VALUES (?,?,?,?,?,?,?,?,?,NOW())')
        ->execute([$transactionId, $documentCategory, $documentId, $actionType, $actionBy, $actionRole, $actionTo, $comments ?: null, $metadata ? json_encode($metadata) : null]);
}

function workflow_match_hierarchy(string $documentCategory, string $module, array $context): ?array
{
    global $pdo;
    $amount = (float)($context['amount'] ?? 0);
    $type = trim((string)($context['type'] ?? ''));
    $department = trim((string)($context['department'] ?? ''));
    $businessUnit = trim((string)($context['business_unit'] ?? ''));

    $stmt = $pdo->prepare("SELECT * FROM workflow_hierarchy_headers WHERE is_active=1 AND document_category=? AND (applies_to_module='ALL' OR applies_to_module=?) AND threshold_from<=? AND threshold_to>=? AND (department='' OR department IS NULL OR department=?) AND (business_unit='' OR business_unit IS NULL OR business_unit=?) ORDER BY threshold_from DESC, id DESC");
    $stmt->execute([$documentCategory, $module, $amount, $amount, $department, $businessUnit]);
    $headers = $stmt->fetchAll();

    foreach ($headers as $header) {
        if (($header['type_mode'] ?? 'all_types') === 'all_types') {
            return $header;
        }
        $types = $pdo->prepare('SELECT * FROM workflow_hierarchy_types WHERE hierarchy_header_id=?');
        $types->execute([(int)$header['id']]);
        foreach ($types->fetchAll() as $row) {
            if (strcasecmp((string)$row['type_name'], $type) === 0) {
                return $header;
            }
        }
    }

    return null;
}

function workflow_stage_assignees(array $stage): array
{
    global $pdo;
    $userIds = [];

    if (!empty($stage['user_id'])) {
        $userIds[] = (int)$stage['user_id'];
    }
    if (!empty($stage['role_id'])) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE role_id=? AND is_active=1 ORDER BY id');
        $stmt->execute([(int)$stage['role_id']]);
        $roleUsers = array_map('intval', array_column($stmt->fetchAll(), 'id'));
        if (($stage['approval_mode'] ?? 'sequential') === 'parallel') {
            $userIds = array_merge($userIds, $roleUsers);
        } elseif ($roleUsers) {
            $userIds[] = $roleUsers[0];
        }
    }

    $userIds = array_values(array_unique(array_filter($userIds)));
    return $userIds;
}

function workflow_start_transaction(string $documentCategory, string $module, int $documentId, string $documentNumber, array $context, int $createdBy): array
{
    global $pdo;

    $header = workflow_match_hierarchy($documentCategory, $module, $context);
    if (!$header) {
        return ['ok' => false, 'message' => 'No active workflow hierarchy matched this document.'];
    }

    $stagesStmt = $pdo->prepare('SELECT * FROM workflow_hierarchy_stages WHERE hierarchy_header_id=? AND is_active=1 ORDER BY stage_no, id');
    $stagesStmt->execute([(int)$header['id']]);
    $stages = $stagesStmt->fetchAll();
    if (!$stages) {
        return ['ok' => false, 'message' => 'Selected hierarchy has no active stages.'];
    }

    $firstStageNo = (int)$stages[0]['stage_no'];
    $initialStatus = in_array(strtolower((string)$stages[0]['stage_type']), ['endorsement'], true) ? 'Under Endorsement' : 'Under Approval';

    $pdo->prepare('INSERT INTO workflow_transactions (document_type,document_id,document_number,hierarchy_header_id,current_stage_no,current_status,created_by,created_at,updated_at) VALUES (?,?,?,?,?,?,?,NOW(),NOW())')
        ->execute([$documentCategory, $documentId, $documentNumber, (int)$header['id'], $firstStageNo, $initialStatus, $createdBy]);
    $transactionId = (int)$pdo->lastInsertId();

    $stepInsert = $pdo->prepare('INSERT INTO workflow_transaction_steps (workflow_transaction_id,stage_no,stage_name,stage_type,approver_role_id,approver_user_id,assigned_user_id,action_status,allow_delegate,allow_sign_on_behalf,is_mandatory,created_at,updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,NOW())');

    foreach ($stages as $stage) {
        $assignees = workflow_stage_assignees($stage);
        if (!$assignees) {
            continue;
        }
        $status = ((int)$stage['stage_no'] === $firstStageNo) ? 'Pending' : 'Queued';
        foreach ($assignees as $assigneeId) {
            $stepInsert->execute([
                $transactionId,
                (int)$stage['stage_no'],
                $stage['stage_name'],
                $stage['stage_type'],
                $stage['role_id'] ?: null,
                $stage['user_id'] ?: null,
                $assigneeId,
                $status,
                (int)$stage['allow_delegate'],
                (int)$stage['allow_sign_on_behalf'],
                (int)$stage['is_mandatory'],
                date('Y-m-d H:i:s')
            ]);
        }
    }

    workflow_log($transactionId, $documentCategory, $documentId, 'submit', $createdBy, null, 'Workflow initiated');
    log_activity($createdBy, 'Workflow Submit', $documentCategory . ' #' . $documentNumber . ' submitted to workflow');

    return ['ok' => true, 'transaction_id' => $transactionId, 'status' => $initialStatus];
}

function workflow_next_stage(int $transactionId): void
{
    global $pdo;
    $tx = $pdo->prepare('SELECT * FROM workflow_transactions WHERE id=?');
    $tx->execute([$transactionId]);
    $transaction = $tx->fetch();
    if (!$transaction) {
        return;
    }

    $currentStage = (int)$transaction['current_stage_no'];
    $pending = $pdo->prepare("SELECT COUNT(*) FROM workflow_transaction_steps WHERE workflow_transaction_id=? AND stage_no=? AND action_status='Pending'");
    $pending->execute([$transactionId, $currentStage]);
    if ((int)$pending->fetchColumn() > 0) {
        return;
    }

    $next = $pdo->prepare('SELECT MIN(stage_no) FROM workflow_transaction_steps WHERE workflow_transaction_id=? AND stage_no>? AND action_status IN (\'Queued\',\'Pending\')');
    $next->execute([$transactionId, $currentStage]);
    $nextStage = (int)$next->fetchColumn();

    if ($nextStage > 0) {
        $pdo->prepare("UPDATE workflow_transaction_steps SET action_status='Pending', updated_at=NOW() WHERE workflow_transaction_id=? AND stage_no=? AND action_status='Queued'")
            ->execute([$transactionId, $nextStage]);
        $stageTypeStmt = $pdo->prepare('SELECT stage_type FROM workflow_transaction_steps WHERE workflow_transaction_id=? AND stage_no=? LIMIT 1');
        $stageTypeStmt->execute([$transactionId, $nextStage]);
        $stageType = strtolower((string)$stageTypeStmt->fetchColumn());
        $status = $stageType === 'endorsement' ? 'Under Endorsement' : 'Under Approval';
        $pdo->prepare('UPDATE workflow_transactions SET current_stage_no=?, current_status=?, updated_at=NOW() WHERE id=?')->execute([$nextStage, $status, $transactionId]);
        workflow_sync_document_status($transaction['document_type'], (int)$transaction['document_id'], $status);
        return;
    }

    $pdo->prepare("UPDATE workflow_transactions SET current_status='Released', updated_at=NOW() WHERE id=?")->execute([$transactionId]);
    workflow_sync_document_status($transaction['document_type'], (int)$transaction['document_id'], 'Released');
}

function workflow_action_step(int $stepId, string $action, int $actorId, string $comment = '', ?int $targetUserId = null): array
{
    global $pdo;
    $stmt = $pdo->prepare('SELECT s.*, t.document_type, t.document_id, t.id AS tx_id, t.current_stage_no FROM workflow_transaction_steps s JOIN workflow_transactions t ON t.id=s.workflow_transaction_id WHERE s.id=?');
    $stmt->execute([$stepId]);
    $step = $stmt->fetch();
    if (!$step) {
        return ['ok' => false, 'message' => 'Workflow step not found.'];
    }

    $canSignOnBehalf = has_permission('workflow_sign_on_behalf');
    $isOwner = (int)$step['assigned_user_id'] === $actorId;
    if (!$isOwner && !$canSignOnBehalf) {
        return ['ok' => false, 'message' => 'You are not authorized to act on this step.'];
    }

    if (in_array($action, ['reject', 'return'], true) && trim($comment) === '') {
        return ['ok' => false, 'message' => 'Comment is mandatory for reject/return action.'];
    }

    if ($action === 'delegate') {
        if (!(int)$step['allow_delegate'] || !$targetUserId) {
            return ['ok' => false, 'message' => 'Delegation is not allowed for this step.'];
        }
        $pdo->prepare('UPDATE workflow_transaction_steps SET delegated_to_user_id=?, assigned_user_id=?, updated_at=NOW() WHERE id=?')->execute([$targetUserId, $targetUserId, $stepId]);
        workflow_log((int)$step['tx_id'], $step['document_type'], (int)$step['document_id'], 'delegate', $actorId, $targetUserId, $comment, ['step_id' => $stepId]);
        log_activity($actorId, 'Workflow Delegate', 'Delegated step #' . $stepId . ' to user #' . $targetUserId);
        return ['ok' => true, 'message' => 'Step delegated.'];
    }

    if ($action === 'reassign') {
        if (!has_permission('workflow_reassign') || !$targetUserId) {
            return ['ok' => false, 'message' => 'Reassignment requires permission and target user.'];
        }
        $pdo->prepare('UPDATE workflow_transaction_steps SET reassigned_by=?, assigned_user_id=?, updated_at=NOW() WHERE id=?')->execute([$actorId, $targetUserId, $stepId]);
        workflow_log((int)$step['tx_id'], $step['document_type'], (int)$step['document_id'], 'reassign', $actorId, $targetUserId, $comment, ['step_id' => $stepId]);
        log_activity($actorId, 'Workflow Reassign', 'Reassigned step #' . $stepId . ' to user #' . $targetUserId);
        return ['ok' => true, 'message' => 'Step reassigned.'];
    }

    if (!in_array($step['action_status'], ['Pending'], true)) {
        return ['ok' => false, 'message' => 'This step is no longer pending.'];
    }

    if ($action === 'endorse' && !has_permission('workflow_action_endorse')) {
        return ['ok' => false, 'message' => 'No endorsement permission.'];
    }
    if ($action === 'approve' && !has_permission('workflow_action_approve')) {
        return ['ok' => false, 'message' => 'No approval permission.'];
    }
    if ($action === 'reject' && !has_permission('workflow_action_reject')) {
        return ['ok' => false, 'message' => 'No reject permission.'];
    }
    if ($action === 'return' && !has_permission('workflow_action_return')) {
        return ['ok' => false, 'message' => 'No return permission.'];
    }

    if (in_array($action, ['endorse', 'approve'], true)) {
        $pdo->prepare("UPDATE workflow_transaction_steps SET action_status='Approved', action_date=NOW(), comments=?, signed_on_behalf_by=?, updated_at=NOW() WHERE id=?")
            ->execute([$comment ?: null, $isOwner ? null : $actorId, $stepId]);
        workflow_log((int)$step['tx_id'], $step['document_type'], (int)$step['document_id'], $action, $actorId, null, $comment, ['step_id' => $stepId]);
        log_activity($actorId, 'Workflow ' . ucfirst($action), ucfirst($action) . ' on step #' . $stepId);
        workflow_next_stage((int)$step['tx_id']);
        return ['ok' => true, 'message' => ucfirst($action) . ' recorded.'];
    }

    if ($action === 'reject') {
        $pdo->prepare("UPDATE workflow_transaction_steps SET action_status='Rejected', action_date=NOW(), rejection_reason=?, comments=?, updated_at=NOW() WHERE id=?")
            ->execute([$comment, $comment, $stepId]);
        $pdo->prepare("UPDATE workflow_transactions SET current_status='Rejected', updated_at=NOW() WHERE id=?")->execute([(int)$step['tx_id']]);
        workflow_sync_document_status($step['document_type'], (int)$step['document_id'], 'Rejected');
        workflow_log((int)$step['tx_id'], $step['document_type'], (int)$step['document_id'], 'reject', $actorId, null, $comment, ['step_id' => $stepId]);
        log_activity($actorId, 'Workflow Reject', 'Rejected step #' . $stepId);
        return ['ok' => true, 'message' => 'Rejected and returned to creator.'];
    }

    if ($action === 'return') {
        $pdo->prepare("UPDATE workflow_transaction_steps SET action_status='Returned', action_date=NOW(), return_reason=?, comments=?, updated_at=NOW() WHERE id=?")
            ->execute([$comment, $comment, $stepId]);
        $pdo->prepare("UPDATE workflow_transactions SET current_status='Returned for Correction', updated_at=NOW() WHERE id=?")->execute([(int)$step['tx_id']]);
        workflow_sync_document_status($step['document_type'], (int)$step['document_id'], 'Returned for Correction');
        workflow_log((int)$step['tx_id'], $step['document_type'], (int)$step['document_id'], 'return', $actorId, null, $comment, ['step_id' => $stepId]);
        log_activity($actorId, 'Workflow Return', 'Returned step #' . $stepId . ' for correction');
        return ['ok' => true, 'message' => 'Returned for correction.'];
    }

    return ['ok' => false, 'message' => 'Unknown action.'];
}
