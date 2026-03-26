<?php
session_start();
require_once "config/db.php";
require_once "helpers/helpers.php";

$stmt = $conn->query("SELECT id, username, email, user_role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Odstrani uporabnika
if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    header("Location: admin_page.php");
    exit;
}

if (isset($_POST['edit_user'])) {
    $user_id = $_POST['edit_user_id'];
    $new_username = $_POST['edit_username'];
    $new_userrole = $_POST['edit_userrole'];
    $new_email = $_POST['edit_email'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, user_role = ?, email = ? WHERE id = ?");
    $stmt->execute([$new_username, $new_userrole, $new_email, $user_id]);

    if ($_SESSION["user_id"] == $user_id) {
        $_SESSION["username"] = $new_username;
    }
    header("Location: admin_page.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>Admin</title>
</head>

<body>

    <?php include 'sidebar.php'; ?>
    <div class="main">
        <h1>Uporabniki:</h1>

        <ul>
            <?php foreach ($users as $user): ?>
                <li>

                    <?php echo htmlspecialchars($user['username']); ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                        <button type="submit" name="delete_user">Delete</button>
                    </form>

                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="edit_user_id" value="<?php echo $user['id']; ?>">
                        <input type="text" name="edit_username" value="<?php echo htmlspecialchars($user['username']); ?>">
                        <select name="edit_userrole">
                            <option value="user" <?php echo $user['user_role'] === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo $user['user_role'] === 'admin' ? 'selected' : ''; ?>>Admin
                            </option>
                        </select>
                        <input type="text" name="edit_email" value="<?php echo htmlspecialchars($user['email']); ?>">

                        <button type="submit" name="edit_user">Submit Changes</button>
                    </form>

                <?php endforeach; ?>
        </ul>


        <a href="signup.php">Sign up!</a>
        <a href="create_post.php">Create a post</a>
        <a href="show_posts.php">Show posts</a>
        <a href="index.php">Home</a>
    </div>

</body>

</html>