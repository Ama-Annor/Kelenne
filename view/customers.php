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

// Total Customers
$result = $conn->query("SELECT COUNT(*) AS total_customers FROM customers");
$total_customers = $result->fetch_assoc()['total_customers'];

// VIP Customers
$result = $conn->query("SELECT COUNT(*) AS vip_customers FROM customers WHERE status = 'vip'");
$vip_customers = $result->fetch_assoc()['vip_customers'];

// This Month's New Customers
$result = $conn->query("
    SELECT COUNT(*) AS this_month_new_users 
    FROM users 
    WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) 
    AND user_type = 'customer' AND YEAR(created_at) = YEAR(CURRENT_DATE())
");
$this_month_new_users = $result->fetch_assoc()['this_month_new_users'];

// Fetch customers
$sql = "SELECT customers.customer_id, users.fname, users.lname, users.email, users.phone, customers.status
        FROM customers
        INNER JOIN users ON customers.user_id = users.user_id";
$result = $conn->query($sql);

$customers = array();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Get total visits and last visit date
        $customer_id = $row['customer_id'];
        $visit_sql = "SELECT COUNT(*) AS total_visits, MAX(created_at) AS last_visit_date
                      FROM services
                      WHERE customer_id = $customer_id";
        $visit_result = $conn->query($visit_sql);
        $visit_data = $visit_result->fetch_assoc();

        // Combine data
        $row['total_visits'] = $visit_data['total_visits'];
        $row['last_visit_date'] = $visit_data['last_visit_date'];

        $customers[] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelenne Car Wash - Customer Management</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/inventory.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
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

        .customer-stats {
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

        .customer-badge {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
        }

        .badge-vip {
            background: #FEF3C7;
            color: #92400E;
        }

        .badge-regular {
            background: #E0E7FF;
            color: #3730A3;
        }

        .search-bar {
            position: relative;
            flex: 1;
            max-width: 500px;
        }

        .search-bar input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.2s;
        }

        .search-bar input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px rgba(30, 64, 175, 0.1);
        }

        tr.hidden {
            display: none;
        }

        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: var(--white);
            margin: 5% auto;
            padding: 20px;
            border-radius: 10px;
            width: 80%;
            max-width: 600px;
        }

        .close {
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--accent-silver);
            border-radius: 4px;
        }
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
        </nav>
    </div>

    <div class="main-content">
        <!-- Customer Statistics -->
        <div class="customer-stats">
            <div class="stat-card">
                <h3>Total Customers</h3>
                <div class="stat-value"><?php echo htmlspecialchars($total_customers); ?></div>
            </div>
            <div class="stat-card">
                <h3>VIP Customers</h3>
                <div class="stat-value"><?php echo htmlspecialchars($vip_customers); ?></div>
            </div>
            <div class="stat-card">
                <h3>This Month's New</h3>
                <div class="stat-value"><?php echo htmlspecialchars($this_month_new_users); ?></div>
            </div>
        </div>

        <div class="actions-bar">
            <div class="search-bar">
                <input type="text"
                       placeholder="Search customers by name, email, or phone..."
                       id="searchInput"
                       oninput="filterCustomers(this.value)">
            </div>
            <button class="btn btn-primary">
                <i class='bx bx-plus'></i> Search
            </button>
        </div>

        <div class="table-container">
            <table class="inventory-table">
                <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Total Visits</th>
                    <th>Last Visit</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody id="customerTableBody">
                <?php foreach ($customers as $customer): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($customer['fname'] . ' ' . $customer['lname']); ?></td>
                        <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                        <td><?php echo htmlspecialchars($customer['email']); ?></td>
                        <td>
                            <?php
                            if ($customer['status'] == 'vip') {
                                echo '<span class="customer-badge badge-vip">VIP</span>';
                            } else {
                                echo '<span class="customer-badge badge-regular">Regular</span>';
                            }
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($customer['total_visits']); ?></td>
                        <td><?php echo htmlspecialchars($customer['last_visit_date'] ?? 'N/A'); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn-icon btn-view" onclick="viewCustomer(<?php echo $customer['customer_id']; ?>)">
                                    <i class='bx bx-show'></i>
                                </button>
                                <button class="btn-icon btn-edit" onclick="editCustomer(<?php echo $customer['customer_id']; ?>)">
                                    <i class='bx bx-edit'></i>
                                </button>
                                <button class="btn-icon btn-delete" onclick="deleteCustomer(<?php echo $customer['customer_id']; ?>)">
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

