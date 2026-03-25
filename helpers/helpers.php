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