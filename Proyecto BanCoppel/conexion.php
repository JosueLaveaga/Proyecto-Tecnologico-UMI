<?php
date_default_timezone_set('America/Mazatlan');

$dsn = 'pgsql:host=localhost;port=5432;dbname=database';
$user = 'postgres';
$password = '1';

try {
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: ' . $e->getMessage();
}
?>
