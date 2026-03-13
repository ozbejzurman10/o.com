<?php
session_start();

// Odstrani vse podatke iz seje
$_SESSION = [];

// Uniči sejo
session_destroy();

// Preusmeri nazaj na index
header("Location: index.php");
exit;