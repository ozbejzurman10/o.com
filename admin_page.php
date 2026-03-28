<?php
session_start();
require_once "config/db.php";
require_once "helpers/helpers.php";

$stmt = $conn->query("SELECT id, display_name, bio, username, email, user_role FROM users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// odstrani uporabnika
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
    $new_display_name = $_POST['edit_display_name'];
    $new_bio = $_POST['edit_bio'];
    $new_userrole = $_POST['edit_userrole'];
    $new_email = $_POST['edit_email'];

    $stmt = $conn->prepare("UPDATE users SET username = ?, display_name = ?, bio = ?, user_role = ?, email = ? WHERE id = ?");
    $stmt->execute([$new_username, $new_display_name, $new_bio, $new_userrole, $new_email, $user_id]);

    if ($_SESSION["user_id"] == $user_id) {
        $_SESSION["username"] = $new_username;
    }
    header("Location: admin_page.php");
    exit;
}

if (isset($_POST['visit_user'])) {
    $visit_user_id = $_POST['visit_user_id'];

    header("Location: profile.php?id=$visit_user_id");
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
        <div class="centered-container">
            <div class="logo">O</div>
            <div class="subtitle">Admin panel</div>
        </div>

        <div class="admin-layout">
            <div class="admin-card">
                <table class="admin-table">
                    <tr>
                        <th>Username</th>
                        <th>Display Name</th>
                        <th>Bio</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>

                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <input class="admin-input" type="text" name="edit_username"
                                    value="<?php echo htmlspecialchars($user['username']); ?>"
                                    form="form_<?php echo $user['id']; ?>">
                            </td>

                            <td>
                                <input class="admin-input" type="text" name="edit_display_name"
                                    value="<?php echo htmlspecialchars($user['display_name']); ?>"
                                    form="form_<?php echo $user['id']; ?>">
                            </td>

                            <td>
                                <textarea class="admin-textarea" name="edit_bio" form="form_<?php echo $user['id']; ?>"
                                    rows="3"><?php echo htmlspecialchars($user['bio']); ?></textarea>
                            </td>

                            <td>
                                <input class="admin-input" type="text" name="edit_email"
                                    value="<?php echo htmlspecialchars($user['email']); ?>"
                                    form="form_<?php echo $user['id']; ?>">
                            </td>

                            <td>
                                <select class="admin-select" name="edit_userrole"
                                    form="form_<?php echo $user['id']; ?>">
                                    <option value="user" <?php echo $user['user_role'] === 'user' ? 'selected' : ''; ?>>User</option>
                                    <option value="admin" <?php echo $user['user_role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                </select>
                            </td>

                            <td>
                                <form method="POST" id="form_<?php echo $user['id']; ?>" class="admin-inline-form">
                                    <input type="hidden" name="edit_user_id" value="<?php echo $user['id']; ?>">
                                    <button class="admin-save-btn" type="submit" name="edit_user">Save</button>
                                </form>

                                <form method="POST" id="form_<?php echo $user['id']; ?>" class="admin-inline-form">
                                    <input type="hidden" name="visit_user_id" value="<?php echo $user['id']; ?>">
                                    <button class="admin-save-btn" type="submit" name="visit_user">Profile</button>
                                </form>

                                <form method="POST" class="admin-inline-form">
                                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                    <button class="admin-delete-btn" type="submit" name="delete_user"
                                        onclick="return confirm('Delete user?')">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

</body>

</html>