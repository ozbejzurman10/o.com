<?php
session_start();
require_once "config/db.php";
require_once "helpers/helpers.php";


$stmt = $conn->prepare("SELECT * FROM posts ORDER BY created_at DESC");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Odstrani objavo
if (isset($_POST['delete_post'])) {
    $post_id = $_POST['post_id'];
    $stmt = $conn->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    header("Location: show_posts.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
</head>
<body>

<ul>
<?php foreach ($posts as $post): ?>
    <li>
        <h2><?php echo htmlspecialchars($post['title']); ?></h2>
        <p><?php echo htmlspecialchars($post['content']); ?></p>
        <small>Objavljeno: <?php echo htmlspecialchars($post['created_at']); ?></small>
        <small>Avtor: <?php echo htmlspecialchars(getUsernameById($conn, $post['user_id'])); ?></small>

        <?php if (
            (isset($_SESSION["user_id"]) && $_SESSION["user_id"] == $post['user_id']) 
            || 
            (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin")
            ): ?>

            <form method="POST" style="display:inline;">
                <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                <button type="submit" name="delete_post">Delete</button>
            </form>
        <?php endif; ?>
    </li>
<?php endforeach; ?>
</ul>

<a href="index.php">Home</a>

</body>