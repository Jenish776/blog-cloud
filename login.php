<?php
// ✅ CORS HEADERS (must be at the top)
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: text/plain");

// ✅ Handle preflight (CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// ✅ Reject non-POST methods
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo "Method Not Allowed";
    exit();
}

// ✅ Start session and include DB
session_start();
include "connection.php";

// ✅ Read JSON input
// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['userName'] ?? '');
$password = $data['password'] ?? '';

// Check DB connection
if (!isset($obj) || !$obj) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database connection error."]);
    exit();
}

// Query the database securely
$stmt = $obj->prepare("SELECT id, username, password FROM register WHERE username = ?");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error."]);
    exit();
}
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Verify password (assuming it's hashed)
if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username']];
    echo json_encode(["success" => true, "message" => "Login successful"]);
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid credentials"]);
}
?>
