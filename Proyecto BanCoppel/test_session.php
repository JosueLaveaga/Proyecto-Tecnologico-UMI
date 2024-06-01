<?php
session_start();
$_SESSION['test'] = 'Test Session';
echo "Session test value: " . $_SESSION['test'];
?>
