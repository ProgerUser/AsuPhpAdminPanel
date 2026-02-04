<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

$operation = filter_input(INPUT_GET, 'operation');
$group_id = filter_input(INPUT_GET, 'group_id', FILTER_VALIDATE_INT);
$db = getDbInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = filter_input(INPUT_POST, 'name');
    $days_mask = (int)($_POST['days_mask'] ?? 0);
    $time_from = $_POST['time_from'] ?? '';
    $time_to = $_POST['time_to'] ?? '';
    $disabled = isset($_POST['disabled']) ? 1 : 0;
$pages = $_POST['pages'] ?? [];
$perms = $_POST['perms'] ?? [];
$dicts = $_POST['dicts'] ?? [];

    $data = [
        'name' => $name,
        'days_mask' => $days_mask,
        'time_from' => $time_from,
        'time_to' => $time_to,
        'disabled' => $disabled
    ];

    if ($operation === 'edit' && $group_id) {
        $db->where('id', $group_id);
        $db->update('`groups`', $data);
        $db->where('group_id', $group_id)->delete('group_pages');
        $db->where('group_id', $group_id)->delete('group_permissions');
        $db->where('group_id', $group_id)->delete('group_dicts');
    } else {
        $group_id = $db->insert('`groups`', $data);
    }

    foreach ($pages as $p) {
        $db->insert('group_pages', [ 'group_id' => $group_id, 'page' => $p ]);
    }
    foreach ($perms as $pid) {
        $db->insert('group_permissions', [ 'group_id' => $group_id, 'permission_id' => (int)$pid ]);
    }
    foreach ($dicts as $did) {
        $db->insert('group_dicts', [ 'group_id' => $group_id, 'dict_id' => (int)$did ]);
    }

    $_SESSION['success'] = 'Группа сохранена';
    header('Location: groups_list.php');
    exit;
}

$group = [ 'name' => '', 'days_mask' => 0, 'time_from' => '', 'time_to' => '', 'disabled' => 0 ];
$selected_pages = [];
$selected_perms = [];
$selected_dicts = [];
if ($operation === 'edit' && $group_id) {
    $db->where('id', $group_id);
    $group = $db->getOne('`groups`');
    $db->where('group_id', $group_id);
    $rows = $db->get('group_pages', null, 'page');
    $selected_pages = array_map(function($r){ return $r['page']; }, $rows);
    $db->where('group_id', $group_id);
    $rows = $db->get('group_permissions', null, 'permission_id');
    $selected_perms = array_map(function($r){ return (int)$r['permission_id']; }, $rows);
    $db->where('group_id', $group_id);
    $rows = $db->get('group_dicts', null, 'dict_id');
    $selected_dicts = array_map(function($r){ return (int)$r['dict_id']; }, $rows);
}

$all_pages = [
    'index.php','dictlist.php','wordlist.php','admin_users.php','add_dictlist.php','add_wordlist.php','transfer_words_authors.php'
];

// Справочники прав и словарей
$all_perms = $db->get('permissions', null, ['id','code','description']);
$dict_rows = $db->get('dict_list', null, ['id','name']);

include BASE_PATH . '/includes/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><?php echo ($operation==='edit'?'Редактирование':'Создание'); ?> группы</h1>
        </div>
    </div>
    <form method="post">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Название группы</label>
                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($group['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Дни недели (битовая маска 0-6, Пн=1&lt;&lt;0)</label>
                    <input type="number" name="days_mask" class="form-control" value="<?php echo (int)$group['days_mask']; ?>" min="0" max="127">
                    <p class="help-block">Напр.: только будни = 31, только выходные = 96, все дни = 127</p>
                </div>
                <div class="form-inline">
                    <div class="form-group">
                        <label>Время с</label>
                        <input type="time" name="time_from" class="form-control" value="<?php echo htmlspecialchars($group['time_from']); ?>">
                    </div>
                    <div class="form-group" style="margin-left:10px;">
                        <label>до</label>
                        <input type="time" name="time_to" class="form-control" value="<?php echo htmlspecialchars($group['time_to']); ?>">
                    </div>
                </div>
                <div class="checkbox" style="margin-top:10px;">
                    <label><input type="checkbox" name="disabled" <?php echo !empty($group['disabled'])?'checked':''; ?>> Отключить доступ</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Разрешённые страницы</label>
                    <select name="pages[]" multiple class="form-control" size="12">
                        <?php foreach ($all_pages as $p): ?>
                            <option value="<?php echo $p; ?>" <?php echo in_array($p, $selected_pages)?'selected':''; ?>><?php echo $p; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Права</label>
                    <select name="perms[]" multiple class="form-control" size="12">
                        <?php foreach ($all_perms as $perm): ?>
                            <option value="<?php echo (int)$perm['id']; ?>" <?php echo in_array((int)$perm['id'], $selected_perms)?'selected':''; ?>><?php echo htmlspecialchars($perm['code']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Доступные словари</label>
                    <select name="dicts[]" multiple class="form-control" size="12">
                        <?php foreach ($dict_rows as $d): ?>
                            <option value="<?php echo (int)$d['id']; ?>" <?php echo in_array((int)$d['id'], $selected_dicts)?'selected':''; ?>><?php echo (int)$d['id'] . ' — ' . htmlspecialchars($d['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Сохранить</button>
        <a class="btn btn-default" href="groups_list.php">Отмена</a>
    </form>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>


