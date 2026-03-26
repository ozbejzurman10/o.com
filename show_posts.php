<?php
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
    <link rel="stylesheet" href="style.css">
    <title>Posts</title>
</head>

<body>
    <?php include 'sidebar.php'; ?>

    <div class="main-posts">
        <div class="posts-wrapper">
            <?php foreach ($posts as $post): ?>
                <div class="post">
                    <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>

                    <div class="post-content">
                        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                    </div>

                    <div class="post-bottom">
                        <?php echo htmlspecialchars(getUsernameById($conn, $post['user_id'])); ?>

                        <?php echo htmlspecialchars($post['created_at']); ?>
                    </div>

                    <?php if (
                        (isset($_SESSION["user_id"]) && $_SESSION["user_id"] == $post['user_id'])
                        ||
                        (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin")
                    ): ?>

                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                            <button type="submit" name="delete_post">Delete Post</button>
                        </form>

                    <?php endif; ?>

                    <div class="post-bottom-right">

                        <?php echo getLikesCount($conn, $post['id']); ?>

                        <?php $liked = isLiked($conn, $_SESSION['user_id'], $post['id']); ?>

                        <form method="POST" action="helpers/like_handler.php" style="display:inline;">
                            <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">

                            <button type="submit" name="like_post"
                                class="<?php echo $liked ? 'liked-btn' : 'not-liked-btn'; ?>">

                                <?php echo "❤︎"?>
                            </button>
                        </form>


                    </div>

                </div>
            <?php endforeach; ?>
        </div>

    </div>
    </div>
</body>