<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

// Log the raw input for debugging
$rawInput = file_get_contents("php://input");
error_log("Raw input: " . $rawInput);

// Decode the JSON input
$data = json_decode($rawInput, true);

// Log the decoded data
error_log("Decoded data: " . print_r($data, true));

// Extract post_id and vote_type with null-safe access
$post_id = $data['post_id'] ?? null;
$vote_type = $data['vote_type'] ?? null;

// Validate input
if ($post_id && ($vote_type === 'upvote' || $vote_type === 'downvote')) {
    $column = $vote_type === 'upvote' ? 'upvotes' : 'downvotes';
    $stmt = $conn->prepare("UPDATE forum_entries SET $column = $column + 1 WHERE id = :post_id");
    $stmt->bindParam(':post_id', $post_id);
    $stmt->execute();

    echo json_encode(["message" => "Vote updated successfully"]);
} else {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input"]);
}
?>
