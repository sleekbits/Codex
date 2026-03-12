<?php
require_once __DIR__ . '/../config/config.php';

function app_setting(string $key, string $default = ''): string
{
    global $pdo;
    static $settings = null;

    if ($settings === null) {
        $rows = $pdo->query('SELECT setting_key, setting_value FROM app_settings')->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }

    return $settings[$key] ?? $default;
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user']);
}

function user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function has_role(array $roles): bool
{
    $currentUser = user();
    if (!$currentUser) {
        return false;
    }

    return in_array($currentUser['role_name'], $roles, true);
}

function can_manage(): bool
{
    return has_role(['Admin', 'Manager']);
}

function require_role(array $roles): void
{
    if (!has_role($roles)) {
        $_SESSION['flash_error'] = 'You do not have permission to perform this action.';
        header('Location: /Codex/dashboard/index.php');
        exit;
    }
}

function flash(string $key): ?string
{
    if (!isset($_SESSION[$key])) {
        return null;
    }

    $message = $_SESSION[$key];
    unset($_SESSION[$key]);

    return $message;
}

function log_activity(?int $userId, string $action, string $description): void
{
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO activity_logs (user_id, action, description, ip_address, created_at) VALUES (?, ?, ?, ?, NOW())');
    $stmt->execute([$userId, $action, $description, $_SERVER['REMOTE_ADDR'] ?? 'CLI']);
}

function query_filters(): array
{
    return [
        'year' => $_GET['year'] ?? date('Y'),
        'month' => $_GET['month'] ?? '',
        'assigned_to' => $_GET['assigned_to'] ?? '',
        'contractor_id' => $_GET['contractor_id'] ?? '',
        'po_status_id' => $_GET['po_status_id'] ?? '',
        'type_id' => $_GET['type_id'] ?? '',
        'search' => trim($_GET['search'] ?? ''),
    ];
}

function tracking_where_sql(array $filters, array &$params): string
{
    $where = ['tr.deleted_at IS NULL'];

    if ($filters['year']) {
        $where[] = 'YEAR(tr.pr_receival_date) = ?';
        $params[] = $filters['year'];
    }
    if ($filters['month']) {
        $where[] = 'MONTH(tr.pr_receival_date) = ?';
        $params[] = $filters['month'];
    }
    foreach (['assigned_to' => 'tr.assigned_to_user_id', 'contractor_id' => 'tr.contractor_id', 'po_status_id' => 'tr.po_status_id', 'type_id' => 'tr.type_id'] as $key => $column) {
        if (!empty($filters[$key])) {
            $where[] = "$column = ?";
            $params[] = $filters[$key];
        }
    }
    if (!empty($filters['search'])) {
        $where[] = '(tr.pr_no LIKE ? OR tr.brief_description LIKE ? OR tr.wo_dwo_vo_ref LIKE ? OR tr.contract_reference LIKE ? OR c.contractor_name LIKE ? OR tr.po_no LIKE ? OR tr.remarks LIKE ?)';
        for ($i = 0; $i < 7; $i++) {
            $params[] = '%' . $filters['search'] . '%';
        }
    }

    return ' WHERE ' . implode(' AND ', $where);
}


function format_date(?string $date): string
{
    if (!$date) {
        return '-';
    }
    $ts = strtotime($date);
    return $ts ? date('d-M-Y', $ts) : '-';
}

function status_badge_class(string $status): string
{
    return match ($status) {
        'Released' => 'bg-success-subtle text-success-emphasis border border-success-subtle',
        'Rejected' => 'bg-danger-subtle text-danger-emphasis border border-danger-subtle',
        'Pending with Procurement' => 'bg-warning-subtle text-warning-emphasis border border-warning-subtle',
        'Pending with Business Unit' => 'bg-info-subtle text-info-emphasis border border-info-subtle',
        'Deleted' => 'bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle',
        default => 'bg-light text-dark border'
    };
}
