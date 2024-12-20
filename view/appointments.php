<?php
// Include the database connection file and start session
require_once '../db/database.php';
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

// Initialize response array
$response = ["success" => false, "message" => ""];

// Check for any response messages from actions
if (isset($_SESSION['response'])) {
    $response = $_SESSION['response'];
    unset($_SESSION['response']);
}

if ($_SESSION['role'] == 'admin') {
    $sql = "SELECT 
            a.appointment_id,
            a.appointment_date,
            a.status,
            u.fname,
            u.lname
        FROM appointments a
        INNER JOIN customers c ON a.customer_id = c.customer_id
        INNER JOIN users u ON c.user_id = u.user_id
        ORDER BY a.appointment_date DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $appointments = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
        }
        $stmt->close();
    }
}
else if ($_SESSION['role'] == 'customer') {
    $sql = "SELECT 
            a.appointment_id,
            a.appointment_date,
            a.status,
            u.fname,
            u.lname
        FROM appointments a
        INNER JOIN customers c ON a.customer_id = c.customer_id
        INNER JOIN users u ON c.user_id = u.user_id
        WHERE c.user_id = {$_SESSION['user_id']}
        ORDER BY a.appointment_date DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->execute();
        $result = $stmt->get_result();
        $appointments = [];

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $appointments[] = $row;
            }
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelenne Car Wash - Appointments</title>
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/appointments.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Include your existing CSS variables and base styles */
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
            transition: all 0.3s ease;
        }

        .main-content {
            flex: 1;
            background: #f0f2f5;
            padding: 20px;
        }

        /* Appointments specific styles */
        .appointments-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-bar input,
        .search-bar select {
            padding: 8px;
            border: 1px solid var(--accent-silver);
            border-radius: 5px;
        }

        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .btn-primary {
            background: var(--primary-blue);
            color: var(--white);
        }

        .btn-primary:hover {
            background: var(--secondary-blue);
        }

        .calendar-view {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .appointments-list {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .appointments-table {
            width: 100%;
            border-collapse: collapse;
        }

        .appointments-table th,
        .appointments-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--accent-silver);
        }

        .appointments-table th {
            background: #f8fafc;
            color: var(--text-dark);
        }

        .action-buttons {
            display: flex;
            gap: 8px;
        }

        .action-buttons button {
            padding: 4px 8px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .view-btn {
            background: var(--secondary-blue);
            color: white;
        }

        .edit-btn {
            background: #10b981;
            color: white;
        }

        .delete-btn {
            background: #ef4444;
            color: white;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .modal-content {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 500px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--accent-silver);
            border-radius: 5px;
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
        <div class="appointments-header">
            <h2>Appointments Management</h2>
            <?php if ($_SESSION['role'] == 'customer'): ?>
                <button class="btn btn-primary" onclick="openModal('add')">
                    <i class='bx bx-plus'></i> New Appointment
                </button>
            <?php endif; ?>
        </div>

        <?php if ($response["message"]): ?>
            <div class="alert <?php echo $response["success"] ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo htmlspecialchars($response["message"]); ?>
            </div>
        <?php endif; ?>

        <div class="search-bar">
            <input type="date" id="dateFilter" placeholder="Filter by date">
            <button class="btn btn-primary" onclick="filterAppointments()">Search</button>
        </div>

        <div class="appointments-list">
            <h3>Appointments</h3>
            <table class="appointments-table" id="appointmentsTable">
                <thead>
                <tr>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <th>Customer Name</th>
                    <?php endif; ?>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <tr data-appointment-id="<?php echo $appointment['appointment_id']; ?>">
                            <?php if ($_SESSION['role'] == 'admin'): ?>
                                <td><?php echo htmlspecialchars($appointment['fname'] . ' ' . $appointment['lname']); ?></td>
                            <?php endif; ?>
                            <td><?php echo date('M d, Y h:i A', strtotime($appointment['appointment_date'])); ?></td>
                            <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                            <td>
                                <?php if ($_SESSION['role'] == 'customer'): ?>
                                    <?php if ($appointment['status'] !== 'Confirmed'): ?>
                                        <div class="action-buttons">
                                            <button class="btn-icon btn-delete" onclick="deleteAppointment(<?php echo $appointment['appointment_id']; ?>)">
                                                <i class='bx bx-trash'></i>
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                <?php elseif ($_SESSION['role'] == 'admin'): ?>
                                    <div class="action-buttons">
                                        <button class="btn-icon btn-edit" onclick="openEditModal(<?php echo $appointment['appointment_id']; ?>)">
                                            <i class='bx bx-edit'></i>
                                        </button>
                                        <button class="btn-icon btn-delete" onclick="deleteAppointment(<?php echo $appointment['appointment_id']; ?>)">
                                            <i class='bx bx-trash'></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Appointment Modal -->
<div id="appointmentModal" class="modal">
    <div class="modal-content">
        <h3>Add New Appointment</h3>
        <form id="appointmentForm" action="../actions/appointment_actions.php" method="POST">
            <input type="hidden" name="action" value="add">

            <div class="form-group">
                <label>Date & Time</label>
                <input type="datetime-local" name="appointment_date" id="appointmentDate" required>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Book Appointment</button>
                <button type="button" class="btn" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Appointment Status Modal -->
<div id="editAppointmentModal" class="modal">
    <div class="modal-content">
        <h3>Update Appointment Status</h3>
        <form id="editAppointmentForm" action="../actions/appointment_actions.php" method="POST">
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="appointment_id" id="editAppointmentId">

            <div class="form-group">
                <label>Appointment Status</label>
                <select name="status" id="editAppointmentStatus" required>
                    <option value="Scheduled">Scheduled</option>
                    <option value="Confirmed">Confirmed</option>
                    <option value="Completed">Completed</option>
                    <option value="Cancelled">Cancelled</option>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" class="btn btn-primary">Update Status</button>
                <button type="button" class="btn" onclick="closeEditModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    function openModal(mode, appointmentId = null) {
        const modal = document.getElementById('appointmentModal');
        const form = document.getElementById('appointmentForm');
        const modalTitle = document.getElementById('modalTitle');

        modal.style.display = 'flex';

        if (mode === 'edit' && appointmentId) {
            modalTitle.textContent = 'Edit Appointment';
            document.getElementById('formAction').value = 'edit';
            document.getElementById('appointmentId').value = appointmentId;

            // Fetch and populate appointment data
            fetch(`../actions/appointment_actions.php?action=get&id=${appointmentId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const appointment = data.appointment;
                        document.getElementById('customerEmail').value = appointment.email;
                        document.getElementById('serviceId').value = appointment.service_id;
                        document.getElementById('appointmentDate').value = appointment.appointment_date.slice(0, 16); 
                        document.getElementById('appointmentStatus').value = appointment.status;

                        // Make email field readonly in edit mode
                        document.getElementById('customerEmail').readOnly = true;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading appointment details');
                });
        } else {
            // Reset form for new appointment
            modalTitle.textContent = 'Add New Appointment';
            form.reset();
            document.getElementById('formAction').value = 'add';
            document.getElementById('appointmentId').value = '';
            document.getElementById('customerEmail').readOnly = false;
        }
    }

    function closeModal() {
        const modal = document.getElementById('appointmentModal');
        modal.style.display = 'none';
    }

    // View appointment details
    function viewAppointment(appointmentId) {
        fetch(`../actions/appointment_actions.php?action=get&id=${appointmentId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const appointment = data.appointment;
                    const formattedDate = new Date(appointment.appointment_date).toLocaleString();
                    const detailsHtml = `
                    <div class="modal-content" style="max-width: 400px;">
                        <h3>Appointment Details</h3>
                        <div style="margin: 15px 0;">
                            <p><strong>Customer Email:</strong> ${appointment.email}</p>
                            <p><strong>Date & Time:</strong> ${formattedDate}</p>
                            <p><strong>Status:</strong> ${appointment.status}</p>
                        </div>
                        <button class="btn btn-primary" onclick="closeModal()">Close</button>
                    </div>
                `;

                    const modal = document.getElementById('appointmentModal');
                    modal.innerHTML = detailsHtml;
                    modal.style.display = 'flex';
                } else {
                    alert('Could not load appointment details');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error loading appointment details');
            });
    }

    // Delete appointment
    function deleteAppointment(appointmentId) {
        if (confirm('Are you sure you want to delete this appointment?')) {
            const formData = new FormData();
            formData.append('action', 'delete');
            formData.append('appointment_id', appointmentId);

            fetch('../actions/appointment_actions.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Remove the row from the table
                        const row = document.querySelector(`tr[data-appointment-id="${appointmentId}"]`);
                        if (row) row.remove();
                        alert('Appointment deleted successfully');
                    } else {
                        alert(data.message || 'Error deleting appointment');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error deleting appointment');
                });
        }
    }

    function openEditModal(appointmentId) {
        const modal = document.getElementById('editAppointmentModal');
        const appointmentIdInput = document.getElementById('editAppointmentId');
        const statusSelect = document.getElementById('editAppointmentStatus');

        // Find the current status of the appointment row
        const row = document.querySelector(`tr[data-appointment-id="${appointmentId}"]`);
        const currentStatus = row.cells[2].textContent.trim();

        modal.style.display = 'flex';
        appointmentIdInput.value = appointmentId;
        statusSelect.value = currentStatus;
    }

    function closeEditModal() {
        const modal = document.getElementById('editAppointmentModal');
        modal.style.display = 'none';
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

    // Add event listener for edit form submission
    document.getElementById('editAppointmentForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('../actions/appointment_actions.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeEditModal();
                    window.location.reload();
                } else {
                    alert(data.message || 'Error updating appointment status');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating appointment status');
            });
    });

    // Handle form submission
    document.getElementById('appointmentForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('../actions/appointment_actions.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeModal();
                    // Reload the page to show updated data
                    window.location.reload();
                } else {
                    alert(data.message || 'Error processing appointment');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error processing appointment');
            });
    });

    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('appointmentModal');
        if (event.target === modal) {
            closeModal();
        }
    }

    // Enhanced filter function
    function filterAppointments() {
        const dateFilter = document.getElementById('dateFilter').value;
        const rows = document.querySelectorAll('#appointmentsTable tbody tr');

        rows.forEach(row => {
            const appointmentDateCell = row.querySelector('td:nth-child(<?php echo $_SESSION['role'] == 'admin' ? 2 : 1; ?>)');
            const appointmentDate = appointmentDateCell.textContent.trim();
            const formattedRowDate = new Date(appointmentDate).toISOString().split('T')[0];

            // Show the row if the date filter is empty or matches the row's date
            const dateMatch = !dateFilter || formattedRowDate === dateFilter;

            // Always show the row if date filter matches
            row.style.display = dateMatch ? '' : 'none';
        });
    }

    // Add event listener for date filter
    document.getElementById('dateFilter').addEventListener('change', filterAppointments);


    // Initialize flatpickr with more specific options
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date picker for filter
        flatpickr("#dateFilter", {
            dateFormat: "Y-m-d",
            allowInput: true,
            altFormat: "F j, Y",
            altInput: true,
            onChange: function(selectedDates, dateStr) {
                filterAppointments();
            }
        });

        // Initialize date picker for appointment form
        flatpickr("#appointmentDate", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            time_24hr: false,
            altFormat: "F j, Y at h:i K",
            altInput: true
        });
    });

    // Add event listeners for real-time filtering
    document.getElementById('dateFilter').addEventListener('change', filterAppointments);
    document.getElementById('customerSearch').addEventListener('input', filterAppointments);
    document.getElementById('serviceFilter').addEventListener('change', filterAppointments);

    // Initialize flatpickr with more specific options
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize date picker for filter
        flatpickr("#dateFilter", {
            dateFormat: "Y-m-d",
            allowInput: true,
            altFormat: "F j, Y",
            altInput: true
        });

        // Initialize date picker for appointment form
        flatpickr("#appointmentDate", {
            enableTime: true,
            dateFormat: "Y-m-d H:i",
            minDate: "today",
            time_24hr: false,
            altFormat: "F j, Y at h:i K",
            altInput: true
        });
    });
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