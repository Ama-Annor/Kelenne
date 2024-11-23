<?php
// Include the database connection file
require '../db/database.php';

// Start the session and set error reporting
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || !isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.html");
    exit();
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Fetch inventory with prepared statement
function getInventoryItems($conn) {
    $sql = "SELECT item_id, name, category, quantity, unit_price, supplier, status 
            FROM inventory_items 
            ORDER BY name ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Get inventory items
$inventory = getInventoryItems($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelenne Car Wash - Inventory Management</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <style>
        /* Using your existing CSS variables and base styles */
        :root {
            --primary-blue: #1e40af;
            --secondary-blue: #3b82f6;
            --accent-silver: #e5e7eb;
            --text-dark: #1f2937;
            --white: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Reusing your sidebar styles */
        .sidebar {
            width: 250px;
            background: var(--primary-blue);
            color: var(--white);
            padding: 20px;
        }

        .menu-item {
            display: flex;
            align-items: center;
            padding: 12px;
            color: var(--white);
            text-decoration: none;
            margin-bottom: 5px;
            border-radius: 5px;
            transition: background 0.3s;
        }

        .menu-item i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            background: #f0f2f5;
            padding: 20px;
        }

        /* Search and Add Section */
        .actions-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-bar {
            display: flex;
            gap: 10px;
            flex: 1;
            max-width: 500px;
        }

        .search-bar input {
            flex: 1;
            padding: 10px;
            border: 1px solid var(--accent-silver);
            border-radius: 5px;
            font-size: 1rem;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background: var(--primary-blue);
            color: var(--white);
        }

        .btn-secondary {
            background: var(--secondary-blue);
            color: var(--white);
        }

        /* Table Styles */
        .table-container {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
        }

        .inventory-table th,
        .inventory-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--accent-silver);
        }

        .inventory-table th {
            background: #f8fafc;
            color: var(--text-dark);
        }

        .action-buttons {
            display: flex;
            gap: 5px;
        }

        .btn-icon {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 1.2rem;
        }

        .btn-view {
            background: #10B981;
            color: white;
        }

        .btn-edit {
            background: #F59E0B;
            color: white;
        }

        .btn-delete {
            background: #EF4444;
            color: white;
        }

        /* Modal base styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            overflow-y: auto;
            padding: 20px;
        }

        /* Modal content container */
        .modal-content {
            background-color: #fff;
            margin: 20px auto;
            padding: 20px;
            border-radius: 8px;
            width: 100%;
            max-width: 600px;
            position: relative;
            max-height: 90vh; /* Maximum height of 90% of the viewport height */
            overflow-y: auto; /* Enable scrolling for content */
            display: flex;
            flex-direction: column;
        }

        /* Form styles */
        .form-group {
            margin-bottom: 15px;
        }

        /* Make inputs and text areas take full width */
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            margin-top: 4px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        /* Close button positioning */
        .close {
            position: sticky;
            top: 0;
            right: 0;
            float: right;
            font-size: 24px;
            cursor: pointer;
            color: #666;
            z-index: 1;
        }

        /* Customer details in view modal */
        .customer-details {
            padding: 10px 0;
        }

        .customer-details p {
            margin: 8px 0;
        }

        /* Media query for smaller screens */
        @media screen and (max-height: 600px) {
            .modal-content {
                margin: 10px auto;
                max-height: 85vh;
            }
        }
    </style>

