<?php
// Force sessions to be stored locally to avoid XAMPP/WAMP issues
ini_set('session.save_path', __DIR__ . '/../sessions');

if (!file_exists(__DIR__ . '/../sessions')) {
    mkdir(__DIR__ . '/../sessions', 0777, true);
}

session_start();
?>
