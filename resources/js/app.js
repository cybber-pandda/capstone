require('./bootstrap');


// Add scroll event listener for animated elements
document.addEventListener('DOMContentLoaded', function() {
    const animatedElements = document.querySelectorAll('.animated-element');
    const handleScroll = () => {
        animatedElements.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            if (elementPosition < window.innerHeight - 100) {
                element.classList.add('visible');
            }
        });
    };
    window.addEventListener('scroll', handleScroll);
    handleScroll();
});

// Sticky Navbar background change on scroll
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.classList.add('scrolled');
    } else {
        navbar.classList.remove('scrolled');
    }
});


// Add animation for testimonials section
document.addEventListener('DOMContentLoaded', function() {
    const testimonials = document.querySelectorAll('.testimonial');
    const handleScrollTestimonials = () => {
        testimonials.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            if (elementPosition < window.innerHeight - 100) {
                element.classList.add('visible');
            }
        });
    };
    window.addEventListener('scroll', handleScrollTestimonials);
    handleScrollTestimonials();
});

// Event card animation
document.addEventListener('DOMContentLoaded', function() {
    const eventCards = document.querySelectorAll('.event-card');
    const handleScrollEvents = () => {
        eventCards.forEach(element => {
            const elementPosition = element.getBoundingClientRect().top;
            if (elementPosition < window.innerHeight - 100) {
                element.classList.add('visible');
            }
        });
    };
    window.addEventListener('scroll', handleScrollEvents);
    handleScrollEvents();
});
