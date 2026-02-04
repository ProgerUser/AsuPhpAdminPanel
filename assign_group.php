<?php
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
require_once 'config/config.php';
require_once BASE_PATH . '/includes/auth_validate.php';

$db = getDbInstance();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
    $groups = $_POST['groups'] ?? [];
    if ($user_id) {
        $db->where('user_id', $user_id)->delete('user_groups');
        foreach ($groups as $gid) {
            $db->insert('user_groups', [ 'user_id' => $user_id, 'group_id' => (int)$gid ]);
        }
        $_SESSION['success'] = 'Группы пользователя обновлены';
    }
    header('Location: assign_group.php');
    exit;
}

$users = $db->get('admin_accounts', null, ['id','user_name']);
$groups = $db->get('groups', null, ['id','name']);

$selected_user = filter_input(INPUT_GET, 'user_id', FILTER_VALIDATE_INT);
$selected = [];
if ($selected_user) {
    $db->where('user_id', $selected_user);
    $rows = $db->get('user_groups', null, 'group_id');
    $selected = array_map(function($r){ return (int)$r['group_id']; }, $rows);
}

include BASE_PATH . '/includes/header.php';
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Назначение групп пользователям</h1>
        </div>
    </div>
    <form method="post">
        <div class="form-group">
            <label>Пользователь</label>
            <select name="user_id" class="form-control" onchange="location.href='assign_group.php?user_id='+this.value;">
                <option value="">Выберите пользователя</option>
                <?php foreach ($users as $u): ?>
                    <option value="<?php echo (int)$u['id']; ?>" <?php echo ($selected_user==(int)$u['id'])?'selected':''; ?>><?php echo htmlspecialchars($u['user_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($selected_user): ?>
            <div class="form-group">
                <label>Группы</label>
                <select name="groups[]" multiple class="form-control" size="12">
                    <?php foreach ($groups as $g): ?>
                        <option value="<?php echo (int)$g['id']; ?>" <?php echo in_array((int)$g['id'], $selected)?'selected':''; ?>><?php echo htmlspecialchars($g['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
        <?php endif; ?>
    </form>
</div>
<?php include BASE_PATH . '/includes/footer.php'; ?>





