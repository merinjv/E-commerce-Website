<?php
session_start();
// remove all session variables
session_unset();
// destroy the session
session_destroy();
?>
<?php require_once(__DIR__ . "/partials/nav.php"); ?>
<?php
session_start();
$_SESSION['message'] = 'Successful Logout';
header("Location: login.php");
?>
