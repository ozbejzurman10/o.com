<?php

require_once "config/db.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function getUserById(PDO $conn, int $id): ?array
{
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        return $user;
    }

    else return null;
}

function getUsernameById(PDO $conn, int $id): ?string
{
    $user = getUserById($conn, $id);

    if ($user["username"]) {
        return $user["username"];
    }

    else return null;
}

function getUserPfpById(PDO $conn, int $id): ?string
{
    $user = getUserById($conn, $id);

    if ($user["profile_image"]) {
        return $user["profile_image"];
    }

    else return null;
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

function isLiked($conn, $user_id, $post_id) {
    $stmt = $conn->prepare("SELECT 1 FROM likes WHERE user_id = ? AND post_id = ?");
    $stmt->execute([$user_id, $post_id]);
    return $stmt->fetch() ? true : false;
}

function getLikesCount($conn, $post_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM likes WHERE post_id = ?");
    $stmt->execute([$post_id]);
    return $stmt->fetchColumn();
}