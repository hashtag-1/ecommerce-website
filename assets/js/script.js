// Seed2Greens - JavaScript Functions

document.addEventListener('DOMContentLoaded', function() {
    
    // === Mobile Menu Toggle ===
    const hamburger = document.getElementById('hamburger');
    const navMenu = document.getElementById('navMenu');
    
    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!hamburger.contains(e.target) && !navMenu.contains(e.target)) {
                navMenu.classList.remove('active');
            }
        });
    }
    
    // === Mobile Dropdown Toggle ===
    const dropdownToggles = document.querySelectorAll('.dropdown-toggle');
    
    dropdownToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function(e) {
            if (window.innerWidth <= 768) {
                e.preventDefault();
                this.parentElement.classList.toggle('active');
            }
        });
    });
    
    // === Quantity Controls ===
    const quantityInputs = document.querySelectorAll('.quantity-control input');
    
    quantityInputs.forEach(function(input) {
        const minusBtn = input.previousElementSibling;
        const plusBtn = input.nextElementSibling;
        
        if (minusBtn) {
            minusBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                if (value > 1) {
                    input.value = value - 1;
                    // Trigger change event
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
        
        if (plusBtn) {
            plusBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                const max = parseInt(input.getAttribute('max')) || 99;
                if (value < max) {
                    input.value = value + 1;
                    input.dispatchEvent(new Event('change'));
                }
            });
        }
    });
    
    // === Flash Message Auto-close ===
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(function(msg) {
        setTimeout(function() {
            msg.style.opacity = '0';
            setTimeout(function() {
                msg.remove();
            }, 300);
        }, 4000);
    });
    
    // === Form Validation ===
    const forms = document.querySelectorAll('form[data-validate]');
    
    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Check required fields
            const requiredFields = form.querySelectorAll('[required]');
            requiredFields.forEach(function(field) {
                if (!field.value.trim()) {
                    isValid = false;
                    field.classList.add('error');
                    
                    // Add error styling if not exists
                    if (!field.nextElementSibling || !field.nextElementSibling.classList.contains('error-message')) {
                        const error = document.createElement('span');
                        error.className = 'error-message';
                        error.style.cssText = 'color: #dc3545; font-size: 12px; margin-top: 5px; display: block;';
                        error.textContent = 'This field is required';
                        field.parentNode.insertBefore(error, field.nextSibling);
                    }
                } else {
                    field.classList.remove('error');
                    const error = field.parentNode.querySelector('.error-message');
                    if (error) error.remove();
                }
            });
            
            // Email validation
            const emailFields = form.querySelectorAll('input[type="email"]');
            emailFields.forEach(function(field) {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (field.value && !emailRegex.test(field.value)) {
                    isValid = false;
                    field.classList.add('error');
                }
            });
            
            // Password match validation
            const confirmPassword = form.querySelector('#confirm_password');
            const password = form.querySelector('#password');
            
            if (confirmPassword && password) {
                if (confirmPassword.value !== password.value) {
                    isValid = false;
                    confirmPassword.classList.add('error');
                }
            }
            
            if (!isValid) {
                e.preventDefault();
            }
        });
    });
    
    // === Password Toggle ===
    const passwordToggles = document.querySelectorAll('.password-toggle');
    
    passwordToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '<i class="fas fa-eye-slash"></i>';
            } else {
                input.type = 'password';
                this.innerHTML = '<i class="fas fa-eye"></i>';
            }
        });
    });
    
    // === Confirmation Dialogs ===
    const confirmButtons = document.querySelectorAll('[data-confirm]');
    
    confirmButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            const message = this.getAttribute('data-confirm') || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // === Auto-calculate totals on cart page ===
    const quantityInputsCart = document.querySelectorAll('.cart-quantity-input');
    
    quantityInputsCart.forEach(function(input) {
        const form = input.closest('.cart-qty-form');
        const minusBtn = form?.querySelector('[data-action="decrease"]');
        const plusBtn = form?.querySelector('[data-action="increase"]');
        
        if (minusBtn) {
            minusBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                if (value > parseInt(input.min)) {
                    input.value = value - 1;
                    form.submit();
                }
            });
        }
        
        if (plusBtn) {
            plusBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                const max = parseInt(input.max);
                if (value < max) {
                    input.value = value + 1;
                    form.submit();
                }
            });
        }
        
        input.addEventListener('change', function() {
            const form = this.closest('.cart-qty-form');
            if (form) {
                form.submit();
            }
        });
    });
    
    // === Add to cart animation ===
    const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
    
    addToCartButtons.forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            
            const form = this.closest('form');
            if (!form) return;
            
            const originalText = this.innerHTML;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            this.disabled = true;
            
            const formData = new FormData(form);
            formData.append('add_to_cart', '1');
            
            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const cartBadge = document.querySelector('.nav-icon[href="cart.php"] .badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                    }
                    
                    btn.innerHTML = '<i class="fas fa-check"></i> Added!';
                    btn.style.background = '#28a745';
                    
                    setTimeout(function() {
                        btn.innerHTML = originalText;
                        btn.style.background = '';
                        btn.disabled = false;
                    }, 1500);
                } else {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                        return;
                    }
                    btn.innerHTML = originalText;
                    btn.style.background = '';
                    btn.disabled = false;
                    alert(data.message || 'Failed to add to cart');
                }
            })
            .catch(() => {
                btn.innerHTML = originalText;
                btn.style.background = '';
                btn.disabled = false;
            });
        });
    });
    
    // === Smooth scroll for anchor links ===
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
    
    // === Search input live filter (client-side only) ===
    const searchInput = document.getElementById('searchInput');
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const term = this.value.toLowerCase();
            const productCards = document.querySelectorAll('.product-card');
            
            productCards.forEach(function(card) {
                const name = card.querySelector('h3')?.textContent.toLowerCase() || '';
                const desc = card.querySelector('p')?.textContent.toLowerCase() || '';
                
                if (name.includes(term) || desc.includes(term)) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
});

