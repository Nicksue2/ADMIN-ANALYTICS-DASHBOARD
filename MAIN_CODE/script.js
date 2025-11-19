let monthlyChartInstance = null;
let topProductsChartInstance = null;
let globalData = []; 


document.addEventListener('DOMContentLoaded', () => {
    fetchData();
    initTheme();
});


const themeSwitch = document.getElementById('darkModeSwitch');

function initTheme() {
    const savedTheme = localStorage.getItem('theme') || 'light';
    document.documentElement.setAttribute('data-theme', savedTheme);
    themeSwitch.checked = savedTheme === 'dark';
    updateChartsTheme(savedTheme);
}

themeSwitch.addEventListener('change', (e) => {
    const theme = e.target.checked ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
    updateChartsTheme(theme);
});

function updateChartsTheme(theme) {
    const textColor = theme === 'dark' ? '#e0e0e0' : '#666';
    const gridColor = theme === 'dark' ? '#333' : '#eee';
    
    if(monthlyChartInstance) {
        monthlyChartInstance.options.scales.x.ticks.color = textColor;
        monthlyChartInstance.options.scales.y.ticks.color = textColor;
        monthlyChartInstance.options.scales.y.grid.color = gridColor;
        monthlyChartInstance.update();
    }
    if(topProductsChartInstance) {
        topProductsChartInstance.options.scales.x.ticks.color = textColor;
        topProductsChartInstance.options.scales.y.ticks.color = textColor;
        topProductsChartInstance.options.scales.y.grid.color = gridColor;
        topProductsChartInstance.update();
    }
}

// Search Logic
document.getElementById('searchInput').addEventListener('keyup', function() {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll('#transactionTableBody tr');
    rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        row.style.display = text.includes(value) ? '' : 'none';
    });
});

// Export to CSV Functionality
function exportTableToCSV(filename) {
    const table = document.getElementById("salesTable");
    let csv = [];
    const rows = table.querySelectorAll("tr");
    
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll("td, th");
        for (let j = 0; j < cols.length - 1; j++)
            row.push('"' + cols[j].innerText + '"');
        csv.push(row.join(","));
    }

    const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
    const downloadLink = document.createElement("a");
    downloadLink.download = filename;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
}

// Fetch Data from API
async function fetchData() {
    try {
        const response = await fetch('api.php');
        const data = await response.json();
        globalData = data.transactions;
        renderDashboard(data);
    } catch (error) {
        console.error('Error fetching data:', error);
    }
}

// Create New Transaction
document.getElementById('addSaleForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const newSale = {
        date: document.getElementById('saleDate').value,
        customer: document.getElementById('saleCustomer').value,
        product: document.getElementById('saleProduct').value,
        quantity: document.getElementById('saleQty').value,
        price: document.getElementById('salePrice').value
    };

    await fetch('api.php?action=create', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(newSale)
    });

    bootstrap.Modal.getInstance(document.getElementById('addSaleModal')).hide();
    document.getElementById('addSaleForm').reset();
    fetchData();
    Swal.fire('Success!', 'New transaction added successfully.', 'success');
});

// Modal Handling for Edit
function openEditModal(id) {
    const sale = globalData.find(item => item.id == id);
    if(sale) {
        document.getElementById('editId').value = sale.id;
        document.getElementById('editDate').value = sale.transaction_date;
        document.getElementById('editCustomer').value = sale.customer;
        document.getElementById('editProduct').value = sale.product;
        document.getElementById('editQty').value = sale.quantity;
        document.getElementById('editPrice').value = sale.price;
        new bootstrap.Modal(document.getElementById('editSaleModal')).show();
    }
}

// Update Existing Transaction
document.getElementById('editSaleForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const updatedSale = {
        id: document.getElementById('editId').value,
        date: document.getElementById('editDate').value,
        customer: document.getElementById('editCustomer').value,
        product: document.getElementById('editProduct').value,
        quantity: document.getElementById('editQty').value,
        price: document.getElementById('editPrice').value
    };

    await fetch('api.php?action=update', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify(updatedSale)
    });

    bootstrap.Modal.getInstance(document.getElementById('editSaleModal')).hide();
    fetchData();
    Swal.fire('Updated!', 'Transaction details updated.', 'success');
});

// Delete Transaction
async function deleteSale(id) {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    });

    if (result.isConfirmed) {
        await fetch('api.php?action=delete', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ id: id })
        });
        fetchData();
        Swal.fire('Deleted!', 'Record has been deleted.', 'success');
    }
}

// Render Charts and Tables
function renderDashboard(data) {
    document.getElementById('totalSalesDisplay').innerText = '₱' + parseFloat(data.total_sales || 0).toLocaleString();

    // Reset Charts
    if(monthlyChartInstance) monthlyChartInstance.destroy();
    if(topProductsChartInstance) topProductsChartInstance.destroy();
    
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textColor = isDark ? '#e0e0e0' : '#666';
    const gridColor = isDark ? '#333' : '#f0f0f0';
    Chart.defaults.color = textColor;
    Chart.defaults.font.family = "'Poppins', sans-serif";

    // 1. Monthly Trends (Line Chart)
    const ctx1 = document.getElementById('monthlyChart').getContext('2d');
    let gradient = ctx1.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(78, 115, 223, 0.5)');
    gradient.addColorStop(1, 'rgba(78, 115, 223, 0.0)');

    monthlyChartInstance = new Chart(ctx1, {
        type: 'line',
        data: {
            labels: data.monthly_sales.map(d => d.month),
            datasets: [{
                label: 'Revenue',
                data: data.monthly_sales.map(d => d.monthly_total),
                borderColor: '#4e73df',
                borderWidth: 3,
                tension: 0.4,
                fill: true,
                backgroundColor: gradient,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#4e73df'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor } },
                y: { grid: { color: gridColor, borderDash: [5, 5] }, ticks: { color: textColor }, beginAtZero: true }
            }
        }
    });

    // 2. Top Products (Bar Chart)
    const ctx2 = document.getElementById('topProductsChart');
    topProductsChartInstance = new Chart(ctx2, {
        type: 'bar',
        data: {
            labels: data.top_products.map(d => d.product),
            datasets: [{
                label: 'Qty',
                data: data.top_products.map(d => d.total_qty),
                backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796'],
                borderRadius: 4,
                barThickness: 30
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { color: textColor } },
                y: { grid: { color: gridColor }, ticks: { color: textColor }, beginAtZero: true }
            }
        }
    });

    // 3. Transaction Table
    const tableBody = document.getElementById('transactionTableBody');
    tableBody.innerHTML = '';
    
    data.transactions.forEach(t => {
        const total = (t.quantity * t.price).toLocaleString();
        const row = `<tr>
            <td class="ps-4">${t.transaction_date}</td>
            <td class="fw-bold">${t.customer}</td>
            <td>${t.product}</td>
            <td><span class="badge bg-secondary bg-opacity-10 text-secondary border">${t.quantity}</span></td>
            <td>₱${parseFloat(t.price).toLocaleString()}</td>
            <td class="fw-bold text-primary">₱${total}</td>
            <td class="text-end pe-4">
                <button class="btn btn-sm btn-light text-warning border me-1" onclick="openEditModal(${t.id})"><i class="fas fa-pen"></i></button>
                <button class="btn btn-sm btn-light text-danger border" onclick="deleteSale(${t.id})"><i class="fas fa-trash"></i></button>
            </td>
        </tr>`;
        tableBody.innerHTML += row;
    });
}