<!-- Add/Edit Customer Modal -->
<div id="customerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeModal()">&times;</span>
        <h2 id="modalTitle">Add New Customer</h2>
        <form id="customerForm">
            <div class="form-group">
                <label for="customerName">Full Name</label>
                <input type="text" id="customerName" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" required>
            </div>
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" required>
            </div>
            <div class="form-group">
                <label for="status">Customer Status</label>
                <select id="status" required>
                    <option value="regular">Regular</option>
                    <option value="vip">VIP</option>
                </select>
            </div>
            <div class="form-group">
                <label for="vehicleMake">Vehicle Make</label>
                <input type="text" id="vehicleMake">
            </div>
            <div class="form-group">
                <label for="vehicleModel">Vehicle Model</label>
                <input type="text" id="vehicleModel">
            </div>
            <div class="form-group">
                <label for="vehicleYear">Vehicle Year</label>
                <input type="number" id="vehicleYear">
            </div>
            <div class="form-group">
                <label for="vehicleLicensePlate">License Plate</label>
                <input type="text" id="vehicleLicensePlate">
            </div>
            <button type="submit" class="btn btn-primary">Save Customer</button>
        </form>
    </div>
</div>

<!-- View Customer Details Modal -->
<div id="viewCustomerModal" class="modal">
    <div class="modal-content">
        <span class="close" onclick="closeViewModal()">&times;</span>
        <h2>Customer Details</h2>
        <div id="customerDetails">
            <!-- Will be populated by JavaScript -->
        </div>
    </div>
</div>

