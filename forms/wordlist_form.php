<fieldset>
    <div class="form-group">
        <label for="word">Слово *</label>
        <input type="text" name="word"
               value="<?php echo htmlspecialchars(($edit ? $wordlist['word'] : '') ?: '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Слово" class="form-control" required="required" id="word">
    </div>

    <div class="form-group">
        <label for="translate">Перевод *</label>
        <textarea name="translate" placeholder="Ссылка на словарь" class="form-control"
                  id="translate"><?php echo htmlspecialchars((($edit) ? $wordlist['translate'] : '') ?: '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div class="form-group">
        <label for="dict_ref">Ссылка на словарь *</label>
        <input type="text" name="dict_ref"
               value="<?php echo htmlspecialchars(($edit ? $wordlist['dict_ref'] : '') ?: '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Ссылка на словарь" class="form-control" required="required" id="dict_ref">
    </div>

    <div class="form-group text-center">
        <label></label>
        <button type="submit" class="btn btn-warning">Сохранить <span class="glyphicon glyphicon-send"></span></button>
    </div>
</fieldset>
