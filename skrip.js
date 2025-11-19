// --- MODAL HANDLING ---
function openModal(id) {
    document.getElementById(id).style.display = 'flex';
}
function closeModal(id) {
    document.getElementById(id).style.display = 'none';
}
window.onclick = function(e) {
    if (e.target.classList.contains('modal-overlay')) {
        e.target.style.display = 'none';
    }
}

// --- DARK MODE ---
const toggle = document.getElementById('darkModeSwitch');
if (localStorage.getItem('theme') === 'dark') {
    document.documentElement.setAttribute('data-theme', 'dark');
    toggle.checked = true;
}
toggle.addEventListener('change', (e) => {
    const theme = e.target.checked ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', theme);
    localStorage.setItem('theme', theme);
});

// --- SEARCH ---
document.getElementById('searchInput').addEventListener('keyup', function() {
    const val = this.value.toLowerCase();
    document.querySelectorAll('#salesTable tbody tr').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(val) ? '' : 'none';
    });
});

// --- EXPORT CSV ---
function exportTableToCSV(filename) {
    let csv = [];
    const rows = document.querySelectorAll("table tr");
    for (let i = 0; i < rows.length; i++) {
        let row = [], cols = rows[i].querySelectorAll("td, th");
        for (let j = 0; j < cols.length - 1; j++) row.push('"' + cols[j].innerText + '"');
        csv.push(row.join(","));
    }
    const blob = new Blob([csv.join("\n")], {type: "text/csv"});
    const link = document.createElement("a");
    link.href = URL.createObjectURL(blob);
    link.download = filename;
    document.body.appendChild(link);
    link.click();
}

// --- EDIT FORM FILL ---
function openEditModal(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('editDate').value = data.transaction_date;
    document.getElementById('editCustomer').value = data.customer;
    document.getElementById('editProduct').value = data.product;
    document.getElementById('editQty').value = data.quantity;
    document.getElementById('editPrice').value = data.price;
    openModal('editSaleModal');
}