<?php
// header('Content-Type: application/json');
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

// // Get the raw POST data
// $json = file_get_contents('php://input');
// $data = json_decode($json, true);

if ($data === null) {
    echo json_encode(['error' => 'Invalid JSON data received']);
    exit;
}

// Validate required fields
if (empty($data['cons_name'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Database connection
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
    
    // Prepare SQL statement
        
    
    $stmt = $pdo->prepare("INSERT INTO consumer 
        (name, address, type, size)
        VALUES (?, ?, ?, ?)");
    
    // Execute with data
    $stmt->execute([
        $data['cons_name'],
        $data['con_address'],
        $data['con_type'],
        $data['size'],
    ]);
    
    echo json_encode(['success' => true, 'message' => 'Data saved successfully']);
    
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>