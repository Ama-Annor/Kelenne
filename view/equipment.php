<?php
// Include the database connection file
require '../db/database.php';

// Start the session
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

$response = ["success" => false, "message" => ""];

// Total Equipment
$result = $conn->query("SELECT COUNT(*) AS total_equipment FROM equipment");
$total_equipment = $result->fetch_assoc()['total_equipment'];

// Operational Equipment
$result = $conn->query("SELECT COUNT(*) AS operational_equipment FROM equipment WHERE status = 'Operational'");
$operational_equipment = $result->fetch_assoc()['operational_equipment'];

// Under Maintenance Equipment
$result = $conn->query("SELECT COUNT(*) AS maintenance_equipment FROM equipment WHERE status = 'Maintenance'");
$maintenance_equipment = $result->fetch_assoc()['maintenance_equipment'];

// Fetch Equipment
$sql = "SELECT 
    equipment_id, 
    name, 
    type, 
    status, 
    last_maintenance_date, 
    next_maintenance_date
FROM equipment";
$result = $conn->query($sql);

$equipment = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $equipment[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelenne Car Wash - Equipment Management</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/inventory.css">
    <style>
        /* Styles from the original equipment page */
        :root {
            --primary-blue: #1e40af;
            --secondary-blue: #3b82f6;
            --accent-silver: #e5e7eb;
            --text-dark: #1f2937;
            --white: #ffffff;
        }

        .equipment-stats {
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

        .stat-value {
            font-size: 1.8rem;
            font-weight: bold;
            color: var(--primary-blue);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }

        .status-operational { background: #DEF7EC; color: #03543F; }
        .status-maintenance { background: #FEF3C7; color: #92400E; }
        .status-repair { background: #FEE2E2; color: #991B1B; }

        /* Maintenance Schedule Section */
        .schedule-section {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .schedule-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .schedule-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .schedule-card {
            background: #f8fafc;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid var(--primary-blue);
        }

        .status-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }

        .status-operational { background: #DEF7EC; color: #03543F; }
        .status-maintenance { background: #FEF3C7; color: #92400E; }
        .status-repair { background: #FEE2E2; color: #991B1B; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <!-- Sidebar-->
    <div class="sidebar" id="sidebar">
        <div class="logo">
            <i class='bx bxs-car-wash'></i>
            <span>KELENNE</span>
        </div>
        <nav>
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
        </nav>
    </div>

    <div class="main-content">
        <!-- Equipment Statistics -->
        <div class="equipment-stats">
            <div class="stat-card">
                <h3>Total Equipment</h3>
                <div class="stat-value"><?php echo htmlspecialchars($total_equipment); ?></div>
            </div>
            <div class="stat-card">
                <h3>Operational Equipment</h3>
                <div class="stat-value"><?php echo htmlspecialchars($operational_equipment); ?></div>
            </div>
            <div class="stat-card">
                <h3>Under Maintenance</h3>
                <div class="stat-value"><?php echo htmlspecialchars($maintenance_equipment); ?></div>
            </div>
        </div>

        <?php
        // Get equipment under maintenance
        $sql = "SELECT equipment_id, name, Type, status, next_maintenance_date 
        FROM equipment 
        WHERE status = 'Maintenance' 
        AND DATE(next_maintenance_date) >= CURDATE()
        ORDER BY next_maintenance_date ASC
        LIMIT 2";
        $result = $conn->query($sql);
        $rows = $result->fetch_all(MYSQLI_ASSOC);

        $conn->close();

        // Helper function to format the date
        function formatMaintenanceDate($date) {
            $maintenance_date = date('Y-m-d', strtotime($date));
            $today = date('Y-m-d');
            $tomorrow = date('Y-m-d', strtotime('+1 day'));

            if ($maintenance_date === $today) {
                return 'Today';
            } elseif ($maintenance_date === $tomorrow) {
                return 'Tomorrow';
            } else {
                return date('Y-m-d', strtotime($date));
            }
        }
        ?>

        <!-- Maintenance Schedule Section -->
        <div class="schedule-section">
            <div class="schedule-header">
                <h2>This Week's Maintenance Schedule</h2>
            </div>
            <div class="schedule-grid">
                <?php if (empty($rows)): ?>
                    <div class="schedule-card">
                        <h3>No Maintenance Scheduled</h3>
                        <p>There are currently no equipment items under maintenance.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($rows as $item): ?>
                        <div class="schedule-card">
                            <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                            <p>Scheduled: <?php echo htmlspecialchars(formatMaintenanceDate($item['next_maintenance_date'])); ?></p>
                            <p>Type: <?php echo htmlspecialchars($item['Type']); ?></p>
                <?php echo htmlspecialchars($item['status']); ?>
            </span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="actions-bar">
            <div class="search-bar">
                <input type="text"
                       placeholder="Search equipment by name or type..."
                       id="searchInput"
                       oninput="filterEquipment(this.value)">
            </div>
            <button class="btn btn-primary" onclick="openAddModal()">
                <i class='bx bx-plus'></i> Add Equipment
            </button>
        </div>

        <div class="table-container">
            <table class="inventory-table">
                <thead>
                <tr>
                    <th>Equipment Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Last Maintenance</th>
                    <th>Next Maintenance</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="equipmentTableBody">
                <?php foreach ($equipment as $item): ?>
                    <tr data-equipment-id="<?php echo $item['equipment_id']; ?>">
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['type']); ?></td>
                        <td>
                            <span class="status-badge <?php
                            echo ($item['status'] == 'Operational') ? 'status-operational' :
                                (($item['status'] == 'Under Maintenance') ? 'status-maintenance' : 'status-repair');
                            ?>">
                                <?php echo htmlspecialchars($item['status']); ?>
                            </span>
                        </td>
                        <td><?php echo htmlspecialchars($item['last_maintenance_date'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($item['next_maintenance_date'] ?? 'N/A'); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon btn-view" onclick="viewEquipment(<?php echo $item['equipment_id']; ?>)">
                                    <i class='bx bx-show'></i>
                                </button>
                                <button class="btn-icon btn-edit" onclick="editEquipment(<?php echo $item['equipment_id']; ?>)">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteEquipment(<?php echo $item['equipment_id']; ?>)">
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

<!-- Add/Edit Equipment Modal -->
<div id="equipmentModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Add New Equipment</h2>
        <form id="equipmentForm">
            <div class="form-group">
                <label for="name">Equipment Name</label>
                <input type="text" id="name" required>
            </div>
            <div class="form-group">
                <label for="type">Type</label>
                <select id="type" required>
                    <option value="Cleaning Equipment">Cleaning Equipment</option>
                    <option value="Washing Equipment">Washing Equipment</option>
                    <option value="Drying Equipment">Drying Equipment</option>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" required>
                    <option value="Operational">Operational</option>
                    <option value="Maintenance">Under Maintenance</option>
                </select>
            </div>
            <div class="form-group">
                <label for="lastMaintenance">Last Maintenance Date</label>
                <input type="date" id="lastMaintenance">
            </div>
            <div class="form-group">
                <label for="nextMaintenance">Next Maintenance Date</label>
                <input type="date" id="nextMaintenance">
            </div>
            <button type="submit" class="btn btn-primary">Save Equipment</button>
        </form>
    </div>
</div>

<!-- View Equipment Details Modal -->
<div id="viewEquipmentModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeViewModal()">&times;</span>
        <h2>Equipment Details</h2>
        <div id="equipmentDetails"></div>
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
    async function makeRequest(action, data) {
        try {
            const formData = new FormData();
            formData.append('action', action);

            Object.keys(data).forEach(key => {
                formData.append(key, data[key]);
            });

            const response = await fetch('../actions/equipment_actions.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!result.success) {
                throw new Error(result.message);
            }

            return result;
        } catch (error) {
            console.error('Error:', error);
            alert(error.message);
            throw error;
        }
    }

    function filterEquipment(searchTerm) {
        const rows = document.getElementById('equipmentTableBody').getElementsByTagName('tr');
        searchTerm = searchTerm.toLowerCase().trim();

        for (let row of rows) {
            const name = row.cells[0].textContent.toLowerCase();
            const type = row.cells[1].textContent.toLowerCase();

            if (name.includes(searchTerm) || type.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }

        let hasVisibleRows = Array.from(rows).some(row => row.style.display !== 'none');

        let noResultsRow = document.getElementById('noResultsRow');
        if (!noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.id = 'noResultsRow';
            const td = document.createElement('td');
            td.colSpan = 6;
            td.style.textAlign = 'center';
            td.style.padding = '20px';
            td.textContent = 'No equipment found matching your search.';
            noResultsRow.appendChild(td);
        }

        if (!hasVisibleRows && searchTerm) {
            if (!document.getElementById('noResultsRow')) {
                document.getElementById('equipmentTableBody').appendChild(noResultsRow);
            }
            noResultsRow.style.display = '';
        } else if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }
    }

    function closeModal() {
        document.getElementById('equipmentModal').style.display = 'none';
    }

    function closeViewModal() {
        document.getElementById('viewEquipmentModal').style.display = 'none';
    }

    async function viewEquipment(id) {
        try {
            const result = await makeRequest('view', { equipment_id: id });
            const equipment = result.data;

            const detailsHtml = `
                <div class="equipment-details">
                    <p><strong>Equipment ID:</strong> ${equipment.equipment_id}</p>
                    <p><strong>Name:</strong> ${equipment.name}</p>
                    <p><strong>Type:</strong> ${equipment.type}</p>
                    <p><strong>Status:</strong> ${equipment.status}</p>
                    <p><strong>Last Maintenance:</strong> ${equipment.last_maintenance_date || 'N/A'}</p>
                    <p><strong>Next Maintenance:</strong> ${equipment.next_maintenance_date || 'N/A'}</p>
                </div>
            `;

            document.getElementById('equipmentDetails').innerHTML = detailsHtml;
            document.getElementById('viewEquipmentModal').style.display = 'block';
        } catch (error) {
            console.error('Error viewing equipment:', error);
        }
    }

    async function editEquipment(id) {
        try {
            const result = await makeRequest('view', { equipment_id: id });
            const equipment = result.data;

            document.getElementById('name').value = equipment.name;
            document.getElementById('type').value = equipment.type;
            document.getElementById('status').value = equipment.status;
            document.getElementById('lastMaintenance').value = equipment.last_maintenance_date;
            document.getElementById('nextMaintenance').value = equipment.next_maintenance_date;


            document.getElementById('modalTitle').textContent = 'Edit Equipment';
            document.getElementById('equipmentForm').dataset.equipmentId = id;
            document.getElementById('equipmentForm').dataset.mode = 'edit';

            document.getElementById('equipmentModal').style.display = 'block';
        } catch (error) {
            console.error('Error loading equipment for edit:', error);
        }
    }

    function openAddModal() {
        // Reset form
        document.getElementById('equipmentForm').reset();
        document.getElementById('modalTitle').textContent = 'Add New Equipment';
        document.getElementById('equipmentForm').dataset.mode = 'add';
        delete document.getElementById('equipmentForm').dataset.equipmentId;

        // Show modal
        document.getElementById('equipmentModal').style.display = 'block';
    }

    async function deleteEquipment(id) {
        if (confirm('Are you sure you want to delete this equipment?')) {
            try {
                await makeRequest('delete', { equipment_id: id });
                const row = document.querySelector(`tr[data-equipment-id="${id}"]`);
                if (row) row.remove();

                // Optional: Update equipment counts
                location.reload();
            } catch (error) {
                console.error('Error deleting equipment:', error);
            }
        }
    }

    document.getElementById('equipmentForm').addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = {
            name: document.getElementById('name').value,
            type: document.getElementById('type').value,
            status: document.getElementById('status').value,
            last_maintenance_date: document.getElementById('lastMaintenance').value,
            next_maintenance_date: document.getElementById('nextMaintenance').value
        };

        const mode = this.dataset.mode || 'add';

        try {
            if (mode === 'edit') {
                formData.equipment_id = this.dataset.equipmentId;
                await makeRequest('edit', formData);
            } else {
                await makeRequest('add', formData);
            }

            location.reload();
        } catch (error) {
            console.error('Error saving equipment:', error);
        }
    });

    window.onclick = function(event) {
        if (event.target == document.getElementById('equipmentModal')) {
            closeModal();
        }
        if (event.target == document.getElementById('viewEquipmentModal')) {
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