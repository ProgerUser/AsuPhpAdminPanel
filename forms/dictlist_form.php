<fieldset>
    <div class="form-group">
        <label for="word">Слово *</label>
        <input type="text" name="word"
               value="<?php echo htmlspecialchars(($edit ? $wordlist['word'] : '') ?: '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Слово" class="form-control" required="required" id="word">
    </div>

    <div class="form-group">
        <label for="translate">Перевод *</label>
        <input type="text" name="translate"
               value="<?php echo htmlspecialchars(($edit ? $wordlist['translate'] : '') ?: '', ENT_QUOTES, 'UTF-8'); ?>"
               placeholder="Перевод" class="form-control" required="required" id="translate">
    </div>

    <div class="form-group">
        <label for="dict_ref">Ссылка на словарь</label>
        <textarea name="dict_ref" placeholder="Ссылка на словарь" class="form-control"
                  id="dict_ref"><?php echo htmlspecialchars((($edit) ? $wordlist['dict_ref'] : '') ?: '', ENT_QUOTES, 'UTF-8'); ?></textarea>
    </div>

    <div class="form-group text-center">
        <label></label>
        <button type="submit" class="btn btn-warning">Сохранить <span class="glyphicon glyphicon-send"></span></button>
    </div>
</fieldset>
