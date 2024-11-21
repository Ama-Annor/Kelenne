// Add this to your existing script section in customers.html
document.addEventListener('DOMContentLoaded', function() {
    const bookNowBtn = document.getElementById('booknow');
    const modal = document.getElementById('book-now-modal');
    const closeBtn = modal.querySelector('.close-button');

    // Prevent default link behavior and show modal
    bookNowBtn.addEventListener('click', function(e) {
        e.preventDefault(); // Prevent page navigation
        modal.style.display = 'block';
    });

    // Close modal when close button is clicked
    closeBtn.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    // Close modal when clicking outside the modal
    window.addEventListener('click', function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    });
});