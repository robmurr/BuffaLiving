<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Or specify the allowed domain for production
include 'db.php';

$post_id = $_GET['post_id'] ?? null;

if (!$post_id) {
    http_response_code(400);
    echo json_encode(["error" => "post_id is required"]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT * FROM comments WHERE post_id = :post_id ORDER BY created_at ASC");
    $stmt->bindParam(':post_id', $post_id);
    $stmt->execute();
    $comments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($comments);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
