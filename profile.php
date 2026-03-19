<?php
session_start();
require_once "config/db.php";

$error = "";

$post_title = trim($_POST["post_title"] ?? "");
$post_content = trim($_POST["post_content"] ?? "");

if (!isset($_SESSION["user_id"])) {
    $error = "You are not logged in!";
}

else {
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION["user_id"]]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) { $error = "User not found!"; }

    else {
        $user_id = $user["id"];
        $username = $user["username"];
        $user_role = $user["user_role"];
        $user_bio = $user["bio"];
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
                $destination = "profile_images/users/" . $newName;

                if (move_uploaded_file($fileTmp, $destination)) {

                    deleteOldPfp($conn, $_SESSION["user_id"]);

                    // shrani v bazo
                    $stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                    $stmt->execute([$newName, $_SESSION["user_id"]]);

                    // refresh
                    $user['profile_image'] = $newName;

                } else { $error = "Upload failed!"; }

            } else { $error = "File too large!"; }

        } else { $error = "Upload error!"; }

    } else { $error = "Invalid file type!"; }
}

}

function deleteOldPfp($conn, $user_id) {
    $stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $userPfp = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($userPfp && !empty($userPfp['profile_image']) && $userPfp['profile_image'] !== 'default.png') {
        $oldImage = "profile_images/users/" . $userPfp['profile_image'];
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
</head>
<body>

<h1>Profile</h1>

<?php if ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>

<?php if (!$error): ?>
    <p><strong>Username:</strong> <?php echo htmlspecialchars($username); ?></p>
    <p><strong>User ID:</strong> <?php echo htmlspecialchars($user_id); ?></p>
    <p><strong>User role:</strong> <?php echo htmlspecialchars($user_role); ?></p>
    <p><strong>User Bio:</strong> <?php echo htmlspecialchars($user_bio); ?></p>

    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="profile_image">
        <button type="submit" name="upload_image">Upload</button>
    </form>

    <?php
    if (!empty($user['profile_image'])) {
        $img = $user['profile_image'];
    } 
    else {
        $img = 'default.png';
    }
    ?>

    <img src="profile_images/users/<?php echo htmlspecialchars($img); ?>" alt="Avatar" style="width:100px;height:100px;">
    
<?php endif; ?>

<p><a href="index.php">Nazaj na začetno stran</a></p>

</body>
</html>

