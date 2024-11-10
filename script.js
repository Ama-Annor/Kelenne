// Smooth Scroll for Navigation Links
document.querySelectorAll('nav a').forEach(link => {
    link.addEventListener('click', function(e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});

// Button Hover Effects
document.querySelectorAll('.btn').forEach(button => {
    button.addEventListener('mouseenter', () => {
        button.style.transform = 'scale(1.1)';
        button.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.2)';
    });
    button.addEventListener('mouseleave', () => {
        button.style.transform = 'scale(1)';
        button.style.boxShadow = 'none';
    });
});

// Reveal Sections on Scroll
const revealSections = document.querySelectorAll('.reveal');
const revealOnScroll = () => {
    const triggerBottom = window.innerHeight / 1.2;
    revealSections.forEach(section => {
        const sectionTop = section.getBoundingClientRect().top;
        if (sectionTop < triggerBottom) {
            section.classList.add('active');
        } else {
            section.classList.remove('active');
        }
    });
};
window.addEventListener('scroll', revealOnScroll);
revealOnScroll();  // Run on page load to check initial visibility

// Car Wash Service Animation
const services = document.querySelectorAll('.service-card');
services.forEach((service, index) => {
    service.style.transitionDelay = `${index * 0.1}s`;  // Stagger animation
    service.addEventListener('mouseenter', () => {
        service.style.transform = 'scale(1.05)';
    });
    service.addEventListener('mouseleave', () => {
        service.style.transform = 'scale(1)';
    });
});

// FAQ Section Toggle with Animation
document.querySelectorAll('.faq-item').forEach(item => {
    item.querySelector('.faq-question').addEventListener('click', () => {
        const answer = item.querySelector('.faq-answer');
        if (answer.style.maxHeight) {
            answer.style.maxHeight = null;
            item.classList.remove('open');
        } else {
            answer.style.maxHeight = answer.scrollHeight + 'px';
            item.classList.add('open');
        }
    });
});

// Add Loading Spinner on Form Submit
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', (e) => {
        e.preventDefault();
        const spinner = document.createElement('div');
        spinner.classList.add('loading-spinner');
        form.appendChild(spinner);
        setTimeout(() => {
            spinner.remove();
            alert('Form submitted successfully!');
            form.reset();
        }, 2000);  // Simulate loading time
    });
});

// Back to Top Button
const backToTop = document.querySelector('.back-to-top');
window.addEventListener('scroll', () => {
    if (window.scrollY > 300) {
        backToTop.classList.add('visible');
    } else {
        backToTop.classList.remove('visible');
    }
});
backToTop.addEventListener('click', () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
});
