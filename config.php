<?php
session_start();

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'smart_classroom');
define('BASE_URL', 'http://localhost/SmartClassroom/');
define('UPLOAD_PATH', __DIR__ . '/uploads/');

try {
    $pdo = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";charset=utf8", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("
    <div style='font-family:Arial;background:#f8d7da;padding:30px;margin:40px;border-radius:8px;color:#721c24;border:1px solid #f5c6cb;'>
        <h3>&#9888; Database Connection Failed</h3>
        <p><strong>Error:</strong> " . $e->getMessage() . "</p>
        <p>Steps to fix:<br>
        1. Open phpMyAdmin<br>
        2. Import <strong>database.sql</strong><br>
        3. Update DB_USER and DB_PASS in <strong>config.php</strong> if needed</p>
    </div>");
}
?>
