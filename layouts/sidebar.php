<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';

$isActiveLink = static function (string $href) use ($currentPath): bool {
    return str_starts_with($currentPath, $href);
};

$menuGroups = [];

$menuGroups[] = [
    'id' => 'menu-core',
    'icon' => 'bi-grid-1x2-fill',
    'label' => 'Core',
    'items' => [
        ['href' => '/Codex/dashboard/index.php', 'icon' => 'bi-house-door', 'label' => 'Dashboard', 'show' => true],
    ],
];

$procurementItems = [
    ['href' => '/Codex/tracking/index.php', 'icon' => 'bi-table', 'label' => 'Tracking Database', 'show' => true],
    ['href' => '/Codex/pr/index.php', 'icon' => 'bi-journal-plus', 'label' => 'Create PR', 'show' => has_permission('pr_view') || has_permission('pr_add')],
    ['href' => '/Codex/po/index.php', 'icon' => 'bi-receipt', 'label' => 'Create PO', 'show' => has_permission('po_view') || has_permission('po_add')],
];
$menuGroups[] = ['id' => 'menu-procurement', 'icon' => 'bi-cart-check', 'label' => 'Procurement', 'items' => $procurementItems];

$workflowItems = [
    ['href' => '/Codex/workflow/index.php', 'icon' => 'bi-diagram-2', 'label' => 'Approval Hierarchy', 'show' => has_permission('workflow_hierarchy_view')],
    ['href' => '/Codex/workflow/inbox.php', 'icon' => 'bi-inboxes', 'label' => 'Workflow Inbox', 'show' => has_permission('workflow_action_view')],
    ['href' => '/Codex/doa/index.php', 'icon' => 'bi-diagram-2', 'label' => 'DOA Hierarchy', 'show' => has_role(['Admin'])],
    ['href' => '/Codex/poa/index.php', 'icon' => 'bi-diagram-3-fill', 'label' => 'POA Hierarchy', 'show' => has_role(['Admin'])],
];
$menuGroups[] = ['id' => 'menu-workflow', 'icon' => 'bi-bezier2', 'label' => 'Workflow', 'items' => $workflowItems];

$dataItems = [
    ['href' => '/Codex/imports/index.php', 'icon' => 'bi-upload', 'label' => 'Import', 'show' => true],
    ['href' => '/Codex/exports/index.php', 'icon' => 'bi-download', 'label' => 'Export', 'show' => true],
    ['href' => '/Codex/suppliers/index.php', 'icon' => 'bi-building', 'label' => 'Supplier/Vendor', 'show' => true],
    ['href' => '/Codex/finance/index.php', 'icon' => 'bi-cash-stack', 'label' => 'Finance', 'show' => has_permission('finance_view')],
    ['href' => '/Codex/master/index.php', 'icon' => 'bi-diagram-3', 'label' => 'Master Data', 'show' => has_role(['Admin'])],
];
$menuGroups[] = ['id' => 'menu-dataops', 'icon' => 'bi-database-gear', 'label' => 'Data Operations', 'items' => $dataItems];

$adminItems = [
    ['href' => '/Codex/users/index.php', 'icon' => 'bi-people', 'label' => 'Users', 'show' => has_role(['Admin'])],
    ['href' => '/Codex/roles/index.php', 'icon' => 'bi-shield-lock', 'label' => 'Roles', 'show' => has_role(['Admin'])],
    ['href' => '/Codex/settings/index.php', 'icon' => 'bi-gear', 'label' => 'Settings', 'show' => has_role(['Admin'])],
    ['href' => '/Codex/logs/index.php', 'icon' => 'bi-clock-history', 'label' => 'Audit Trail', 'show' => has_permission('logs_view')],
];
$menuGroups[] = ['id' => 'menu-admin', 'icon' => 'bi-sliders2-vertical', 'label' => 'Administration', 'items' => $adminItems];
?>
<div class="col-lg-3 col-xl-2 col-md-4 sidebar-wrap p-3">
    <aside class="sidebar modern-sidebar min-vh-100 p-3">
        <div class="sidebar-head mb-3">
            <div class="sidebar-eyebrow">Navigation</div>
            <div class="sidebar-title">Control Panel</div>
        </div>

        <nav class="sidebar-nav" aria-label="Sidebar menu">
            <ul class="list-unstyled m-0 sidebar-groups">
                <?php foreach ($menuGroups as $group): ?>
                    <?php
                    $visibleItems = array_values(array_filter($group['items'], static fn(array $item): bool => !empty($item['show'])));
                    if (!$visibleItems) {
                        continue;
                    }
                    $groupActive = false;
                    foreach ($visibleItems as $menuItem) {
                        if ($isActiveLink($menuItem['href'])) {
                            $groupActive = true;
                            break;
                        }
                    }
                    ?>
                    <li class="sidebar-group-item <?= $groupActive ? 'is-open' : '' ?>">
                        <button class="sidebar-group-toggle <?= $groupActive ? '' : 'collapsed' ?>" type="button" data-bs-toggle="collapse" data-bs-target="#<?= e($group['id']) ?>" aria-expanded="<?= $groupActive ? 'true' : 'false' ?>" aria-controls="<?= e($group['id']) ?>">
                            <span class="menu-main-left">
                                <i class="bi <?= e($group['icon']) ?>"></i>
                                <span><?= e($group['label']) ?></span>
                            </span>
                            <i class="bi bi-chevron-down menu-chevron"></i>
                        </button>
                        <div id="<?= e($group['id']) ?>" class="collapse submenu-collapse <?= $groupActive ? 'show' : '' ?>">
                            <ul class="list-unstyled mb-0 sidebar-submenu">
                                <?php foreach ($visibleItems as $menuItem): ?>
                                    <?php $activeItem = $isActiveLink($menuItem['href']); ?>
                                    <li>
                                        <a class="sidebar-submenu-link <?= $activeItem ? 'active' : '' ?>" href="<?= e($menuItem['href']) ?>">
                                            <i class="bi <?= e($menuItem['icon']) ?>"></i>
                                            <span><?= e($menuItem['label']) ?></span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </nav>
    </aside>
</div>
<div class="col-lg-9 col-xl-10 col-md-8 p-4 content-wrap">
<?php if ($msg = flash('flash_success')): ?><div class="alert alert-success shadow-sm"><?= e($msg) ?></div><?php endif; ?>
<?php if ($msg = flash('flash_error')): ?><div class="alert alert-danger shadow-sm"><?= e($msg) ?></div><?php endif; ?>
