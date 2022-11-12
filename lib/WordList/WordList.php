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
            'id' => 'ID',
            'word' => 'Word',
            'translate' => 'Translate',
            'dict_ref' => 'Dict ref'
        ];

        return $ordering;
    }
}
?>
