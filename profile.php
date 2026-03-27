<?php
session_start();
require_once "config/db.php";
require_once "helpers/helpers.php";

$error = "";
$success = "";

if (isset($_GET['id'])) {
    $profile_user_id = intval($_GET['id']);
} else {
    $profile_user_id = $_SESSION['user_id'];
}

if (isset($_SESSION['user_id'])) {
    $isOwnProfile = ($_SESSION['user_id'] == $profile_user_id);
} else {
    header("Location: login.php");
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$profile_user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT COUNT(*) AS followers_count FROM follows WHERE followed_user_id = ?");
$stmt->execute([$profile_user_id]);
$followerData = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT * FROM posts WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$profile_user_id]);
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$user) {
    $error = "User not found!";
} else {
    $user_id = $user["id"];
    $username = $user["username"];
    $user_role = $user["user_role"];
    $user_bio = $user["bio"];
    $followers_count = $followerData['followers_count'];
    $display_name = $user["display_name"];
}

// following list
$followingUsers = [];
if (!$error && $isOwnProfile) {
    $stmt = $conn->prepare("SELECT u.id, u.username, u.display_name FROM follows f JOIN users u ON u.id = f.followed_user_id WHERE f.following_user_id = ? ORDER BY u.username ASC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $followingUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// save changes
if (!$error && $isOwnProfile && isset($_POST['save_profile'])) {
    $new_bio = trim($_POST['new_bio'] ?? "");
    $new_display_name = trim($_POST['new_display_name'] ?? "");

    if ((mb_strlen($new_display_name) > 32) || (mb_strlen($new_display_name) < 1)) {
        $error = "Display name must contain 1 to 32 characters!";
    } elseif (mb_strlen($new_bio) > 500) {
        $error = "Bio can be at most 500 characters!";
    } else {
        $stmt = $conn->prepare("UPDATE users SET display_name = ?, bio = ? WHERE id = ?");
        $stmt->execute([$new_display_name, $new_bio, $_SESSION["user_id"]]);

        $display_name = $new_display_name;
        $user_bio = $new_bio;

        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file = $_FILES['profile_image'];

            $fileName = $file['name'];
            $fileTmp = $file['tmp_name'];
            $fileSize = $file['size'];
            $fileError = $file['error'];
            $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            $allowed = ['jpg', 'jpeg', 'png'];

            if (!in_array($fileType, $allowed, true)) {
                $error = "Invalid file type!";
            } elseif ($fileError !== 0) {
                $error = "Upload error!";
            } elseif ($fileSize >= 2 * 1024 * 1024) {
                $error = "File too large!";
            } else {
                $newName = uniqid("user_pfp", true) . "." . $fileType;
                $destination = "profile_images/" . $newName;

                if (!is_dir("profile_images")) {
                    @mkdir("profile_images", 0777, true);
                }

                if (move_uploaded_file($fileTmp, $destination)) {
                    deleteOldPfp($conn, $_SESSION["user_id"]);
                    $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $stmt->execute([$newName, $_SESSION["user_id"]]);
                    $user['profile_image'] = $newName;
                } else {
                    $error = "Upload failed!";
                }
            }
        }

        if ($error === "") {
            $success = "Changes saved!";
        }
    }
}


function deleteOldPfp($conn, $user_id)
{
    $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userPfp = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userPfp && !empty($userPfp['profile_image']) && $userPfp['profile_image'] !== 'default.png') {
        $oldImage = "profile_images/" . $userPfp['profile_image'];
        if (file_exists($oldImage)) {
            unlink($oldImage);
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <?php include 'sidebar.php'; ?>

    <div class="main">

        <?php
        if (!empty($user['profile_image'])) {
            $img = $user['profile_image'];
        } else {
            $img = 'default.png';
        }
        ?>

        <div class="profile-layout">
            <div class="profile-card">
                <div class="profile-top">
                    <img class="profile-pic profile-pic-large"
                        src="profile_images/<?php echo htmlspecialchars($img); ?>">

                    <div class="profile-top-text">
                        <div class="profile-display"><?php echo htmlspecialchars($display_name); ?></div>
                        <div class="profile-username">@<?php echo htmlspecialchars($username); ?></div>
                        <div class="profile-stats">
                            <div><strong><?php echo htmlspecialchars($followers_count); ?></strong> followers</div>
                            <div><strong><?php echo htmlspecialchars(count($posts)); ?></strong> posts</div>
                        </div>
                    </div>
                </div>

                <div class="profile-bio"><?php echo nl2br(htmlspecialchars($user_bio ?: "")); ?></div>

                <?php if ($isOwnProfile): ?>
                    <div class="profile-section">

                        <form class="profile-form" method="POST" enctype="multipart/form-data">
                            <label>Display name</label>
                            <input type="text" name="new_display_name"
                                value="<?php echo htmlspecialchars($display_name); ?>" maxlength="32">

                            <label>Bio</label>
                            <textarea name="new_bio" rows="4"
                                maxlength="500"><?php echo htmlspecialchars($user_bio); ?></textarea>

                            <label>Profile image (optional)</label>
                            <input type="file" name="profile_image" accept="image/*">

                            <?php if ($error): ?>
                                <div class="profile-error"><?php echo htmlspecialchars($error); ?></div>
                            <?php elseif ($success): ?>
                                <div class="profile-success"><?php echo htmlspecialchars($success); ?></div>
                            <?php else: ?>
                                <div class="profile-success"> <br></div>
                            <?php endif; ?>

                            <button type="submit" name="save_profile" style="align-self: center;">Save Changes</button>
                        </form>


                    </div>

                    <div class="profile-section">
                        <div class="profile-section-title">Following</div>
                        <?php if (count($followingUsers) === 0): ?>
                            <div class="profile-following-empty">You’re not following anyone yet.</div>
                        <?php else: ?>
                            <div class="profile-following-list">
                                <?php foreach ($followingUsers as $fu): ?>
                                    <a class="profile-following-item"
                                        href="profile.php?id=<?php echo htmlspecialchars($fu['id']); ?>">
                                        <div class="profile-following-name">
                                            <?php echo htmlspecialchars($fu['display_name'] ?: $fu['username']); ?>
                                        </div>
                                        <div class="profile-following-username">
                                            @<?php echo htmlspecialchars($fu['username']); ?>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                <?php else: ?>
                    <div class="profile-section">
                        <form class="profile-action-form" method="POST" action="helpers/follow_handler.php">
                            <input type="hidden" name="profile_user_id" value="<?php echo $profile_user_id; ?>">
                            <button class="profile-action-btn" type="submit" name="follow_unfollow">
                                <?php echo isFollowing($conn, $_SESSION['user_id'], $profile_user_id) ? "Unfollow" : "Follow"; ?>
                            </button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>



        <!-- list posts -->
        <div class="profile-posts">
            <div class="posts-wrapper">
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>

                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>

                        <div class="post-footer">

                            <a href="profile.php?id=<?php echo $post['user_id'] ?>" class="post-bottom-profile">
                                <span class="post-author">
                                    <img class="post-author-pic"
                                        src="profile_images/<?php echo htmlspecialchars(getUserPfpById($conn, $post['user_id']) ?: 'default.png'); ?>"
                                        alt="Avatar">
                                    <span><?php echo htmlspecialchars(getUsernameById($conn, $post['user_id'])); ?></span>
                                </span>
                            </a>

                            <div class="post-bottom">
                                <?php echo htmlspecialchars($post['created_at']); ?>
                            </div>

                            <?php if (
                                (isset($_SESSION["user_id"]) && $_SESSION["user_id"] == $post['user_id'])
                                ||
                                (isset($_SESSION["user_role"]) && $_SESSION["user_role"] === "admin")
                            ): ?>

                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                                    <button class="delete-post-btn" type="submit" name="delete_post">Delete</button>
                                </form>

                            <?php endif; ?>

                            <div class="post-bottom-right">

                                <?php echo getLikesCount($conn, $post['id']); ?>

                                <?php
                                if (isset($_SESSION['user_id'])):
                                    $liked = isLiked($conn, $_SESSION['user_id'], $post['id']);
                                else:
                                    $liked = false;
                                    ?>

                                <?php endif; ?>


                                <form method="POST" action="helpers/like_handler.php" style="display:inline;">
                                    <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">

                                    <button type="submit" name="like_post"
                                        class="<?php echo $liked ? 'liked-btn' : 'not-liked-btn'; ?>">

                                        <?php echo "❤︎" ?>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

        </div>



</body>

</html>