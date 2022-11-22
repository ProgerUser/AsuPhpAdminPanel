<?php

class DictList
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
            'id' => 'ИД',
            'name' => 'Наименование',
            'dict_author' => 'Автор',
            'year_pub' => 'Дата публикации',
            'created_at' => 'Дата создания',
            'updated_at' => 'Дата обновления',
            'wordcnt' => 'Кол-во слов'
        ];

        return $ordering;
    }
}

?>
