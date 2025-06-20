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
}elseif ($action === 'search'){

    $search_by = $data['search_by'] ?? '';
    $search_term = $data['search_term'] ?? '';

    if (empty($search_by) || empty($search_term)) {
        echo json_encode(['error' => 'Search parameters are required']);
        exit;
    } else {
        switch ($search_by) {
            case 'month_bill':
                $stmt = $pdo->prepare("SELECT c.acc_code, c.fullname, ch.charges_id, 
                                        ch.gross_current_bill, ch.monthbill, p.payment_id, 
                                        p.amount, p.changes, p.mode_of_payment, p.payment_date, 
                                        CASE 
                                            WHEN EXISTS (
                                                SELECT 1 FROM charges 
                                                WHERE amount = ch.gross_current_bill OR amount > ch.gross_current_bill
                                            ) 
                                            THEN 'Yes' ELSE 'No' END AS PAID
                                    FROM consumer c
                                    LEFT JOIN charges ch ON c.acc_code = ch.acc_code
                                    LEFT JOIN payments p ON ch.charges_id = p.charges_id
                                    WHERE ch.monthbill = ?");
                $stmt->execute([$search_term]);
                $results = $stmt->fetchAll();
                echo json_encode(['data' => $results]);
                break;
            case 'charges_id':
                $stmt = $pdo->prepare("SELECT c.acc_code, c.fullname, ch.charges_id, 
                                        ch.gross_current_bill, ch.monthbill, p.payment_id, 
                                        p.amount, p.changes, p.mode_of_payment, p.payment_date, 
                                        CASE 
                                            WHEN EXISTS (
                                                SELECT 1 FROM charges 
                                                WHERE amount = ch.gross_current_bill OR amount > ch.gross_current_bill
                                            ) 
                                            THEN 'Yes' ELSE 'No' END AS PAID
                                    FROM consumer c
                                    LEFT JOIN charges ch ON c.acc_code = ch.acc_code
                                    LEFT JOIN payments p ON ch.charges_id = p.charges_id
                                    WHERE ch.charges_id = ?");
                $stmt->execute([$search_term]);
                $results = $stmt->fetchAll();
                echo json_encode(['data' => $results]);
                break;
            case 'payment_id':
                $stmt = $pdo->prepare("SELECT c.acc_code, c.fullname, ch.charges_id, 
                                        ch.gross_current_bill, ch.monthbill, p.payment_id, 
                                        p.amount, p.changes, p.mode_of_payment, p.payment_date, 
                                        CASE 
                                            WHEN EXISTS (
                                                SELECT 1 FROM charges 
                                                WHERE amount = ch.gross_current_bill OR amount > ch.gross_current_bill
                                            ) 
                                            THEN 'Yes' ELSE 'No' END AS PAID
                                    FROM consumer c
                                    LEFT JOIN charges ch ON c.acc_code = ch.acc_code
                                    LEFT JOIN payments p ON ch.charges_id = p.charges_id
                                    WHERE p.payment_id = ?");
                $stmt->execute([$search_term]);
                $results = $stmt->fetchAll();
                echo json_encode(['data' => $results]);
                break;
            case 'amount':
                $stmt = $pdo->prepare("SELECT c.acc_code, c.fullname, ch.charges_id, 
                                        ch.gross_current_bill, ch.monthbill, p.payment_id, 
                                        p.amount, p.changes, p.mode_of_payment, p.payment_date, 
                                        CASE 
                                            WHEN EXISTS (
                                                SELECT 1 FROM charges 
                                                WHERE amount = ch.gross_current_bill OR amount > ch.gross_current_bill
                                            ) 
                                            THEN 'Yes' ELSE 'No' END AS PAID
                                    FROM consumer c
                                    LEFT JOIN charges ch ON c.acc_code = ch.acc_code
                                    LEFT JOIN payments p ON ch.charges_id = p.charges_id
                                    WHERE p.amount LIKE ?");
                $stmt->execute([$search_term]);
                $results = $stmt->fetchAll();
                echo json_encode(['data' => $results]);
                break;
            case 'mode_of_payment':
                $stmt = $pdo->prepare("SELECT c.acc_code, c.fullname, ch.charges_id, 
                                        ch.gross_current_bill, ch.monthbill, p.payment_id, 
                                        p.amount, p.changes, p.mode_of_payment, p.payment_date, 
                                        CASE 
                                            WHEN EXISTS (
                                                SELECT 1 FROM charges 
                                                WHERE amount = ch.gross_current_bill OR amount > ch.gross_current_bill
                                            ) 
                                            THEN 'Yes' ELSE 'No' END AS PAID
                                    FROM consumer c
                                    LEFT JOIN charges ch ON c.acc_code = ch.acc_code
                                    LEFT JOIN payments p ON ch.charges_id = p.charges_id
                                    WHERE p.mode_of_payment LIKE ?");
                $stmt->execute(['%' . $search_term . '%']);
                $results = $stmt->fetchAll();
                echo json_encode(['data' => $results]);
                break;
            case 'paid':
                $stmt = $pdo->prepare("SELECT c.acc_code, c.fullname, ch.charges_id, 
                    ch.gross_current_bill, ch.monthbill, p.payment_id, 
                    p.amount, p.changes, p.mode_of_payment, p.payment_date, 
                    CASE 
                        WHEN EXISTS (
                            SELECT 1 FROM charges 
                            WHERE amount = ch.gross_current_bill OR amount > ch.gross_current_bill
                        ) 
                        THEN 'Yes' ELSE 'No' END AS PAID
                    FROM consumer c
                    LEFT JOIN charges ch ON c.acc_code = ch.acc_code
                    LEFT JOIN payments p ON ch.charges_id = p.charges_id");
                $stmt->execute();
                $results = $stmt->fetchAll();

                // Filter results in PHP
                $filtered = array_filter($results, function($row) use ($search_term) {
                    return stripos($row['PAID'], $search_term) !== false;
                });

                echo json_encode(['data' => array_values($filtered)]);
                break;
            default:
                echo json_encode(['error' => 'Invalid search criteria']);
                exit;
        }
    }
}