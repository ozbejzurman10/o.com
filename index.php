<?php
session_start();
require_once "config/db.php";

$stmt = $conn->query("SELECT username, user_role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>

<body>

<?php if (isset($_SESSION["username"])): ?>
    <p>Signed in as: <strong><?php echo htmlspecialchars($_SESSION["username"]); ?></strong></p>
    <p><a href="logout.php">Logout</a></p>
<?php else: ?>
    <p>You are not logged in!</p>
    <p><a href="login.php">Login</a></p>
<?php endif; ?>

<h1>Uporabniki:</h1>

<ul>
<?php foreach ($users as $user): ?>
    <li><?php echo htmlspecialchars($user['username']); ?> <?php echo htmlspecialchars($user['user_role']); ?></li>
<?php endforeach; ?>
</ul>

<a href="signup.php">Sign up!</a>
<a href="create_post.php">Create a post</a>
<a href="show_posts.php">Show posts</a>

<?php if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin"): ?>
    <a href="admin_page.php">Admin</a>
<?php endif; ?>


</body>
</html>