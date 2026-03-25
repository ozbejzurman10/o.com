<?php
session_start();
require_once "config/db.php";
require_once "helpers/helpers.php";

$stmt = $conn->query("SELECT username, user_role, id FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Home</title>
</head>

<body>
    
    <?php include 'sidebar.php'; ?>

    <div class="main">
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
                <li>
                    <?php echo htmlspecialchars($user['username']); ?>
                    <?php echo htmlspecialchars($user['user_role']); ?>

                    <a href="profile.php?id=<?php echo $user['id']; ?>">
                        <?php echo htmlspecialchars($user['username']); ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>

        <a href="signup.php">Sign up!</a>
        <a href="create_post.php">Create a post</a>
        <a href="show_posts.php">Show posts</a>
        <a href="profile.php">Profile</a>

        <?php if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin"): ?>
            <a href="admin_page.php">Admin</a>
        <?php endif; ?>

    </div>

</body>

</html>