<?php
require_once "config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $display_name = trim($_POST["display_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Izpolnite vsa polja!";
    }

    elseif (strlen($display_name) > 32) {
    $error = "Display name lahko vsebuje največ 32 znakov!";
    }

    elseif (strlen($username) > 16) {
    $error = "Username lahko vsebuje največ 16 znakov!";
    }

    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $error = "Username lahko vsebuje samo črke, številke in _";
    }
    
    else {
        // Preveri če uporabnik že obstaja
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->execute([$email]);

        if ($check->rowCount() > 0) {
            $error = "Uporabnik s tem emailom že obstaja.";
        } 
        
        else {
            // Hash gesla
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Vstavi novega uporabnika
            $stmt = $conn->prepare(
                "INSERT INTO users (username, display_name, email, password, user_role)
                VALUES (?, ?, ?, ?, 'user')"
            );

            // Uspesna prijava
            if ($stmt->execute([$username, $display_name, $email, $hashedPassword])) {
                header("Location: login.php");
            } else {
                $error = "Napaka pri registraciji.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="centered-container">
    <div class="logo">O</a></div>
    <div class="subtitle">Welcome new user!</div>
</div>


<div class="centered-container">
<form class="login_form" method="POST">
    <label>Email</label>
    <input type="email" name="email">
    
    <label>Username</label>
    <input type="text" name="username" maxlength="16">

    <label>Display Name</label>
    <input type="text" name="display_name" maxlength="32">

    <label>Password</label>
    <input type="password" name="password">

    <?php if ($error): ?>
        <div style="color:red;"><?php echo $error; ?></div>
    <?php else: ?>
        <div> <br> </div>
    <?php endif; ?>

    <button type="submit">Register</button>

    <div>Already have an account? <a href="login.php">Login</a></div>
    <div><a href="index.php" style="text-decoration: none;">← Back</a></div>

</form>
</div>


</body>
</html>