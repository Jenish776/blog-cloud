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
$data = json_decode(file_get_contents("php://input"), true);
$username = trim($data['userName'] ?? '');
$password = $data['password'] ?? '';

echo "Username: $username, Password: $password\n"; // Debugging output

// ✅ Validate input
if (!$username || !$password) {
    echo "Missing credentials";
    exit();
}

// ✅ Query the database securely
$stmt = $obj->prepare("SELECT id, username, Password FROM register WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// ✅ Verify password (assuming it's hashed)
if ($user && password_verify($password, $user['Password'])) {
    $_SESSION['user'] = ['id' => $user['id'], 'username' => $user['username']];
    echo "Login successful";
} else {
    echo "Invalid credentials";
}
?>
