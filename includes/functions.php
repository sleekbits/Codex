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

function role_permissions(): array
{
    static $cacheByRole = [];
    $currentUser = user();
    if (!$currentUser) {
        return [];
    }

    $roleId = (int)($currentUser['role_id'] ?? 0);
    if ($roleId <= 0) {
        return [];
    }

    if (array_key_exists($roleId, $cacheByRole)) {
        return $cacheByRole[$roleId];
    }

    global $pdo;
    $stmt = $pdo->prepare('SELECT permission_key, is_allowed FROM role_permissions WHERE role_id=?');
    $stmt->execute([$roleId]);

    $permissions = [];
    foreach ($stmt->fetchAll() as $row) {
        $permissions[$row['permission_key']] = (int)$row['is_allowed'] === 1;
    }

    $cacheByRole[$roleId] = $permissions;
    return $permissions;
}

function has_permission(string $permissionKey): bool
{
    if (has_role(['Admin'])) {
        return true;
    }

    $permissions = role_permissions();
    if ($permissions === []) {
        // Backward compatibility for environments that still rely on role-only access.
        return can_manage();
    }

    return !empty($permissions[$permissionKey]);
}

function require_permission(string $permissionKey): void
{
    if (!has_permission($permissionKey)) {
        $_SESSION['flash_error'] = 'You do not have permission to access this module.';
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

function get_multi_filter(string $key): array
{
    if (!isset($_GET[$key])) {
        return [];
    }
    $raw = $_GET[$key];
    $vals = is_array($raw) ? $raw : explode(',', (string)$raw);
    $vals = array_values(array_filter(array_map(static fn($v) => trim((string)$v), $vals), static fn($v) => $v !== ''));
    return array_unique($vals);
}

function query_filters(): array
{
    return [
        'year' => get_multi_filter('year'),
        'month' => get_multi_filter('month'),
        'assigned_to' => get_multi_filter('assigned_to'),
        'contractor_id' => get_multi_filter('contractor_id'),
        'po_status_id' => get_multi_filter('po_status_id'),
        'type_id' => get_multi_filter('type_id'),
        'search' => trim($_GET['search'] ?? ''),
    ];
}

function where_in_clause(string $column, array $values, array &$params): ?string
{
    if (!$values) {
        return null;
    }
    $placeholders = implode(',', array_fill(0, count($values), '?'));
    foreach ($values as $value) {
        $params[] = $value;
    }
    return "$column IN ($placeholders)";
}

function tracking_where_sql(array $filters, array &$params): string
{
    $where = ['tr.deleted_at IS NULL'];

    if ($clause = where_in_clause('YEAR(tr.pr_receival_date)', $filters['year'] ?? [], $params)) {
        $where[] = $clause;
    }
    if ($clause = where_in_clause('MONTH(tr.pr_receival_date)', $filters['month'] ?? [], $params)) {
        $where[] = $clause;
    }

    foreach ([
        'assigned_to' => 'tr.assigned_to_user_id',
        'contractor_id' => 'tr.contractor_id',
        'po_status_id' => 'tr.po_status_id',
        'type_id' => 'tr.type_id'
    ] as $key => $column) {
        if ($clause = where_in_clause($column, $filters[$key] ?? [], $params)) {
            $where[] = $clause;
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

function selected_multi(array $values, $value): string
{
    return in_array((string)$value, array_map('strval', $values), true) ? 'selected' : '';
}
