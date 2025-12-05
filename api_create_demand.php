<?php
header('Content-Type: application/json');

// ---------- DEBUG LOG ----------
$debugPath  = __DIR__ . "/debug_input.txt";
$now        = date("Y-m-d H:i:s");
$rawInput   = file_get_contents("php://input");
$headers    = function_exists('getallheaders') ? getallheaders() : [];

$debugMsg  = "==== $now ====\n";
$debugMsg .= "REMOTE_ADDR: " . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n";
$debugMsg .= "REQUEST_METHOD: " . ($_SERVER['REQUEST_METHOD'] ?? '') . "\n";
$debugMsg .= "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? '') . "\n";
$debugMsg .= "HEADERS: " . json_encode($headers) . "\n";
$debugMsg .= "_POST: " . json_encode($_POST) . "\n";
$debugMsg .= "RAW_INPUT: " . ($rawInput === "" ? "(empty)" : $rawInput) . "\n\n";
file_put_contents($debugPath, $debugMsg, FILE_APPEND);
// --------------------------------

// ---------- DB CONNECTION ----------
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

// ---------- READ BODY (JSON OR POST) ----------
$data = json_decode($rawInput, true);
if (empty($data) || !is_array($data)) {
    $data = $_POST;
}

// Normalise keys
$supplier = $data['supplier']  ?? $data['Supplier']  ?? $data['SUPPLIER']  ?? '';
$userName = $data['userName'] ?? $data['username'] ?? $data['user_name'] ?? '';
$amount   = $data['amount']   ?? $data['montant']  ?? 0;

// ---------- DETAILED VALIDATION ----------
$errors = [];

if ($supplier === '') {
    $errors[] = 'supplier empty';
}
if ($userName === '') {
    $errors[] = 'userName empty';
}
if (!is_numeric($amount)) {
    $errors[] = 'amount not numeric';
} elseif ($amount <= 0) {
    $errors[] = 'amount <= 0';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success'  => false,
        'error'    => 'Invalid input',
        'details'  => $errors,
        'received' => $data,
    ]);
    exit;
}

// Cast amount to float
$amount = (float)$amount;

// ---------- INSERT INTO demands ----------
$stmt = $conn->prepare(
    "INSERT INTO demands (supplier, user_name, amount_tnd) VALUES (?, ?, ?)"
);
$stmt->bind_param('ssd', $supplier, $userName, $amount);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => 'Insert failed',
        'sql_err' => $stmt->error,
    ]);
}

$stmt->close();
$conn->close();
