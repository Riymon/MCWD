<?php

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if ($data === null) {
    echo json_encode(['error' => 'Invalid JSON data received']);
    exit;
}

// Validate required fields
if (empty($data['cons_name'])) {
    echo json_encode(['error' => 'Missing required fields']);
    exit;
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

    $searchName = $data['cons_name'];

    $stmt = $pdo -> prepare("SELECT c.acc_code, c.fullname, c.address, c.type, c.size, ch.prev_reading, ch.current_reading
                            FROM consumer c
                            LEFT JOIN  charges ch USING (acc_code)
                            WHERE c.fullname = ? LIMIT 1");
    $stmt -> execute([$searchName]);
    $result = $stmt -> fetch();

    if(!$result) {
        echo json_encode(['error' => 'No consumer found with that name']);
        exit;
    }

    echo json_encode([
            'success' => true,
            'data' => [
                'acc_code' => $result['acc_code'],
                'name' => $result['fullname'],
                'address' => $result['address'],
                'type' => $result['type'],
                'size' => $result['size'],
                'prev_reading' => $result['prev_reading'],
                'current_reading' => $result['current_reading']
            ]
        ]);


} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => 'An error occurred: ' . $e->getMessage()]);
}       
?>