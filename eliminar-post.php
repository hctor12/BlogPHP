<?php
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$id = $_POST['id'] ?? null;
if (!$id) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT autor_id FROM posts WHERE id = :id");
$stmt->execute(['id' => $id]);
$post = $stmt->fetch();

if (!$post || ($post['autor_id'] != $_SESSION['user_id'] && !$_SESSION['is_admin'])) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("DELETE FROM posts WHERE id = :id");
try {
    $stmt->execute(['id' => $id]);
    header('Location: index.php');
} catch(PDOException $e) {
    die('Error al eliminar el post');
}