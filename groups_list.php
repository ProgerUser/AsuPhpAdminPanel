<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

$db = getDbInstance();
$groups = $db->get('`groups`');

include BASE_PATH . '/includes/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Группы доступа</h1>
            <a href="edit_group.php?operation=create" class="btn btn-success"><i class="glyphicon glyphicon-plus"></i> Создать группу</a>
        </div>
    </div>
    <table class="table table-striped table-bordered table-condensed">
        <thead>
        <tr>
            <th>ID</th>
            <th>Название</th>
            <th>Дни</th>
            <th>Время</th>
            <th>Отключена</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($groups as $g): ?>
            <tr>
                <td><?php echo (int)$g['id']; ?></td>
                <td><?php echo htmlspecialchars($g['name']); ?></td>
                <td><?php echo (int)$g['days_mask']; ?></td>
                <td><?php echo htmlspecialchars(($g['time_from'] ?? '') . ' - ' . ($g['time_to'] ?? '')); ?></td>
                <td><?php echo !empty($g['disabled']) ? 'Да' : 'Нет'; ?></td>
                <td>
                    <a href="edit_group.php?group_id=<?php echo (int)$g['id']; ?>&operation=edit" class="btn btn-primary btn-sm"><i class="glyphicon glyphicon-edit"></i></a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>


