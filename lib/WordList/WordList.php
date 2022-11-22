<?php

class WordList
{
    /**
     *
     */
    public function __construct()
    {
    }

    /**
     *
     */
    public function __destruct()
    {
    }

    /**
     * Set friendly columns\' names to order tables\' entries
     */
    public function setOrderingValues()
    {
        $ordering = [
            'id' => 'Ид',
            'word' => 'Слово',
            'translate' => 'Перевод',
            'dict_ref' => 'Ссылка на словарь',
            'created_at' => 'Время создания',
            'updated_at' => 'Время обновления'
        ];

        return $ordering;
    }
}

?>
