<?php
require_once "config/db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($username) || empty($email) || empty($password)) {
        $error = "Izpolnite vsa polja!";
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
                "INSERT INTO users (username, email, password, user_role)
                VALUES (?, ?, ?, 'user')"
            );

            if ($stmt->execute([$username, $email, $hashedPassword])) {
                $success = "Registracija uspešna!";
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
    <div class="logo">O</div>
    <div class="subtitle">Welcome new user!</div>
</div>


<div class="centered-container">
<form class="login_form" method="POST">
    <label>Email:</label>
    <input type="email" name="email">
    
    <label>Username:</label>
    <input type="text" name="username">

    <label>Password:</label>
    <input type="password" name="password">

    <button type="submit">Register</button>

    <div>Already have an account? <a href="login.php">Login</a></div>
    <div><a href="index.php" style="text-decoration: none;">← Back</a></div>

</form>
</div>

<?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green;"><?php echo $success; ?></p>
<?php endif; ?>



</body>
</html>