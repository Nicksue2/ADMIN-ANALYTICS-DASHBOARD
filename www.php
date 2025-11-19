<?php
/* --- DB --- */
$host = 'localhost'; $db = 'project1'; $user = 'root'; $pass = '';

try { 
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass); 
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
} catch (PDOException $e) { 
    die("Connection failed: " . $e->getMessage()); 
}

/* --- CRUD THINGZZ --- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            $stmt = $pdo->prepare("INSERT INTO sales1 (transaction_date, customer, product, quantity, price) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['date'], $_POST['customer'], $_POST['product'], $_POST['quantity'], $_POST['price']]);
        } elseif ($_POST['action'] === 'update') {
            $stmt = $pdo->prepare("UPDATE sales1 SET transaction_date=?, customer=?, product=?, quantity=?, price=? WHERE id=?");
            $stmt->execute([$_POST['date'], $_POST['customer'], $_POST['product'], $_POST['quantity'], $_POST['price'], $_POST['id']]);
        } elseif ($_POST['action'] === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM sales1 WHERE id=?");
            $stmt->execute([$_POST['id']]);
        }
        header("Location: www.php"); exit();
    }
}

/* --- DATA FETCHING --- */
// ------1. Total Revenue
$stmt = $pdo->query("SELECT SUM(quantity * price) as total FROM sales1");
$totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// ------ 2. Top Products
$stmt = $pdo->query("SELECT product, SUM(quantity) as qty FROM sales1 GROUP BY product ORDER BY qty DESC LIMIT 3");
$topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
$maxQty = 0; foreach($topProducts as $p) { if($p['qty'] > $maxQty) $maxQty = $p['qty']; }

// ------ 3. Monthly Sales 
$stmt = $pdo->query("SELECT DATE_FORMAT(transaction_date, '%Y-%m') as month, SUM(quantity * price) as total FROM sales GROUP BY month ORDER BY month ASC");
$monthlyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

$points = ""; 
$svgCircles = [];
$maxSales = 0; foreach($monthlyData as $m) { if($m['total'] > $maxSales) $maxSales = $m['total']; }


$chartHeight = 100;
$paddingY = 10; 
$usableHeight = $chartHeight - ($paddingY * 2);
$step = count($monthlyData) > 1 ? 100 / (count($monthlyData) - 1) : 0;

foreach($monthlyData as $i => $d) {
    $x = $i * $step;
    $ratio = ($d['total'] / ($maxSales ?: 1));
    $y = ($chartHeight - $paddingY) - ($ratio * $usableHeight); 
    $points .= "$x,$y ";
    $svgCircles[] = ['x' => $x, 'y' => $y, 'label' => date('M', strtotime($d['month']))];
}

// ------ 4. Transactions
$stmt = $pdo->query("SELECT * FROM sales1 ORDER BY transaction_date DESC");
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaleZzz Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="istilo.css">
</head>
<body>

