<?php
// Вспомогательные функции для сессий и remember-me

if (!function_exists('clearAuthCookie')) {
    function clearAuthCookie() {
        $past = time() - 3600;
        // series_id
        setcookie('series_id', '', [
            'expires' => $past,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        // remember_token
        setcookie('remember_token', '', [
            'expires' => $past,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }
}

?>
 