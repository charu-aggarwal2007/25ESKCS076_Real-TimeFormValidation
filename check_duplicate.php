<?php
/**
 * check_duplicate.php
 * ------------------------------------------------------------
 * Called via fetch() from validation.js while the student is
 * still typing. Returns JSON: { "exists": true/false }
 * This is what makes the "real-time" part genuinely real-time —
 * it hits the live database, not just a client-side regex.
 */

header('Content-Type: application/json');
require_once 'config.php';

$type  = $_POST['type']  ?? '';
$value = trim($_POST['value'] ?? '');

if (!in_array($type, ['email', 'phone'], true) || $value === '') {
    echo json_encode(['exists' => false, 'error' => 'Invalid request']);
    exit;
}

$column = $type; // 'email' or 'phone' — both are safe, whitelisted values
$stmt = $conn->prepare("SELECT id FROM students WHERE $column = ? LIMIT 1");
$stmt->bind_param('s', $value);
$stmt->execute();
$stmt->store_result();

echo json_encode(['exists' => $stmt->num_rows > 0]);

$stmt->close();
$conn->close();
