<?php
// Save a full debug snapshot for every request (appends)
$debugPath = __DIR__ . "/debug_input.txt";
$now = date("Y-m-d H:i:s");
$rawInput = file_get_contents("php://input");

// Collect raw headers (works in most setups)
$headers = function_exists('getallheaders') ? getallheaders() : [];
$headersJson = json_encode($headers);

// Build debug message
$debugMsg = "==== $now ====\n";
$debugMsg .= "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n";
$debugMsg .= "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? '') . "\n";
$debugMsg .= "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
$debugMsg .= "HEADERS: " . $headersJson . "\n";
$debugMsg .= "_POST: " . json_encode($_POST) . "\n";
$debugMsg .= "RAW_INPUT: " . ($rawInput === "" ? "(empty)" : $rawInput) . "\n\n";

// Append to debug file (file will grow; delete between tests if you want)
file_put_contents($debugPath, $debugMsg, FILE_APPEND);

// Proceed with handling input
header('Content-Type: application/json');

$host     = 'localhost';
$user     = 'root';
$password = '';
$dbname   = 'wordpress';

$conn = new mysqli($host, $user, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB connection error']);
    exit;
}

// Try to parse JSON first
$data = json_decode($rawInput, true);

// If JSON is empty or invalid, try to use $_POST (form-encoded)
if (empty($data) || !is_array($data)) {
    $data = $_POST;
}

// Normalise keys (lowercase names so both "userName" and "username" work)
$supplier = $data['supplier'] ?? $data['Supplier'] ?? $data['SUPPLIER'] ?? '';
// try different variants for username
$userName = $data['userName'] ?? $data['username'] ?? $data['user_name'] ?? '';
// amount: accept amount or montant
$amount   = $data['amount'] ?? $data['montant'] ?? 0;

// validation
if ($supplier === '' || $userName === '' || !is_numeric($amount) || $amount <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input', 'received' => $data]);
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO demands (supplier, user_name, amount_tnd) VALUES (?, ?, ?)"
);
$stmt->bind_param('ssd', $supplier, $userName, $amount);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Insert failed']);
}

$stmt->close();
$conn->close();
