<fieldset>

    <div class="form-group">
        <label for="id">ИД *</label>
        <input type="text" name="id"
               value="<?php echo htmlspecialchars(($edit ? $dictlist['id'] : '') ?: '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Название" class="form-control" required="required" id="id">
    </div>

    <div class="form-group">
        <label for="name">Название *</label>
        <input type="text" name="name"
               value="<?php echo htmlspecialchars(($edit ? $dictlist['name'] : '') ?: '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Название" class="form-control" required="required" id="name">
    </div>

    <div class="form-group">
        <label for="dict_author">Автор *</label>
        <input type="text" name="dict_author"
               value="<?php echo htmlspecialchars(($edit ? $dictlist['dict_author'] : '') ?: '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Автор" class="form-control" required="required" id="dict_author">
    </div>

    <div class="form-group">
        <label for="year_pub">Дата публикации *</label>
        <input type="date" name="year_pub"
               value="<?php echo htmlspecialchars(($edit ? $dictlist['year_pub'] : '') ?: '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Дата публикации" class="form-control" required="required" id="year_pub">
    </div>


    <div class="form-group text-center">
        <label></label>
        <button type="submit" class="btn btn-warning">Сохранить <span class="glyphicon glyphicon-send"></span></button>
    </div>
</fieldset>
