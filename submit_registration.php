<?php
/**
 * submit_registration.php
 * ------------------------------------------------------------
 * Handles the POST from index.php. Client-side JS already
 * validates in real time, but we NEVER trust the client —
 * every rule is re-checked here before touching the database.
 */

require_once 'config.php';

function fail($message) {
    $_SESSION['flash'] = ['type' => 'error', 'message' => $message];
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail('Invalid request method.');
}

// --------------------------------------------------------
// Collect + sanitize
// --------------------------------------------------------
$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$phone     = trim($_POST['phone'] ?? '');
$dob       = trim($_POST['dob'] ?? '');
$gender    = trim($_POST['gender'] ?? '');
$course    = trim($_POST['course'] ?? '');
$address   = trim($_POST['address'] ?? '');
$password  = $_POST['password'] ?? '';
$confirm   = $_POST['confirm_password'] ?? '';

// --------------------------------------------------------
// Server-side validation (mirrors validation.js rules)
// --------------------------------------------------------
if (!preg_match("/^[A-Za-z][A-Za-z\s.'-]{2,49}$/", $full_name)) {
    fail('Please enter a valid full name.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Please enter a valid email address.');
}

if (!preg_match('/^[6-9]\d{9}$/', $phone)) {
    fail('Please enter a valid 10-digit mobile number.');
}

if (empty($dob)) {
    fail('Please enter your date of birth.');
}
$dobDate = DateTime::createFromFormat('Y-m-d', $dob);
if (!$dobDate) {
    fail('Invalid date of birth format.');
}
$age = $dobDate->diff(new DateTime())->y;
if ($dobDate > new DateTime()) {
    fail('Date of birth cannot be in the future.');
}
if ($age < 15 || $age > 100) {
    fail('You must be between 15 and 100 years old to register.');
}

if (!in_array($gender, ['Male', 'Female', 'Other'], true)) {
    fail('Please select a gender.');
}

if ($course === '') {
    fail('Please select a course.');
}

if (strlen($address) < 10) {
    fail('Please enter your complete address (min 10 characters).');
}

if (strlen($password) < 8
    || !preg_match('/[A-Z]/', $password)
    || !preg_match('/[a-z]/', $password)
    || !preg_match('/\d/', $password)
    || !preg_match('/[^A-Za-z0-9]/', $password)) {
    fail('Password must be at least 8 characters and include uppercase, lowercase, a number, and a symbol.');
}

if ($password !== $confirm) {
    fail('Passwords do not match.');
}

// --------------------------------------------------------
// Duplicate check (race-condition-safe: DB has UNIQUE
// constraints too, this just gives a friendlier message)
// --------------------------------------------------------
$stmt = $conn->prepare("SELECT id FROM students WHERE email = ? OR phone = ? LIMIT 1");
$stmt->bind_param('ss', $email, $phone);
$stmt->execute();
$stmt->store_result();
if ($stmt->num_rows > 0) {
    $stmt->close();
    fail('A student with this email or phone number is already registered.');
}
$stmt->close();

// --------------------------------------------------------
// Insert
// --------------------------------------------------------
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$stmt = $conn->prepare(
    "INSERT INTO students (full_name, email, phone, dob, gender, course, address, password)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param('ssssssss', $full_name, $email, $phone, $dob, $gender, $course, $address, $hashedPassword);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Registration successful.'];
    $_SESSION['last_student'] = [
        'id' => $newId,
        'full_name' => $full_name,
        'email' => $email,
        'course' => $course
    ];
} else {
    $_SESSION['flash'] = ['type' => 'error', 'message' => 'Something went wrong. Please try again.'];
}

$stmt->close();
$conn->close();

header('Location: index.php');
exit;