</head>
<body>
<div class="dashboard-container">
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <i class='bx bxs-car-wash'></i>
            <span>KELENNE</span>
        </div>
        <nav>
            <a href="appointments.php" class="menu-item">
                <i class='bx bx-calendar'></i>
                <span>Appointments</span>
            </a>
            <a href="employee.php" class="menu-item">
                <i class='bx bx-user'></i>
                <span>Employees</span>
            </a>
            <a href="revenue-analytics.php" class="menu-item">
                <i class='bx bx-line-chart'></i>
                <span>Analytics</span>
            </a>
            <a href="inventory.php" class="menu-item">
                <i class='bx bx-box'></i>
                <span>Inventory</span>
            </a>
            <a href="customers.php" class="menu-item">
                <i class='bx bx-group'></i>
                <span>Customers</span>
            </a>
            <a href="services.php" class="menu-item">
                <i class='bx bx-list-ul'></i>
                <span>Services</span>
            </a>
            <a href="equipment.php" class="menu-item">
                <i class='bx bx-wrench'></i>
                <span>Equipment</span>
            </a>
            <a href="promotions.php" class="menu-item">
                <i class='bx bx-gift'></i>
                <span>Promotions & Rewards</span>
            </a>
            <a href="profile.php" class="menu-item">
                <i class='bx bx-user'></i>
                <span>Profile Settings</span>
            </a>
            <a href="../actions/logout.php" class="menu-item" onclick="event.preventDefault(); logoutUser();">
                <i class='bx bx-exit'></i>Logout
            </a>
        </nav>
    </div>

    <div class="main-content">
        <div class="actions-bar">
            <div class="search-bar">
                <input type="text" placeholder="Search inventory..." id="searchInput">
                <button class="btn btn-secondary" onclick="searchInventory()">
                    <i class='bx bx-search'></i> Search
                </button>
            </div>
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class='bx bx-plus'></i> Add New Item
            </button>
        </div>

        <div class="table-container">
            <table class="inventory-table">
                <thead>
                <tr>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Supplier</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="inventoryTableBody">
                <?php foreach ($inventory as $item): ?>
                    <tr data-item-id="<?php echo htmlspecialchars($item['item_id']); ?>">
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td>$<?php echo htmlspecialchars(number_format($item['unit_price'], 2)); ?></td>
                        <td><?php echo htmlspecialchars($item['supplier']); ?></td>
                        <td><?php echo htmlspecialchars($item['status']); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon btn-view" onclick="viewItem(<?php echo $item['item_id']; ?>)">
                                    <i class='bx bx-show'></i>
                                </button>
                                <button class="btn-icon btn-edit" onclick="editItem(<?php echo $item['item_id']; ?>)">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteItem(<?php echo $item['item_id']; ?>)">
                                    <i class='bx bx-trash'></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Inventory Modal -->
<div id="inventoryModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Add New Item</h2>
        <form id="inventoryForm">
            <input type="hidden" id="itemId">
            <div class="form-group">
                <label for="itemName">Item Name</label>
                <input type="text" id="itemName" required>
            </div>
            <div class="form-group">
                <label for="category">Category</label>
                <select id="category" required>
                    <option value="">Select Category</option>
                    <option value="Cleaning Supplies">Cleaning Supplies</option>
                    <option value="Chemicals">Chemicals</option>
                    <option value="Equipment">Equipment</option>
                    <option value="Tools">Tools</option>
                </select>
            </div>
            <div class="form-group">
                <label for="quantity">Quantity</label>
                <input type="number" id="quantity" min="0" required>
            </div>
            <div class="form-group">
                <label for="unitPrice">Unit Price ($)</label>
                <input type="number" id="unitPrice" min="0" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="supplier">Supplier</label>
                <input type="text" id="supplier" required>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" required>
                    <option value="In Stock">In Stock</option>
                    <option value="Out of Stock">Out of Stock</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Save Item</button>
        </form>
    </div>
</div>

