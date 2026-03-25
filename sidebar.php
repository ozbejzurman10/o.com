<div class="sidenav">
    <a href="index.php" class="logo">O</a>

    <div class="nav-buttons">
        <a href="index.php">Home</a>
        <a href="create_post.php">New Post</a>
        <a href="show_posts.php">Posts</a>

        <?php if (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin"): ?>
            <a href="admin_page.php">Admin Tools</a>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION["user_id"])): ?>
        <div class="profile-div">
            <a href="profile.php">
                <img src="profile_images/<?php echo getUserPfpById($conn, $_SESSION["user_id"]); ?>" class="profile-pic">
            </a>

            <?php $username = $_SESSION["username"];

            if (strlen($username) > 9) {
                $short_username = substr($username, 0, 7) . "...";
            }
            ?>

            <div style="margin-left: -12px;">
                <a href="profile.php" class="profile-link">
                    <?php echo htmlspecialchars(isset($short_username) ? $short_username : $username); ?>
                </a>
                <a class="logout-link" href="logout.php">Logout</a>
            </div>


        </div>

    <?php else: ?>
        <div class="profile-div">
            <a href="login.php">
                <img src="profile_images/default.png" class="profile-pic">
            </a>

            <div style="margin-left: -12px;">
                <a href="profile.php" class="profile-link">
                    Guest
                </a>
                <a class="logout-link" href="login.php">Login</a>
            </div>


        </div>

    <?php endif; ?>
</div>