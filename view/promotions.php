<?php
// Include the database connection file
require '../db/database.php';

// Start the session
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['role']) || !isset($_SESSION['user_id']) || $_SESSION['role'] == 'employee') {
    header("Location: login.html");
    exit();
}

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$response = ["success" => false, "message" => ""];

// Total Promotions
$result = $conn->query("SELECT COUNT(*) AS total_promotions FROM promotions");
$total_promotions = $result->fetch_assoc()['total_promotions'];

// Active Promotions
$result = $conn->query("SELECT COUNT(*) AS active_promotions FROM promotions WHERE is_active = 1");
$active_promotions = $result->fetch_assoc()['active_promotions'];

// Total Rewards Issued (sum of usage counts)
$result = $conn->query("SELECT SUM(usage_count) AS total_rewards FROM promotions");
$total_rewards = $result->fetch_assoc()['total_rewards'] ?? 0;

// Fetch Promotions
$sql = "SELECT promotion_id, name, type, discount_percentage, valid_until, is_active, usage_count
        FROM promotions";
$result = $conn->query($sql);

// Store promotions in an array
$promotions = [];
while ($row = $result->fetch_assoc()) {
    $promotions[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/inventory.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
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

        .main-content {
            flex: 1;
            background: #f0f2f5;
            padding: 20px;
        }

        .promotion-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: var(--text-dark);
            font-size: 0.9rem;
            margin-bottom: 10px;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-blue);
        }

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

        .table-container {
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .promotion-table {
            width: 100%;
            border-collapse: collapse;
        }

        .promotion-table th,
        .promotion-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--accent-silver);
        }

        .promotion-table th {
            background: #f8fafc;
            color: var(--text-dark);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }

        .status-active {
            background: #DEF7EC;
            color: #03543F;
        }

        .status-expired {
            background: #FDE8E8;
            color: #9B1C1C;
        }

        .status-scheduled {
            background: #E1EFFE;
            color: #1E429F;
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

        .btn-view { background: #10B981; color: white; }
        .btn-edit { background: #F59E0B; color: white; }
        .btn-delete { background: #EF4444; color: white; }

    </style>
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <i class='bx bxs-car-wash'></i>
            <span>KELENNE</span>
        </div>
        <nav>
            <?php if ($_SESSION['role'] == 'customer'): ?>
                <a href="admin/customerdashboard.php" class="menu-item">
                    <i class='bx bx-home'></i>
                    <span>Dashboard</span>
                </a>
                <a href="bookNow.php" class="menu-item">
                    <i class='bx bx-calendar'></i>
                    <span>Book a Service</span>
                </a>
                <a href="appointments.php" class="menu-item">
                    <i class='bx bx-calendar'></i>
                    <span>Appointments</span>
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
            <?php elseif ($_SESSION['role'] == 'admin'): ?>
                <a href="admin/dashboard.php" class="menu-item">
                    <i class='bx bx-home'></i>
                    <span>Dashboard</span>
                </a>
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
            <?php endif; ?>
        </nav>
    </div>

    <div class="main-content">
        <!-- Promotion Statistics -->
        <div class="promotion-stats">
            <div class="stat-card">
                <h3>Promotions Total</h3>
                <div class="stat-value"><?php echo htmlspecialchars($total_promotions); ?></div>
            </div>
            <div class="stat-card">
                <h3>Active Promotions</h3>
                <div class="stat-value"><?php echo htmlspecialchars($active_promotions); ?></div>
            </div>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <div class="stat-card">
                    <h3>Total Rewards Issued</h3>
                    <div class="stat-value"><?php echo htmlspecialchars($total_rewards); ?></div>
                </div>
            <?php endif; ?>
        </div>
        <div class="actions-bar">
            <div class="search-bar">
                <input type="text" placeholder="Search promotions..." id="searchInput">
                <button class="btn btn-secondary" onclick="searchPromotions()">
                    <i class='bx bx-search'></i> Search
                </button>
            </div>
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <button class="btn btn-primary" onclick="openAddModal()">
                    <i class='bx bx-plus'></i> Add New Promotion
                </button>
            <?php endif; ?>
        </div>

        <div class="table-container">
            <table class="promotion-table">
                <thead>
                <tr>
                    <th>Promotion Name</th>
                    <th>Type</th>
                    <th>Discount</th>
                    <th>Valid Until</th>
                    <th>Status</th>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <th>Usage Count</th>
                        <th>Actions</th>
                    <?php endif; ?>
                </tr>
                </thead>
                <tbody id="promotionTableBody">
                <?php foreach ($promotions as $promotion): ?>
                    <tr  data-service-id="<?php echo $promotion['promotion_id']; ?>">
                        <td><?php echo htmlspecialchars($promotion['name']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['type']); ?></td>
                        <td><?php echo htmlspecialchars($promotion['discount_percentage'])."%"; ?></td>
                        <td><?php echo htmlspecialchars($promotion['valid_until']); ?></td>
                        <td><span class="service-status <?php echo $promotion['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                    <?php echo $promotion['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span></td>
                        <?php if ($_SESSION['role'] == 'admin'): ?>
                            <td><?php echo htmlspecialchars($promotion['usage_count']); ?></td>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-icon btn-view" onclick="viewPromotion(<?php echo $promotion['promotion_id']; ?>)">
                                        <i class='bx bx-show'></i>
                                    </button>
                                    <button class="btn-icon btn-edit" onclick="editPromotion(<?php echo $promotion['promotion_id']; ?>)">
                                        <i class='bx bx-edit'></i>
                                    </button>
                                    <button class="btn-icon btn-delete" onclick="deletePromotion(<?php echo $promotion['promotion_id']; ?>)">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add/Edit Promotion Modal -->
<div id="promotionModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Add New Promotion</h2>
        <form id="promotionForm">
            <div class="form-group">
                <label for="promotionName">Promotion Name</label>
                <input type="text" id="promotionName" required>
            </div>
            <div class="form-group">
                <label for="promotionType">Type</label>
                <select id="promotionType" required>
                    <option value="Percentage Discount">Percentage Discount</option>
                    <option value="Fixed Amount">Fixed Amount</option>
                    <option value="Buy One Get One">Buy One Get One</option>
                </select>
            </div>
            <div class="form-group">
                <label for="discountValue">Discount Value</label>
                <input type="number" id="discountValue" required>
            </div>
            <div class="form-group">
                <label for="validUntil">Valid Until</label>
                <input type="date" id="validUntil" required>
            </div>
            <input type="number" id="usageCount" name="usageCount" placeholder="Usage Count" min="0">
            <select id="isActive" name="isActive">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
            <button type="submit" class="btn btn-primary">Save Promotion</button>
        </form>
    </div>
</div>

<script>
    // Modal functions
    function openAddModal() {
        document.getElementById('promotionModal').style.display = 'block';
        document.getElementById('modalTitle').textContent = 'Add New Promotion';
        document.getElementById('promotionForm').reset();
        document.getElementById('promotionForm').dataset.mode = 'add';
    }

    function closeModal() {
        document.getElementById('promotionModal').style.display = 'none';
    }

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

    // CRUD Operations
    function viewPromotion(id) {
        fetch('../actions/promotion_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=view_promotion&id=${id}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const promotion = data.promotion;
                    alert(`
Promotion Details:
Name: ${promotion.name}
Type: ${promotion.type}
Discount: ${promotion.discount_percentage}%
Valid Until: ${promotion.valid_until}
Usage Count: ${promotion.usage_count}
                `);
                } else {
                    alert('Error viewing promotion: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching promotion details.');
            });
    }

    function editPromotion(id) {
        fetch('../actions/promotion_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=view_promotion&id=${id}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const promotion = data.promotion;
                    document.getElementById('promotionModal').style.display = 'block';
                    document.getElementById('modalTitle').textContent = 'Edit Promotion';
                    document.getElementById('promotionName').value = promotion.name;
                    document.getElementById('promotionType').value = promotion.type;
                    document.getElementById('discountValue').value = promotion.discount_percentage;
                    document.getElementById('validUntil').value = promotion.valid_until;
                    document.getElementById('usageCount').value = promotion.usage_count;
                    document.getElementById('isActive').value = promotion.is_active;

                    document.getElementById('promotionForm').dataset.mode = 'edit';
                    document.getElementById('promotionForm').dataset.id = id;
                } else {
                    alert('Error fetching promotion details: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while fetching promotion details.');
            });
    }

    function deletePromotion(id) {
        if (confirm('Are you sure you want to delete this promotion?')) {
            fetch('../actions/promotion_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete_promotion&id=${id}`
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error deleting promotion: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while deleting the promotion.');
                });
        }
    }

    function searchPromotions() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.getElementById('promotionTableBody').getElementsByTagName('tr');

        for (let row of rows) {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        }
    }

    // Form submission
    document.getElementById('promotionForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const mode = this.dataset.mode;

        const formData = new FormData();
        formData.append('action', mode === 'edit' ? 'edit_promotion' : 'add_promotion');
        formData.append('promotionName', document.getElementById('promotionName').value);
        formData.append('promotionType', document.getElementById('promotionType').value);
        formData.append('discountValue', document.getElementById('discountValue').value);
        formData.append('validUntil', document.getElementById('validUntil').value);
        formData.append('usageCount', document.getElementById('usageCount').value);
        formData.append('isActive', document.getElementById('isActive').value);

        if (mode === 'edit') {
            formData.append('id', this.dataset.id);
        }

        fetch('../actions/promotion_actions.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the promotion.');
            });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        if (event.target == document.getElementById('promotionModal')) {
            closeModal();
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