<!-- View Item Details Modal -->
<div id="viewItemModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeViewModal()">&times;</span>
        <h2>Item Details</h2>
        <div id="itemDetails" class="inventory-details">
            <!-- Will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
    function logoutUser() {
        // Clear sessions (usually handled on server side)
        fetch('../actions/logout.php', {
            method: 'POST'
        }).then(response => {
            if (response.ok) {
                // Redirect to login page after logout
                window.location.href = 'login.html';
            }
        });
    }

    // Function to handle API calls
    async function makeRequest(action, data = {}) {
        try {
            const formData = new FormData();
            formData.append('action', action);

            Object.keys(data).forEach(key => {
                formData.append(key, data[key]);
            });

            const response = await fetch('../actions/inventory_actions.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message || 'An error occurred');
            }

            return result;
        } catch (error) {
            console.error('Error:', error);
            alert(error.message);
            throw error;
        }
    }

    // Modal functions
    function openAddModal() {
        document.getElementById('modalTitle').textContent = 'Add New Item';
        document.getElementById('inventoryForm').reset();
        document.getElementById('inventoryForm').dataset.mode = 'add';
        document.getElementById('inventoryModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('inventoryModal').style.display = 'none';
    }

    function closeViewModal() {
        document.getElementById('viewItemModal').style.display = 'none';
    }

    // CRUD Operations
    async function viewItem(id) {
        try {
            const result = await makeRequest('view', { item_id: id });
            const item = result.data;

            const detailsHtml = `
            <p><strong>Item ID:</strong> ${item.item_id}</p>
            <p><strong>Name:</strong> ${item.name}</p>
            <p><strong>Category:</strong> ${item.category}</p>
            <p><strong>Quantity:</strong> ${item.quantity}</p>
            <p><strong>Unit Price:</strong> $${parseFloat(item.unit_price).toFixed(2)}</p>
            <p><strong>Supplier:</strong> ${item.supplier}</p>
            <p><strong>Status:</strong> ${item.status}</p>
            <p><strong>Last Updated:</strong> ${item.last_updated || 'N/A'}</p>
        `;

            document.getElementById('itemDetails').innerHTML = detailsHtml;
            document.getElementById('viewItemModal').style.display = 'block';
        } catch (error) {
            console.error('Error viewing item:', error);
        }
    }

    async function editItem(id) {
        try {
            const result = await makeRequest('view', { item_id: id });
            const item = result.data;

            document.getElementById('itemId').value = item.item_id;
            document.getElementById('itemName').value = item.name;
            document.getElementById('category').value = item.category;
            document.getElementById('quantity').value = item.quantity;
            document.getElementById('unitPrice').value = item.unit_price;
            document.getElementById('supplier').value = item.supplier;
            document.getElementById('status').value = item.status;

            document.getElementById('modalTitle').textContent = 'Edit Item';
            document.getElementById('inventoryForm').dataset.mode = 'edit';
            document.getElementById('inventoryModal').style.display = 'block';
        } catch (error) {
            console.error('Error loading item for edit:', error);
        }
    }

    async function deleteItem(id) {
        if (confirm('Are you sure you want to delete this item?')) {
            try {
                await makeRequest('delete', { item_id: id });
                const row = document.querySelector(`tr[data-item-id="${id}"]`);
                if (row) row.remove();
            } catch (error) {
                console.error('Error deleting item:', error);
            }
        }
    }

    // Form submission
    document.getElementById('inventoryForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = {
            name: document.getElementById('itemName').value,
            category: document.getElementById('category').value,
            quantity: document.getElementById('quantity').value,
            unit_price: document.getElementById('unitPrice').value,
            supplier: document.getElementById('supplier').value,
            status: document.getElementById('status').value
        };

        const mode = this.dataset.mode || 'add';

        try {
            if (mode === 'edit') {
                formData.item_id = document.getElementById('itemId').value;
                await makeRequest('edit', formData);
            } else {
                await makeRequest('add', formData);
            }
            location.reload();
        } catch (error) {
            console.error('Error saving item:', error);
        }
    });

    // Search functionality
    function searchInventory() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.getElementById('inventoryTableBody').getElementsByTagName('tr');

        Array.from(rows).forEach(row => {
            const text = Array.from(row.cells)
                .slice(0, -1) // Exclude the actions column
                .map(cell => cell.textContent.toLowerCase())
                .join(' ');

            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    }

    // Add event listener for search input
    document.getElementById('searchInput').addEventListener('input', debounce(searchInventory, 300));

    // Utility function for debouncing
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Close modals when clicking outside
    window.onclick = function(event) {
        if (event.target === document.getElementById('inventoryModal')) {
            closeModal();
        }
        if (event.target === document.getElementById('viewItemModal')) {
            closeViewModal();
        }
    }
</script>
<script type="text/javascript">
    (function() {
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }

        window.onpageshow = function(event) {
            if (event.persisted) {
                window.location.reload();
            }
        };
    })();
</script>
</body>
</html>
