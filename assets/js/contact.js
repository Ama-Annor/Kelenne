//Initialize AOS (Animate on Scroll)
document.addEventListener('DOMContentLoaded', function() {
    //Initialize AOS library
    AOS.init({
        duration: 1000,    
        once: true,        
        offset: 100        
    });

    //Form handling
    const contactForm = document.getElementById('contactForm');
    const inputs = contactForm.querySelectorAll('input, textarea');

    //Add floating label effect
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });

        //Remove focus effect if input is empty
        input.addEventListener('blur', function() {
            if (this.value === '') {
                this.parentElement.classList.remove('focused');
            }
        });

        //Check if input has value on page load
        if (input.value !== '') {
            input.parentElement.classList.add('focused');
        }
    });

    //Form submission handling
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault(); 

        const formData = new FormData(this);
        
        let isValid = true;
        inputs.forEach(input => {
            if (!input.value.trim()) {
                isValid = false;
                input.classList.add('error');
            } else {
                input.classList.remove('error');
            }
        });

        if (isValid) {
            alert('Thank you for your message! We will get back to you soon.');
            this.reset();
            
            inputs.forEach(input => {
                input.parentElement.classList.remove('focused');
            });
        } else {
            alert('Please fill in all required fields.');
        }
    });

    inputs.forEach(input => {
        input.addEventListener('input', function() {
            if (this.value.trim()) {
                this.classList.remove('error');
            }
        });
    });
});