/* === Rose Animation for Sandesh Bhandari Contact Button === */
(function() {
    const contactBtn = document.getElementById('contactRoseBtn');
    const roseContainer = document.getElementById('roseContainer');
    
    if (!contactBtn || !roseContainer) return;
    
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    
    function createRose() {
        const wrapper = document.createElement('div');
        wrapper.className = 'rose-wrapper' + (prefersReducedMotion ? ' instant' : '');
        
        wrapper.innerHTML = `
            <div class="rose-glow"></div>
            <div class="rose-bloom">
                <div class="rose-petal inner-1"></div>
                <div class="rose-petal inner-2"></div>
                <div class="rose-petal inner-3"></div>
                <div class="rose-petal outer-1"></div>
                <div class="rose-petal outer-2"></div>
                <div class="rose-petal outer-3"></div>
                <div class="rose-petal outer-4"></div>
                <div class="rose-petal outer-5"></div>
                <div class="rose-petal center"></div>
            </div>
            <div class="rose-stem"></div>
            <div class="rose-leaf left"></div>
            <div class="rose-leaf right"></div>
        `;
        
        roseContainer.appendChild(wrapper);
        
        if (prefersReducedMotion) {
            wrapper.style.opacity = '1';
            wrapper.querySelectorAll('.rose-petal, .rose-stem, .rose-leaf, .rose-bloom, .rose-glow').forEach(function(el) {
                el.style.opacity = '1';
                if (el.classList.contains('rose-petal')) {
                    var rotation = getComputedStyle(el).getPropertyValue('--petal-rotation').trim();
                    el.style.transform = 'scale(1) rotate(' + (rotation || '0deg') + ')';
                } else {
                    el.style.transform = 'scale(1)';
                }
            });
        } else {
            requestAnimationFrame(function() {
                wrapper.classList.add('blooming');
            });
        }
        
        return wrapper;
    }
    
    contactBtn.addEventListener('click', function(e) {
        e.preventDefault();
        
        const existing = roseContainer.querySelector('.rose-wrapper');
        if (existing) {
            existing.remove();
        }
        
        createRose();
    });
})();
