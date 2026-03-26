<?php
session_start();
require_once "../config/db.php";

$user_id = $_SESSION["user_id"];

// follow unfollow
if (isset($_POST['follow_unfollow'])) {
    $profile_user_id = $_POST["profile_user_id"];

    if ($user_id != $profile_user_id) {

        if (isFollowing($conn, $_SESSION['user_id'], $profile_user_id)) {
            // unfollow
            $stmt = $conn->prepare("DELETE FROM follows WHERE following_user_id = ? AND followed_user_id = ?");
            $stmt->execute([$_SESSION['user_id'], $profile_user_id]);
        } 
        
        else {
            // follow
            $stmt = $conn->prepare("INSERT INTO follows (following_user_id, followed_user_id) VALUES (?, ?)");
            $stmt->execute([$_SESSION['user_id'], $profile_user_id]);
        }

        // refreshaj stran
        header("Location: ../profile.php?id=" . $profile_user_id);
        exit;
    }
}

function isFollowing($conn, $follower_id, $followed_id) {
    $stmt = $conn->prepare("SELECT followed_user_id FROM follows WHERE following_user_id = ? AND followed_user_id = ?");
    $stmt->execute([$follower_id, $followed_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return true;
    } 
    else {
        return false;
    }
}

?>