<?php
session_start();
require_once "config/db.php";
require_once "helpers/helpers.php";

$error = "";
$success = "";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $post_title = trim($_POST["post_title"] ?? "");
    $post_content = trim($_POST["post_content"] ?? "");

    if (!isset($_SESSION["user_id"])) {
        $error = "You must be logged in to create a post!";
    } else

        if ($post_title === "" || $post_content === "") {
            $error = "Please fill all fields!";
        } else {
            $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id, created_at) VALUES (?, ?, ?, ?)");
            $stmt->execute([$post_title, $post_content, $_SESSION["user_id"], date("Y-m-d H:i:s")]);
            $success = "Post created!";
            $post_title = "";
            $post_content = "";
        }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <title>New Post</title>
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="centered-container">
            <div class="logo">O</div>
            <div class="subtitle">New Post</div>
        </div>

        <div class="createpost-layout">
            <div class="createpost-card">
                <form class="createpost-form" method="POST">
                    <label>Title</label>
                    <input type="text" name="post_title" maxlength="80"
                        value="<?php echo htmlspecialchars($post_title ?? ""); ?>">

                    <label>Content</label>
                    <textarea name="post_content" rows="8"
                        maxlength="2000"><?php echo htmlspecialchars($post_content ?? ""); ?></textarea>

                    <?php if ($error): ?>
                        <div class="post-error"><?php echo htmlspecialchars($error); ?></div>
                    <?php elseif ($success): ?>
                        <div class="post-success"><?php echo htmlspecialchars($success); ?></div>
                    <?php else: ?>
                        <div class="post-success"><br></div>
                    <?php endif; ?>

                    <button type="submit">Post</button>
                    <a class="createpost-back" href="index.php">← Back</a>
                </form>
            </div>
        </div>
    </div>
</body>

</html>