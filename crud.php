<?php
// filepath: c:\xampp\htdocs\Webdevphp\mcwd\crud.php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(0);
$data = json_decode(file_get_contents('php://input'), true);
file_put_contents('debug_pay.txt', print_r($data, true));

$host = 'localhost';
$db = 'mcwdcalculator';
$user = 'root';
$pass = 'Arong143';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => FALSE,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}


$action = $data['action'] ?? '';

$cons_name =  $data['consumer_name'] ?? '' ;
$cons_add =  $data['consumer_add'] ?? '' ;
$cons_acc_code =   $data['account_code'] ?? '' ;
$cons_type =  $data['type'] ?? '' ;
$cons_size =  $data['consumer_size'] ?? '' ;

if ($action === 'create') {
     
    $stmt = $pdo -> prepare("INSERT INTO consumer (acc_code, fullname, address, type, size, created_at) 
                        VALUES (?, ?, ?, ?, ?, NOW())");

    $stmt -> execute([$cons_acc_code, $cons_name, $cons_add, $cons_type, $cons_size]);
    
    echo json_encode(['message' => 'Created successfully!']);
} elseif ($action === 'update') {

    $consumer = 0;
    $stmt = $pdo -> prepare("SELECT acc_code FROM consumer WHERE acc_code = ?");
    $stmt -> execute([$cons_acc_code]);
    $consumer = $stmt -> fetch();

    if(!empty($consumer)){
        if($cons_acc_code && $cons_add){
            $stmt = $pdo -> prepare("UPDATE consumer SET address = ? WHERE acc_code = ?");
            $stmt -> execute([$cons_add, $cons_acc_code]);
        }
        if($cons_acc_code && $cons_name){
            $stmt = $pdo -> prepare("UPDATE consumer SET fullname = ? WHERE acc_code = ?");
            $stmt -> execute([$cons_name, $cons_acc_code]);
        }
        if($cons_acc_code && $cons_type){
            $stmt = $pdo -> prepare("UPDATE consumer SET type = ? WHERE acc_code = ?");
            $stmt -> execute([$cons_type, $cons_acc_code]);
        }
        if($cons_acc_code && $cons_size){
            $stmt = $pdo -> prepare("UPDATE consumer SET size = ? WHERE acc_code = ?");
            $stmt -> execute([$cons_size, $cons_acc_code]);
        }

        $stmt = $pdo -> prepare("UPDATE consumer SET update_at = NOW() WHERE acc_code = ?");
        $stmt -> execute([$cons_acc_code]);
    }
    else  echo json_encode(['message' => 'Consumer isnt in the record!']);
    echo json_encode(['message' => 'Updated successfully!']);
} elseif ($action === 'delete') {
    // Handle delete logic

    $consumer = 0;
    $stmt = $pdo -> prepare("SELECT acc_code FROM consumer WHERE acc_code = ?");
    $stmt -> execute([$cons_acc_code]);
    $consumer = $stmt -> fetch();

        if(!empty($consumer)){
            $stmt = $pdo -> prepare("DELETE FROM consumer WHERE acc_code = ?");
            $stmt -> execute([$cons_acc_code]);
        }

    echo json_encode(['message' => 'Deleted successfully!']);
} elseif ($action === 'pay') {
    
    try {
        // Use $data directly (already decoded)
        $required = ['cons_acc_code', 'cons_charges_id', 'cons_month_bill', 'cons_payment_method', 'cons_amount', 'cons_change'];
        foreach ($required as $field) {
            if (empty($data[$field])) {
                throw new Exception("Missing required field: $field");
            }
        }

        // Validate payment
        $stmt = $pdo->prepare("SELECT charges_id FROM charges 
                              WHERE monthbill = ? AND charges_id = ? AND acc_code = ?");
        $stmt->execute([
            $data['cons_month_bill'],
            $data['cons_charges_id'],
            $data['cons_acc_code']
        ]);
        
        if (!$stmt->fetch()) {
            throw new Exception("Invalid charge reference");
        }

        // Process payment
        $stmt = $pdo->prepare("INSERT INTO payments 
                             (charges_id, amount, changes, mode_of_payment, payment_date) 
                             VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([
            $data['cons_charges_id'],
            $data['cons_amount'],
            $data['cons_change'],
            $data['cons_payment_method']
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Payment processed successfully',
            'payment_id' => $pdo->lastInsertId()
        ]);
        
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
    exit;
}