<?php
session_start();
require_once "config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $post_title = trim($_POST["post_title"] ?? "");
    $post_content = trim($_POST["post_content"] ?? "");

    if (!isset($_SESSION["user_id"])) {
        $error = "You must be logged in to create a post.";
    } else

    if ($post_title === "" || $post_content === "") {
        $error = "Vnesite naslov in vsebino objave.";
    } else {
        $stmt = $conn->prepare("INSERT INTO posts (title, content, user_id, created_at) VALUES (?, ?, ?, ?)");    
        $stmt->execute([$post_title, $post_content, $_SESSION["user_id"], date("Y-m-d H:i:s")]);

    }
}
?>

<!DOCTYPE html>
<html lang="sl">
<head>
    <meta charset="UTF-8">
    <title>Ustvari objavo</title>
</head>
<body>

<h1>Ustvari objavo</h1>

<?php if ($error): ?>
    <p style="color:red;"><?php echo htmlspecialchars($error); ?></p>
<?php endif; ?>

<form method="POST">
    <label>Naslov:</label><br>
    <input type="text" name="post_title"><br><br>

    <label>Vsebina:</label><br>
    <textarea name="post_content"></textarea><br><br>

    <button type="submit">Ustvari objavo</button>
</form>

<p><a href="index.php">Nazaj na začetno stran</a></p>

</body>
</html>

