<?php
include 'db.php';

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Or specify the allowed domain for production

try {
    $stmt = $conn->prepare("SELECT * FROM forum_entries ORDER BY created_at DESC");
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($posts);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