<div class="main-wrapper">
    
    <div class="hero-header">
        <div class="container header-content">
            <div class="d-flex justify-content-between align-items-center fade-in">
                <div>
                    <h2 style="margin:0"><i class="fas fa-rocket"></i> Sales Analytics Dashboard</h2>
                    <small class="opacity-75">Project 1: DSA & ELECT1 FINALS</small>
                </div>
                <div class="controls-area">
                    <div style="display:flex; align-items:center; gap:5px;">
                        <input class="form-check-input" type="checkbox" id="darkModeSwitch">
                        <i class="fas fa-moon"></i>
                    </div>
                    <button class="btn-new" onclick="openModal('addSaleModal')">
                        <i class="fas fa-plus"></i> New Entry
                    </button>
                </div>
            </div>

            <div class="text-center fade-in" style="margin-top:40px;">
                <p class="opacity-75" style="text-transform:uppercase; margin-bottom:0;">Total Revenue Generated</p>
                <h1 class="display-3" id="totalSalesDisplay">₱<?php echo number_format($totalRevenue); ?></h1>
            </div>
        </div>
    </div>

    <div class="container content-overlap fade-in">
        
        <div class="row">
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h5 style="margin:0">Monthly Trends</h5>
                            <small class="opacity-75">Sales over time</small>
                        </div>
                        <div class="icon-circle bg-primary-soft"><i class="fas fa-chart-line"></i></div>
                    </div>
                    <div class="chart-container">
                        <svg viewBox="0 0 100 100" preserveAspectRatio="none">
                            <polyline points="<?php echo $points; ?>"></polyline>
                            <?php foreach($svgCircles as $c): ?>
                                <circle cx="<?php echo $c['x']; ?>" cy="<?php echo $c['y']; ?>"></circle>
                            <?php endforeach; ?>
                        </svg>
                        <div class="d-flex justify-content-between" style="margin-top:5px; font-size:0.75rem; color:gray;">
                            <?php foreach($svgCircles as $c): ?>
                                <span><?php echo $c['label']; ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h5 style="margin:0">Top Products</h5>
                            <small class="opacity-75">Highest quantity sold</small>
                        </div>
                        <div class="icon-circle bg-warning-soft"><i class="fas fa-chart-bar"></i></div>
                    </div>
                    <div class="chart-container d-flex align-items-end">
                        <?php 
                        $colors = ['#4e73df', '#1cc88a', '#36b9cc']; 
                        $i=0;
                        foreach($topProducts as $prod): 
                            // Max height 80% to leave room for tooltips
                            $h = ($maxQty > 0) ? ($prod['qty'] / $maxQty) * 80 : 0; 
                        ?>
                        <div class="bar-group">
                            <div class="bar" data-tooltip="<?php echo $prod['qty']; ?>" 
                                 style="height:<?php echo $h; ?>%; background:<?php echo $colors[$i++ % 3]; ?>;"></div>
                            <small style="margin-top:10px; font-size:0.7rem;"><?php echo $prod['product']; ?></small>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-header">
                <h5 style="margin:0"><i class="fas fa-list"></i> Recent Transactions</h5>
                <div style="display:flex; gap:10px;">
                    <input type="text" id="searchInput" class="search-box" placeholder="Search records...">
                    <button class="btn-excel" onclick="exportTableToCSV('sales.csv')">
                        <i class="fas fa-file-excel"></i> Export
                    </button>
                </div>
            </div>
            <div class="table-responsive">
                <table id="salesTable">
                    <thead>
                        <tr>
                            <th>Date</th> <th>Customer</th> <th>Product</th> <th>Qty</th> <th>Price</th> <th>Total</th> <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($transactions as $t): ?>
                        <tr>
                            <td><?php echo $t['transaction_date']; ?></td>
                            <td style="font-weight:bold"><?php echo $t['customer']; ?></td>
                            <td><?php echo $t['product']; ?></td>
                            <td><span class="badge"><?php echo $t['quantity']; ?></span></td>
                            <td>₱<?php echo number_format($t['price']); ?></td>
                            <td style="color:#4e73df; font-weight:bold">₱<?php echo number_format($t['quantity']*$t['price']); ?></td>
                            <td>
                                <button class="btn-action btn-edit" onclick='openEditModal(<?php echo json_encode($t); ?>)'>
                                    <i class="fas fa-pen"></i>
                                </button>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Delete this?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                                    <button class="btn-action btn-delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<div id="addSaleModal" class="modal-overlay">
    <div class="modal-content fade-in">
        <button class="btn-close" onclick="closeModal('addSaleModal')">×</button>
        <h3 style="margin-top:0">Add New Sale</h3>
        <form method="POST">
            <input type="hidden" name="action" value="create">
            <label>Date</label><input type="date" name="date" class="form-control" required>
            <label>Customer</label><input type="text" name="customer" class="form-control" required>
            <label>Product</label>
            <select name="product" class="form-select">
                <option>Gaming Laptop</option><option>Mechanical Keyboard</option><option>RTX 6767</option><option>Monitor 144hz</option>
            </select>
            <div class="d-flex gap-3">
                <div style="flex:1"><label>Qty</label><input type="number" name="quantity" value="1" class="form-control" required></div>
                <div style="flex:1"><label>Price</label><input type="number" name="price" class="form-control" required></div>
            </div>
            <button type="submit" class="btn-submit btn-primary">Save Transaction</button>
        </form>
    </div>
</div>

<div id="editSaleModal" class="modal-overlay">
    <div class="modal-content fade-in">
        <button class="btn-close" onclick="closeModal('editSaleModal')">×</button>
        <h3 style="margin-top:0; color:#f6c23e">Edit Transaction</h3>
        <form method="POST">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="id" id="editId">
            <label>Date</label><input type="date" name="date" id="editDate" class="form-control" required>
            <label>Customer</label><input type="text" name="customer" id="editCustomer" class="form-control" required>
            <label>Product</label>
            <select name="product" id="editProduct" class="form-select">
                <option>Gaming Laptop</option><option>Mechanical Keyboard</option><option>RTX 6767</option><option>Monitor 144hz</option>
            </select>
            <div class="d-flex gap-3">
                <div style="flex:1"><label>Qty</label><input type="number" name="quantity" id="editQty" class="form-control" required></div>
                <div style="flex:1"><label>Price</label><input type="number" name="price" id="editPrice" class="form-control" required></div>
            </div>
            <button type="submit" class="btn-submit btn-warning">Update Changes</button>
        </form>
    </div>
</div>

<script src="skrip.js"></script>
</body>
</html>