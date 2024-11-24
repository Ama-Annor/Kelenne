// Function to handle API calls
async function makeServiceRequest(action, data = {}) {
    try {
        const formData = new FormData();
        formData.append('action', action);

        Object.keys(data).forEach(key => {
            formData.append(key, data[key]);
        });

        const response = await fetch('../../actions/service_actions.php', {
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

// Modal functions for Add Service
function openAddServiceModal() {
    const modal = document.getElementById('addServiceModal');
    const form = document.getElementById('addServiceForm');

    // Reset form
    form.reset();
    loadCustomers();

    modal.style.display = 'block';
}

function closeAddServiceModal() {
    document.getElementById('addServiceModal').style.display = 'none';
}

// Modal functions for Edit Service
function openEditServiceModal(service) {
    const modal = document.getElementById('editServiceModal');
    const form = document.getElementById('editServiceForm');

    // Populate form with service data
    document.getElementById('editServiceId').value = service.service_id;
    document.getElementById('editServiceName').value = service.name;
    document.getElementById('editServiceDuration').value = service.duration;
    document.getElementById('editServicePrice').value = service.price;
    document.getElementById('editServiceDescription').value = service.description;
    document.getElementById('editServiceStatus').value = service.is_active;

    modal.style.display = 'block';
}

function closeEditServiceModal() {
    document.getElementById('editServiceModal').style.display = 'none';
}

function closeViewServiceModal() {
    document.getElementById('viewServiceModal').style.display = 'none';
}

// CRUD Operations
async function viewService(id) {
    try {
        const result = await makeServiceRequest('view', { service_id: id });
        const service = result.data;

        const detailsHtml = `
            <p><strong>Service ID:</strong> ${service.service_id}</p>
            <p><strong>Name:</strong> ${service.name}</p>
            <p><strong>Description:</strong> ${service.description}</p>
            <p><strong>Duration:</strong> ${service.duration} minutes</p>
            <p><strong>Price:</strong> ${parseFloat(service.price).toFixed(2)}</p>
            <p><strong>Status:</strong> ${service.is_active ? 'Active' : 'Inactive'}</p>
            
            ${service.fname ? `
                <h3>Customer Details</h3>
                <p><strong>Name:</strong> ${service.fname} ${service.lname}</p>
                <p><strong>Email:</strong> ${service.email}</p>
                <p><strong>Phone:</strong> ${service.phone}</p>
                <p><strong>Customer Status:</strong> ${service.customer_status}</p>
            ` : '<p>No customer associated with this service</p>'}
        `;

        document.getElementById('serviceDetails').innerHTML = detailsHtml;
        document.getElementById('viewServiceModal').style.display = 'block';
    } catch (error) {
        console.error('Error viewing service:', error);
    }
}

async function editService(id) {
    try {
        const result = await makeServiceRequest('view', { service_id: id });
        const service = result.data;
        openEditServiceModal(service);
    } catch (error) {
        console.error('Error loading service for edit:', error);
    }
}

async function deleteService(id) {
    if (confirm('Are you sure you want to delete this service?')) {
        try {
            await makeServiceRequest('delete', { service_id: id });
            const row = document.querySelector(`tr[data-service-id="${id}"]`);
            if (row) row.remove();
        } catch (error) {
            console.error('Error deleting service:', error);
        }
    }
}

// Populate customer dropdown when adding a service
async function loadCustomers() {
    try {
        const result = await makeServiceRequest('get_customers');
        const customerSelect = document.getElementById('addServiceCustomer');
        customerSelect.innerHTML = '<option value="">Select Customer</option>';

        result.customers.forEach(customer => {
            const option = document.createElement('option');
            option.value = customer.customer_id;
            option.textContent = `${customer.fname} ${customer.lname} (${customer.email})`;
            customerSelect.appendChild(option);
        });
    } catch (error) {
        console.error('Error loading customers:', error);
    }
}

// Form submission for Add Service
document.getElementById('addServiceForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = {
        name: document.getElementById('addServiceName').value,
        description: document.getElementById('addServiceDescription').value,
        duration: document.getElementById('addServiceDuration').value,
        price: document.getElementById('addServicePrice').value,
        is_active: document.getElementById('addServiceStatus').value,
        customer_id: document.getElementById('addServiceCustomer').value
    };

    try {
        await makeServiceRequest('add', formData);
        location.reload(); // Refresh the page to show updated data
    } catch (error) {
        console.error('Error saving service:', error);
    }
});

// Form submission for Edit Service
document.getElementById('editServiceForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const formData = {
        service_id: document.getElementById('editServiceId').value,
        name: document.getElementById('editServiceName').value,
        description: document.getElementById('editServiceDescription').value,
        duration: document.getElementById('editServiceDuration').value,
        price: document.getElementById('editServicePrice').value,
        is_active: document.getElementById('editServiceStatus').value
    };

    try {
        await makeServiceRequest('edit', formData);
        location.reload(); // Refresh the page to show updated data
    } catch (error) {
        console.error('Error updating service:', error);
    }
});

// Search functionality
function searchServices() {
    const searchTerm = document.getElementById('searchInput').value.toLowerCase();
    const rows = document.getElementById('servicesTableBody').getElementsByTagName('tr');

    Array.from(rows).forEach(row => {
        const text = Array.from(row.cells)
            .slice(0, -1) 
            .map(cell => cell.textContent.toLowerCase())
            .join(' ');

        row.style.display = text.includes(searchTerm) ? '' : 'none';
    });
}

// Add event listener for search input
document.getElementById('searchInput').addEventListener('input', debounce(searchServices, 300));

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
    const addServiceModal = document.getElementById('addServiceModal');
    const editServiceModal = document.getElementById('editServiceModal');
    const viewServiceModal = document.getElementById('viewServiceModal');

    if (event.target === addServiceModal) {
        closeAddServiceModal();
    }
    if (event.target === editServiceModal) {
        closeEditServiceModal();
    }
    if (event.target === viewServiceModal) {
        closeViewServiceModal();
    }
};