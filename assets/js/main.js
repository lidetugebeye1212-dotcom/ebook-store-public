// Main JavaScript File

// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    // Create mobile menu button
    const navbar = document.querySelector('.navbar');
    const navLinks = document.querySelector('.nav-links');
    
    if (window.innerWidth <= 768) {
        const menuToggle = document.createElement('div');
        menuToggle.className = 'menu-toggle';
        menuToggle.innerHTML = '<i class="fas fa-bars"></i>';
        navbar.insertBefore(menuToggle, navbar.firstChild);
        
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            this.innerHTML = navLinks.classList.contains('active') ? 
                '<i class="fas fa-times"></i>' : '<i class="fas fa-bars"></i>';
        });
    }
    
    // Initialize tooltips
    initTooltips();
    
    // Initialize form validation
    initFormValidation();
    
    // Initialize search suggestions
    initSearchSuggestions();
    
    // Initialize Ethiopian book carousel
    initEthiopianCarousel();
});

// Tooltips
function initTooltips() {
    const tooltips = document.querySelectorAll('[data-tooltip]');
    tooltips.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltipText = e.target.dataset.tooltip;
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = tooltipText;
    tooltip.style.position = 'absolute';
    tooltip.style.background = '#333';
    tooltip.style.color = '#fff';
    tooltip.style.padding = '5px 10px';
    tooltip.style.borderRadius = '4px';
    tooltip.style.fontSize = '12px';
    tooltip.style.zIndex = '1000';
    
    const rect = e.target.getBoundingClientRect();
    tooltip.style.top = rect.bottom + window.scrollY + 5 + 'px';
    tooltip.style.left = rect.left + window.scrollX + 'px';
    
    document.body.appendChild(tooltip);
    e.target._tooltip = tooltip;
}

function hideTooltip(e) {
    if (e.target._tooltip) {
        e.target._tooltip.remove();
        e.target._tooltip = null;
    }
}

// Form Validation
function initFormValidation() {
    const forms = document.querySelectorAll('form[data-validate]');
    forms.forEach(form => {
        form.addEventListener('submit', validateForm);
    });
}

function validateForm(e) {
    const form = e.target;
    let isValid = true;
    
    const requiredFields = form.querySelectorAll('[required]');
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            isValid = false;
            showFieldError(field, 'This field is required');
        } else {
            removeFieldError(field);
        }
        
        // Email validation
        if (field.type === 'email' && field.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value)) {
                isValid = false;
                showFieldError(field, 'Please enter a valid email address');
            }
        }
        
        // Password validation
        if (field.type === 'password' && field.value) {
            if (field.value.length < 6) {
                isValid = false;
                showFieldError(field, 'Password must be at least 6 characters');
            }
        }
    });
    
    // Password match validation
    const password = form.querySelector('#password');
    const confirmPassword = form.querySelector('#confirm_password');
    if (password && confirmPassword && password.value !== confirmPassword.value) {
        isValid = false;
        showFieldError(confirmPassword, 'Passwords do not match');
    }
    
    if (!isValid) {
        e.preventDefault();
    }
}

function showFieldError(field, message) {
    removeFieldError(field);
    
    field.classList.add('error');
    const errorDiv = document.createElement('div');
    errorDiv.className = 'field-error';
    errorDiv.textContent = message;
    errorDiv.style.color = '#d63031';
    errorDiv.style.fontSize = '12px';
    errorDiv.style.marginTop = '5px';
    
    field.parentNode.insertBefore(errorDiv, field.nextSibling);
}

function removeFieldError(field) {
    field.classList.remove('error');
    const errorDiv = field.parentNode.querySelector('.field-error');
    if (errorDiv) {
        errorDiv.remove();
    }
}

// Search Suggestions
function initSearchSuggestions() {
    const searchInput = document.querySelector('.search-input, .search-input-large');
    if (!searchInput) return;
    
    let debounceTimer;
    searchInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        
        if (query.length < 2) {
            removeSuggestions();
            return;
        }
        
        debounceTimer = setTimeout(() => {
            fetchSuggestions(query);
        }, 300);
    });
    
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.search-container')) {
            removeSuggestions();
        }
    });
}

function fetchSuggestions(query) {
    // Simulated suggestions - In production, make AJAX call to server
    const suggestions = [
        'Ethiopian History',
        'Amharic Fiction',
        'Fikir Eske Mekabir',
        'Dertogada',
        'Ethiopian Culture'
    ].filter(item => item.toLowerCase().includes(query.toLowerCase()));
    
    displaySuggestions(suggestions);
}