<script>
    function filterCustomers(searchTerm) {
        const rows = document.getElementById('customerTableBody').getElementsByTagName('tr');
        searchTerm = searchTerm.toLowerCase().trim();

        for (let row of rows) {
            const customerName = row.cells[0].textContent.toLowerCase();
            const email = row.cells[2].textContent.toLowerCase();
            const phone = row.cells[1].textContent.toLowerCase();

            if (customerName.includes(searchTerm) ||
                email.includes(searchTerm) ||
                phone.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        }

        let hasVisibleRows = false;
        for (let row of rows) {
            if (row.style.display !== 'none') {
                hasVisibleRows = true;
                break;
            }
        }

        let noResultsRow = document.getElementById('noResultsRow');
        if (!noResultsRow) {
            noResultsRow = document.createElement('tr');
            noResultsRow.id = 'noResultsRow';
            const td = document.createElement('td');
            td.colSpan = 7; // Span all columns
            td.style.textAlign = 'center';
            td.style.padding = '20px';
            td.textContent = 'No customers found matching your search.';
            noResultsRow.appendChild(td);
        }

        if (!hasVisibleRows && searchTerm) {
            // Only show "no results" if there's a search term
            if (!document.getElementById('noResultsRow')) {
                document.getElementById('customerTableBody').appendChild(noResultsRow);
            }
            noResultsRow.style.display = '';
        } else if (noResultsRow) {
            noResultsRow.style.display = 'none';
        }
    }

    // Optional: Add debouncing to improve performance
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

    // Use debounced version of the filter function for better performance
    const debouncedFilter = debounce(filterCustomers, 300);

    // Update the search input event listener
    document.getElementById('searchInput').addEventListener('input', function(e) {
        debouncedFilter(e.target.value);
    });

</script>

    <script>

        // Function to handle API calls
        async function makeRequest(action, data) {
            try {
                const formData = new FormData();
                formData.append('action', action);

                // Add all data properties to formData
                Object.keys(data).forEach(key => {
                    formData.append(key, data[key]);
                });

                const response = await fetch('../actions/customer_actions.php', {
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
        

        function closeModal() {
            document.getElementById('customerModal').style.display = 'none';
        }

        function closeViewModal() {
            document.getElementById('viewCustomerModal').style.display = 'none';
        }

        // CRUD Operations
        async function viewCustomer(id) {
            try {
                const result = await makeRequest('view', { customer_id: id });
                const customer = result.data;

                const detailsHtml = `
            <div class="customer-details">
                <p><strong>Customer ID:</strong> ${customer.customer_id}</p>
                <p><strong>Name:</strong> ${customer.fname} ${customer.lname}</p>
                <p><strong>Phone:</strong> ${customer.phone}</p>
                <p><strong>Email:</strong> ${customer.email}</p>
                <p><strong>Status:</strong> ${customer.status.toUpperCase()}</p>
                <p><strong>Total Visits:</strong> ${customer.total_visits}</p>
                <p><strong>Last Visit:</strong> ${customer.last_visit || 'N/A'}</p>
                ${customer.vehicle ? `
                    <h3>Vehicle Details</h3>
                    <p><strong>Make:</strong> ${customer.vehicle.make || 'N/A'}</p>
                    <p><strong>Model:</strong> ${customer.vehicle.model || 'N/A'}</p>
                    <p><strong>Year:</strong> ${customer.vehicle.year || 'N/A'}</p>
                    <p><strong>License Plate:</strong> ${customer.vehicle.license_plate || 'N/A'}</p>
                ` : ''}
            </div>
        `;

                document.getElementById('customerDetails').innerHTML = detailsHtml;
                document.getElementById('viewCustomerModal').style.display = 'block';
            } catch (error) {
                console.error('Error viewing customer:', error);
            }
        }

        async function editCustomer(id) {
            try {
                const result = await makeRequest('view', { customer_id: id });
                const customer = result.data;

                // Existing customer fields
                document.getElementById('customerName').value = `${customer.fname} ${customer.lname}`;
                document.getElementById('phone').value = customer.phone;
                document.getElementById('email').value = customer.email;
                document.getElementById('status').value = customer.status;

                // Vehicle-specific fields
                const vehicle = customer.vehicle || {};
                document.getElementById('vehicleMake').value = vehicle.make || '';
                document.getElementById('vehicleModel').value = vehicle.model || '';
                document.getElementById('vehicleYear').value = vehicle.year || '';
                document.getElementById('vehicleLicensePlate').value = vehicle.license_plate || '';

                // Update form for edit mode
                document.getElementById('modalTitle').textContent = 'Edit Customer';
                document.getElementById('customerForm').dataset.customerId = id;
                document.getElementById('customerForm').dataset.mode = 'edit';

                document.getElementById('customerModal').style.display = 'block';
            } catch (error) {
                console.error('Error loading customer for edit:', error);
            }
        }


        async function deleteCustomer(id) {
            if (confirm('Are you sure you want to delete this customer?')) {
                try {
                    await makeRequest('delete', { customer_id: id });
                    // Remove the row from the table
                    const row = document.querySelector(`tr[data-customer-id="${id}"]`);
                    if (row) row.remove();

                    // Update customer counts
                    updateCustomerCounts();
                } catch (error) {
                    console.error('Error deleting customer:', error);
                }
            }
        }

        // Form submission
        document.getElementById('customerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = {
                fname: document.getElementById('customerName').value.split(' ')[0],
                lname: document.getElementById('customerName').value.split(' ').slice(1).join(' '),
                email: document.getElementById('email').value,
                phone: document.getElementById('phone').value,
                status: document.getElementById('status').value,
                vehicle_make: document.getElementById('vehicleMake').value,
                vehicle_model: document.getElementById('vehicleModel').value,
                vehicle_year: document.getElementById('vehicleYear').value,
                vehicle_license_plate: document.getElementById('vehicleLicensePlate').value
            };

            const mode = this.dataset.mode || 'add';

            try {
                if (mode === 'edit') {
                    formData.customer_id = this.dataset.customerId;
                    await makeRequest('edit', formData);
                } else {
                    await makeRequest('add', formData);
                }

                location.reload();
            } catch (error) {
                console.error('Error saving customer:', error);
            }
        });

        async function updateCustomerCounts() {
            // This function would make an AJAX call to get updated counts
            // For now, we'll just reload the page
            location.reload();
        }

        // Close modals when clicking outside
        window.onclick = function(event) {
            if (event.target == document.getElementById('customerModal')) {
                closeModal();
            }
            if (event.target == document.getElementById('viewCustomerModal')) {
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
