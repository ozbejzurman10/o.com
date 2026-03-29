<?php
session_start();

// odstrani vse podatke
$_SESSION = [];

// unici sejo
session_destroy();

header("Location: index.php");
exit;