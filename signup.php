<?php
require_once "config/db.php";

$error = "";
$username = "";
$display_name = "";
$email = "";
$password = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $display_name = trim($_POST["display_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Please fill in all fields!";
    }


    elseif (strlen($display_name) > 32) {
    $error = "Display name can contain a maximum of 32 characters!";;
    }

    elseif (strlen($username) > 16) {
    $error = "Username can contain a maximum of 16 characters!";
    }

    elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $error = "Username can only contain letters, numbers, and underscores!";
    }
    
    else {
        // preveri ce uporabnik ze obstaja
        $checkEmail = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkEmail->execute([$email]);

        $checkUsername = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $checkUsername->execute([$username]);

        // preveri mail
        if ($checkEmail->rowCount() > 0) {
            $error = "A user with this email already exists!";
        }

        // preveri username
        else if ($checkUsername->rowCount() > 0) {
                $error = "Username is already taken!";
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
                $error = "Registration failed. Please try again.";
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
    <input type="email" name="email" value="<?php echo $email; ?>">
    
    <label>Username</label>
    <input type="text" name="username" maxlength="16" value="<?php echo $username; ?>">

    <label>Display Name</label>
    <input type="text" name="display_name" maxlength="32" value="<?php echo $display_name; ?>">

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