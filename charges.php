<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data === null) {
    echo json_encode(['error' => 'Invalid JSON data received: ' . json_last_error_msg()]);
    exit;
}

// Validate required fields
$required = ['acc_code', 'previous_reading', 'current_reading', 'monthbill'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['error' => "Missing required field: $field"]);
        exit;
    }
}

$host = 'localhost';
$db = 'mcwdcalculator';
$user = 'root';
$pass = 'Arong143';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    // Verify account exists
    $stmt = $pdo->prepare("SELECT acc_code FROM consumer WHERE acc_code = ?");
    $stmt->execute([$data['acc_code']]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['error' => 'Invalid Account Code. No record found!']);
        exit;
    }

    // Insert charge data
    $stmt = $pdo->prepare("INSERT INTO charges (
        acc_code, 
        prev_reading, 
        current_reading,
        consume, 
        water_fee, 
        franchise_tax, 
        pca, 
        pwa, 
        gross_current_bill, 
        monthbill
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $success = $stmt->execute([
        $data['acc_code'],
        $data['previous_reading'],
        $data['current_reading'],
        $data['consume'],
        $data['water_fee'],
        $data['ftax'],
        $data['pca'],
        $data['pwa'],
        $data['gross_bill'],
        $data['monthbill']
    ]);

    if ($success) {
        echo json_encode(['success' => true, 'message' => 'Data saved successfully']);
    } else {
        echo json_encode(['error' => 'Failed to save data']);
    }

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode([
        'error' => 'Database error',
        'details' => $e->getMessage(),
        'query' => isset($stmt) ? $stmt->queryString : null
    ]);
}
?>