<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

$host = 'localhost';
$db   = 'project1';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die(json_encode(["error" => $e->getMessage()]));
}


$action = $_GET['action'] ?? 'read';
$method = $_SERVER['REQUEST_METHOD'];
// ----CRUD:D
// ccreate transaction
if ($method === 'POST' && $action === 'create') {
    $data = json_decode(file_get_contents("php://input"), true);
    $sql = "INSERT INTO sales (product, quantity, price, customer, transaction_date) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['product'], $data['quantity'], $data['price'], $data['customer'], $data['date']]);
    echo json_encode(["message" => "Sale added"]);
    exit;
}

// update Transaction
if ($method === 'POST' && $action === 'update') {
    $data = json_decode(file_get_contents("php://input"), true);
    $sql = "UPDATE sales SET product=?, quantity=?, price=?, customer=?, transaction_date=? WHERE id=?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['product'], $data['quantity'], $data['price'], $data['customer'], $data['date'], $data['id']]);
    echo json_encode(["message" => "Sale updated"]);
    exit;
}

// Delete Transaction
if ($method === 'POST' && $action === 'delete') {
    $data = json_decode(file_get_contents("php://input"), true);
    $sql = "DELETE FROM sales WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$data['id']]);
    echo json_encode(["message" => "Sale deleted"]);
    exit;
}

//------ Read (Analytics & Data Fetching)
//------ 1. Total Revenue
$stmt = $pdo->query("SELECT SUM(quantity * price) as total_revenue FROM sales");
$totalSales = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'];

//------ 2. Top Products (for Bar Chart)
$stmt = $pdo->query("SELECT product, SUM(quantity) as total_qty FROM sales GROUP BY product ORDER BY total_qty DESC LIMIT 5");
$topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

//------ 3. Monthly Sales (for Line Chart)
$stmt = $pdo->query("SELECT DATE_FORMAT(transaction_date, '%Y-%m') as month, SUM(quantity * price) as monthly_total FROM sales GROUP BY month ORDER BY month ASC");
$monthlySales = $stmt->fetchAll(PDO::FETCH_ASSOC);

//------ 4. Recent Transactions (for Table)
$stmt = $pdo->query("SELECT * FROM sales ORDER BY transaction_date DESC");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

//------ return all data as JSON
echo json_encode([
    "total_sales" => $totalSales,
    "top_products" => $topProducts,
    "monthly_sales" => $monthlySales,
    "transactions" => $transactions
]);
?>