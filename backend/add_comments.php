<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Or specify the allowed domain for production

include 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$post_id = $data['post_id'] ?? null;
$username = $data['username'] ?? null;
$content = $data['content'] ?? null;

if (!$post_id || !$username || !$content) {
    http_response_code(400);
    echo json_encode(["error" => "post_id, username, and content are required"]);
    exit;
}

try {
    $stmt = $conn->prepare("INSERT INTO comments (post_id, username, content) VALUES (:post_id, :username, :content)");
    $stmt->bindParam(':post_id', $post_id);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':content', $content);
    $stmt->execute();

    echo json_encode(["message" => "Comment added successfully"]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
