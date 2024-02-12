<fieldset>
    <!-- Form Name -->
    <legend>Добавить пользователя</legend>
    <!-- Text input-->
    <div class="form-group">
        <label class="col-md-4 control-label">Логин</label>
        <div class="col-md-4 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
                <input type="text" name="user_name" autocomplete="off" placeholder="Логин пользователя" class="form-control"
                       value="<?php echo ($edit) ? $admin_account['user_name'] : ''; ?>" autocomplete="off">
            </div>
        </div>
    </div>
    <!-- Text input-->
    <div class="form-group">
        <label class="col-md-4 control-label">Пароль</label>
        <div class="col-md-4 inputGroupContainer">
            <div class="input-group">
                <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
                <input type="Пароль" name="password" autocomplete="off" placeholder="Пароль " class="form-control"
                       required="" autocomplete="off">
            </div>
        </div>
    </div>
    <!-- radio checks -->
    <div class="form-group">
        <label class="col-md-4 control-label">Роль</label>
        <div class="col-md-4">
            <div class="radio">
                <label>
                    <?php //echo $admin_account['admin_type'] ?>
                    <input type="radio" name="admin_type" value="super"
                           required="" <?php echo ($edit && $admin_account['admin_type'] == 'super') ? "checked" : ""; ?>/>
                    Супер админ
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="admin_type" value="admin"
                           required="" <?php echo ($edit && $admin_account['admin_type'] == 'admin') ? "checked" : ""; ?>/>
                    Админ
                </label>
            </div>
        </div>
    </div>
    <!-- Button -->
    <div class="form-group">
        <label class="col-md-4 control-label"></label>
        <div class="col-md-4">
            <button type="submit" class="btn btn-warning">Save <span class="glyphicon glyphicon-send"></span></button>
        </div>
    </div>
</fieldset>