function displaySuggestions(suggestions) {
    removeSuggestions();
    
    const searchContainer = document.querySelector('.search-container');
    if (!searchContainer || suggestions.length === 0) return;
    
    const suggestionsDiv = document.createElement('div');
    suggestionsDiv.className = 'search-suggestions';
    suggestionsDiv.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        margin-top: 5px;
        z-index: 1000;
    `;
    
    suggestions.forEach(suggestion => {
        const item = document.createElement('div');
        item.className = 'suggestion-item';
        item.style.cssText = `
            padding: 10px 15px;
            cursor: pointer;
            border-bottom: 1px solid #f1f1f1;
        `;
        item.innerHTML = `<i class="fas fa-search" style="margin-right: 10px; color: #6c5ce7;"></i>${suggestion}`;
        
        item.addEventListener('click', function() {
            const input = searchContainer.querySelector('input');
            input.value = suggestion;
            input.form.submit();
        });
        
        item.addEventListener('mouseenter', function() {
            this.style.background = '#f8f9fa';
        });
        
        item.addEventListener('mouseleave', function() {
            this.style.background = 'white';
        });
        
        suggestionsDiv.appendChild(item);
    });
    
    searchContainer.style.position = 'relative';
    searchContainer.appendChild(suggestionsDiv);
}

function removeSuggestions() {
    const existingSuggestions = document.querySelector('.search-suggestions');
    if (existingSuggestions) {
        existingSuggestions.remove();
    }
}

// Ethiopian Book Carousel
function initEthiopianCarousel() {
    const carousel = document.querySelector('.ethiopian-carousel');
    if (!carousel) return;
    
    let currentSlide = 0;
    const slides = carousel.querySelectorAll('.carousel-item');
    const prevButton = carousel.querySelector('.carousel-prev');
    const nextButton = carousel.querySelector('.carousel-next');
    
    function showSlide(index) {
        slides.forEach((slide, i) => {
            slide.style.display = i === index ? 'block' : 'none';
        });
    }
    
    if (prevButton && nextButton && slides.length > 0) {
        showSlide(currentSlide);
        
        prevButton.addEventListener('click', function() {
            currentSlide = (currentSlide - 1 + slides.length) % slides.length;
            showSlide(currentSlide);
        });
        
        nextButton.addEventListener('click', function() {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        });
        
        // Auto advance every 5 seconds
        setInterval(() => {
            currentSlide = (currentSlide + 1) % slides.length;
            showSlide(currentSlide);
        }, 5000);
    }
}

// Add to Cart Animation
function addToCartAnimation(button) {
    const cartIcon = document.querySelector('.nav-links a[href*="cart"] i');
    
    button.classList.add('adding');
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    
    setTimeout(() => {
        button.classList.remove('adding');
        button.classList.add('added');
        button.innerHTML = '<i class="fas fa-check"></i> Added to Cart';
        
        if (cartIcon) {
            cartIcon.classList.add('shake');
            setTimeout(() => {
                cartIcon.classList.remove('shake');
            }, 500);
        }
        
        setTimeout(() => {
            button.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
            button.classList.remove('added');
        }, 2000);
    }, 1000);
}

// Review Modal Functions
function openReviewModal() {
    const modal = document.getElementById('reviewModal');
    if (modal) {
        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

function closeReviewModal() {
    const modal = document.getElementById('reviewModal');
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('reviewModal');
    if (event.target === modal) {
        closeReviewModal();
    }
});

// Smooth Scroll
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Back to Top Button
window.addEventListener('scroll', function() {
    const backToTop = document.getElementById('back-to-top');
    if (!backToTop) {
        createBackToTopButton();
    }
    
    if (window.scrollY > 300) {
        document.getElementById('back-to-top').style.display = 'flex';
    } else {
        document.getElementById('back-to-top').style.display = 'none';
    }
});

function createBackToTopButton() {
    const button = document.createElement('button');
    button.id = 'back-to-top';
    button.innerHTML = '<i class="fas fa-arrow-up"></i>';
    button.style.cssText = `
        position: fixed;
        bottom: 30px;
        right: 30px;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #6c5ce7 0%, #a463f5 100%);
        color: white;
        border: none;
        cursor: pointer;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        box-shadow: 0 5px 20px rgba(108, 92, 231, 0.3);
        transition: all 0.3s;
        z-index: 999;
    `;
    
    button.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.boxShadow = '0 8px 25px rgba(108, 92, 231, 0.4)';
    });
    
    button.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '0 5px 20px rgba(108, 92, 231, 0.3)';
    });
    
    button.addEventListener('click', function() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
    
    document.body.appendChild(button);
}