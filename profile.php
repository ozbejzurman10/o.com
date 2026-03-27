<?php
session_start();
require_once "config/db.php";
require_once "helpers/helpers.php";

$error = "";

if (isset($_GET['id'])) {
    $profile_user_id = intval($_GET['id']); // ID uporabnika iz URL
} else {
    $profile_user_id = $_SESSION['user_id']; // moj profil, če ni ID
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
    $error = "User not found";
} else {
    $user_id = $user["id"];
    $username = $user["username"];
    $user_role = $user["user_role"];
    $user_bio = $user["bio"];
    $followers_count = $followerData['followers_count'];
    $display_name = $user["display_name"];
}



// profilna slika
if (isset($_POST['upload_image']) && isset($_FILES['profile_image'])) {

    $file = $_FILES['profile_image'];

    // osnovni podatki
    $fileName = $file['name'];
    $fileTmp = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileError = $file['error'];

    // vrsta datoteke
    $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    $allowed = ['jpg', 'jpeg', 'png'];

    if (in_array($fileType, $allowed)) {

        if ($fileError === 0) {

            if ($fileSize < 2 * 1024 * 1024) {

                // unikaten filename
                $newName = uniqid("user_pfp", true) . "." . $fileType;
                $destination = "profile_images/" . $newName;

                if (move_uploaded_file($fileTmp, $destination)) {

                    deleteOldPfp($conn, $_SESSION["user_id"]);

                    // shrani v bazo
                    $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $stmt->execute([$newName, $_SESSION["user_id"]]);

                    // refresh
                    $user['profile_image'] = $newName;

                } else {
                    $error = "Upload failed!";
                }

            } else {
                $error = "File too large!";
            }

        } else {
            $error = "Upload error!";
        }

    } else {
        $error = "Invalid file type!";
    }
}

// bio
if (isset($_POST['update_bio'])) {
    $new_bio = trim($_POST['new_bio']);

    $stmt = $conn->prepare("UPDATE users SET bio = ? WHERE id = ?");
    $stmt->execute([$new_bio, $profile_user_id]);

    $user_bio = $new_bio;
}

// display name
if (isset($_POST['update_display_name'])) {
    $new_display_name = trim($_POST['new_display_name']);

    $stmt = $conn->prepare("UPDATE users SET display_name = ? WHERE id = ?");
    $stmt->execute([$new_display_name, $profile_user_id]);

    $display_name = $new_display_name;
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
        <h1>Profile</h1>

        <?php if ($error): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <?php if (!$error): ?>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
            <p><strong>Display Name:</strong> <?php echo htmlspecialchars($display_name); ?></p>
            <p><strong>User ID:</strong> <?php echo htmlspecialchars($user_id); ?></p>
            <p><strong>User role:</strong> <?php echo htmlspecialchars($user_role); ?></p>
            <p><strong>User Bio:</strong> <?php echo htmlspecialchars($user_bio); ?></p>

            <p>Followers: <?php echo htmlspecialchars($followerData['followers_count']); ?></p>

            <?php if ($isOwnProfile): ?>
                <!-- edit bio -->
                <form method="POST">
                    <textarea name="new_bio" rows="4" cols="50"><?php echo htmlspecialchars($user_bio); ?></textarea>
                    <button type="submit" name="update_bio">Update Bio</button>
                </form>

                <!-- edit display name -->
                <form method="POST">
                    <input type="text" name="new_display_name" value="<?php echo htmlspecialchars($display_name); ?>"></input>
                    <button type="submit" name="update_display_name">Update Display Name</button>
                </form>

                <!-- pfp -->
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="profile_image" accept="image/*">
                    <button type="submit" name="upload_image">Upload</button>
                </form>


            <?php else: ?>
                <!-- follow -->
                <form method="POST" action="helpers/follow_handler.php">
                    <input type="hidden" name="profile_user_id" value="<?php echo $profile_user_id; ?>">
                    <button type="submit" name="follow_unfollow">
                        <?php echo isFollowing($conn, $_SESSION['user_id'], $profile_user_id) ? "Unfollow" : "Follow"; ?>
                    </button>
                </form>
            <?php endif; ?>

            <?php
            if (!empty($user['profile_image'])) {
                $img = $user['profile_image'];
            } else {
                $img = 'default.png';
            }
            ?>

            <img class="profile-pic" src="profile_images/<?php echo htmlspecialchars($img); ?>" alt="Avatar"
                style="width:100px;height:100px;">

        <?php endif; ?>


        <!-- list posts -->
        <div class="main-posts">
            <div class="posts-wrapper">
                <?php foreach ($posts as $post): ?>
                    <div class="post">
                        <div class="post-title"><?php echo htmlspecialchars($post['title']); ?></div>

                        <div class="post-content">
                            <?php echo nl2br(htmlspecialchars($post['content'])); ?>
                        </div>

                        <div class="post-footer">

                            <a href="profile.php?id=<?php echo $post['user_id'] ?>" class="post-bottom-profile">
                                <?php echo htmlspecialchars(getUsernameById($conn, $post['user_id'])); ?>
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
                                    <button type="submit" name="delete_post">Delete Post</button>
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
                </div>
            <?php endforeach; ?>
        </div>

    </div>



</body>

</html>