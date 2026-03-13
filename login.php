<?php
session_start();
require_once "config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "Fill in both fields!";
    } else {
        // Poišči uporabnika po uporabniškem imenu
        $stmt = $conn->prepare("SELECT id, username, password, user_role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {
            // Prijava uspešna
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["username"] = $user["username"];
            $_SESSION["user_role"] = $user['user_role'];

            // Preusmeri na začetno stran
            header("Location: index.php");
            exit;
        } else {
            $error = "Wrong username or password!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="centered-container">
    <div class="logo">O</div>
    <div class="subtitle">Welcome back!</div>
</div>

<?php if ($error): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<div class="centered-container">
<form class="login_form" method="POST">
    <label>Username:</label>
    <input type="text" name="username">

    <label>Password:</label>
    <input type="password" name="password">

    <button type="submit">Login</button>

    <div>Don’t have an account yet? <a href="signup.php">Sign up</a></div>
    <div><a href="index.php" style="text-decoration: none;">← Back</a></div>

</form>
</div>



</body>
</html>

