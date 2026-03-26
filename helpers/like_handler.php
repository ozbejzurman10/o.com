<?php
session_start();
require_once "../config/db.php";

if (isset($_POST["like_post"]) && isset($_SESSION["user_id"])) {

    $user_id = $_SESSION["user_id"];
    $post_id = $_POST["post_id"];

    if (isLiked($conn, $user_id, $post_id)) {
        // unlike
        $stmt = $conn->prepare("DELETE FROM likes WHERE user_id = ? AND post_id = ?");
        $stmt->execute([$user_id, $post_id]);
    } else {
        // like
        $stmt = $conn->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $post_id]);
    }

    // redirect nazaj
    $redirect = $_SERVER['HTTP_REFERER'] ?? '../show_posts.php';
    header("Location: $redirect");
    exit;
}

function isLiked($conn, $user_id, $post_id) {
    $stmt = $conn->prepare("SELECT 1 FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$user_id, $post_id]);
    return $stmt->fetch() ? true : false;
}