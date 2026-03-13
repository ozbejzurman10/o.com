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
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Sign Up</title>
</head>
<body>

<h1>Registracija</h1>

<?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>

<?php if ($success): ?>
    <p style="color:green;"><?php echo $success; ?></p>
<?php endif; ?>


<form method="POST">
    <label>Uporabniško ime:</label><br>
    <input type="text" name="username"><br><br>

    <label>Email:</label><br>
    <input type="email" name="email"><br><br>

    <label>Geslo:</label><br>
    <input type="password" name="password"><br><br>

    <button type="submit">Registriraj se</button>
</form>

<p><a href="index.php">Nazaj na začetno stran</a></p>
    

</body>
</html>