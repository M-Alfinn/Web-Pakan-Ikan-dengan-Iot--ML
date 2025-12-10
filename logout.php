<?php
session_start();

// Hancurkan semua session
$_SESSION = [];
session_destroy();

// Redirect ke halaman login atau index
header("Location: index.php");
exit();
?>