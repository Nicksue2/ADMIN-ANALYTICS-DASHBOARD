<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SaleZzz Analytics Dashboard</title>

    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.min.css" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="main-wrapper">
    
    <div class="hero-header text-white p-5">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4 fade-in">
                <div>
                    <h2 class="fw-bold mb-0"><i class="fas fa-rocket me-2"></i>Sales Analytics Dashboard</h2>
                    <small class="opacity-75">Project 1: DSA & ELECT1 FINALS</small>
                </div>
                <div class="d-flex gap-3 align-items-center">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="darkModeSwitch">
                        <label class="form-check-label" for="darkModeSwitch">
                            <i class="fas fa-moon text-white"></i>
                        </label>
                    </div>
                    <button class="btn btn-light rounded-pill text-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addSaleModal">
                        <i class="fas fa-plus me-2"></i> New Entry
                    </button>
                </div>
            </div>

            <div class="text-center mt-5 fade-in">
                <p class="text-uppercase letter-spacing opacity-75 mb-1">Total Revenue Generated</p>
                <h1 class="display-3 fw-bold" id="totalSalesDisplay">Loading...</h1>
            </div>
        </div>
    </div>

    <div class="container content-overlap fade-in">
        
        <div class="row g-4 mb-4">
            <div class="col-md-6">
                <div class="card shadow-lg border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="fw-bold text-secondary m-0">Monthly Trends</h5>
                                <small class="text-muted">Sales over time</small>
                            </div>
                            <div class="bg-primary bg-opacity-10 p-2 rounded-circle text-primary">
                                <i class="fas fa-chart-line"></i>
                            </div>
                        </div>
                        <div style="height: 300px; position: relative;">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-lg border-0 h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div>
                                <h5 class="fw-bold text-secondary m-0">Top Selling Products</h5>
                                <small class="text-muted">Highest quantity sold</small>
                            </div>
                            <div class="bg-warning bg-opacity-10 p-2 rounded-circle text-warning">
                                <i class="fas fa-chart-bar"></i>
                            </div>
                        </div>
                        <div style="height: 300px; position: relative;">
                            <canvas id="topProductsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow-lg border-0 mb-5">
            <div class="card-header bg-transparent border-0 py-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <h5 class="fw-bold m-0 text-secondary">
                    <i class="fas fa-list me-2"></i>Recent Transactions
                </h5>
                <div class="d-flex gap-2 search-group">
                    <input type="text" id="searchInput" class="form-control rounded-pill border-0" placeholder="Search records...">
                    <button class="btn btn-excel rounded-pill px-4 shadow-sm" onclick="exportTableToCSV('transactions.csv')">
                        <i class="fas fa-file-excel me-2"></i> Export
                    </button>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="salesTable">
                        <thead class="table-header-custom">
                            <tr>
                                <th class="ps-4 py-3 text-secondary text-uppercase small">Date</th>
                                <th class="py-3 text-secondary text-uppercase small">Customer</th>
                                <th class="py-3 text-secondary text-uppercase small">Product</th>
                                <th class="py-3 text-secondary text-uppercase small">Qty</th>
                                <th class="py-3 text-secondary text-uppercase small">Price</th>
                                <th class="py-3 text-secondary text-uppercase small">Total</th>
                                <th class="text-end pe-4 py-3 text-secondary text-uppercase small">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="transactionTableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="addSaleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Add New Sale</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="addSaleForm">
                    <div class="mb-3">
                        <label>Date</label>
                        <input type="date" class="form-control" id="saleDate" required>
                    </div>
                    <div class="mb-3">
                        <label>Customer</label>
                        <input type="text" class="form-control" id="saleCustomer" required>
                    </div>
                    <div class="mb-3">
                        <label>Product</label>
                        <select class="form-select" id="saleProduct">
                            <option>Gaming Laptop</option>
                            <option>Mechanical Keyboard</option>
                            <option>Gaming Mouse</option>
                            <option>Monitor 144hz</option>
                            <option>Headset</option>
                            <option>RTX 6767</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label>Quantity</label>
                            <input type="number" class="form-control" id="saleQty" value="1" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label>Price (₱)</label>
                            <input type="number" class="form-control" id="salePrice" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100 rounded-pill py-2">Save Transaction</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="editSaleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-warning bg-opacity-10">
                <h5 class="modal-title fw-bold text-warning">Edit Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="editSaleForm">
                    <input type="hidden" id="editId">
                    <div class="mb-3">
                        <label>Date</label>
                        <input type="date" class="form-control" id="editDate" required>
                    </div>
                    <div class="mb-3">
                        <label>Customer</label>
                        <input type="text" class="form-control" id="editCustomer" required>
                    </div>
                    <div class="mb-3">
                        <label>Product</label>
                        <select class="form-select" id="editProduct">
                            <option>Gaming Laptop</option>
                            <option>Mechanical Keyboard</option>
                            <option>Gaming Mouse</option>
                            <option>Monitor 144hz</option>
                            <option>Headset</option>
                            <option>RTX 6767</option>
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label>Quantity</label>
                            <input type="number" class="form-control" id="editQty" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label>Price (₱)</label>
                            <input type="number" class="form-control" id="editPrice" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-warning w-100 rounded-pill fw-bold py-2">Update Changes</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="script.js"></script>

</body>
</html>