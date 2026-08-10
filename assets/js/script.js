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

// ============================================
// Kathmandu Live Time + Weather
// ============================================
(function() {
    const KATHMANDU_LAT = 27.7172;
    const KATHMANDU_LON = 85.3240;
    const WEATHER_CACHE_MS = 10 * 60 * 1000; // 10 minutes

    let weatherCache = { data: null, ts: null };

    function updateClock() {
        const el = document.getElementById('kathmanduTime');
        if (!el) return;
        const now = new Date();
        const isMobile = window.innerWidth <= 480;
        const options = {
            timeZone: 'Asia/Kathmandu',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        };
        if (!isMobile) {
            options.second = '2-digit';
        }
        const fmt = new Intl.DateTimeFormat('en-US', options);
        el.textContent = fmt.format(now);
    }

    function weatherCodeToIcon(code) {
        if (code === 0 || code === 1) return 'fa-sun';
        if (code === 2) return 'fa-cloud-sun';
        if (code === 3) return 'fa-cloud';
        if (code === 45 || code === 48) return 'fa-smog';
        if (code >= 51 && code <= 55) return 'fa-cloud-rain';
        if (code >= 61 && code <= 65) return 'fa-cloud-rain';
        if (code >= 71 && code <= 75) return 'fa-snowflake';
        if (code >= 80 && code <= 82) return 'fa-cloud-rain';
        if (code >= 95 && code <= 99) return 'fa-bolt';
        return 'fa-cloud';
    }

    function weatherCodeToText(code) {
        if (code === 0 || code === 1) return 'Clear';
        if (code === 2) return 'Partly Cloudy';
        if (code === 3) return 'Overcast';
        if (code === 45 || code === 48) return 'Fog';
        if (code >= 51 && code <= 55) return 'Drizzle';
        if (code >= 61 && code <= 65) return 'Rain';
        if (code >= 71 && code <= 75) return 'Snow';
        if (code >= 80 && code <= 82) return 'Rain Showers';
        if (code >= 95 && code <= 99) return 'Thunderstorm';
        return 'Cloudy';
    }

    function updateWeatherUI(data, isStale) {
        const conditionEl = document.getElementById('weatherCondition');
        const iconEl = document.getElementById('weatherIcon');
        const textEl = document.getElementById('weatherConditionText');
        const tempEl = document.getElementById('weatherTemp');
        const humidityEl = document.getElementById('weatherHumidity');
        const windEl = document.getElementById('weatherWind');

        if (!conditionEl || !tempEl) return;

        const current = data.current || data;
        const code = current.weather_code;
        const icon = weatherCodeToIcon(code);
        const text = weatherCodeToText(code);

        if (iconEl) iconEl.className = 'fas ' + icon;
        if (textEl) textEl.textContent = text + (isStale ? ' (cached)' : '');
        tempEl.textContent = Math.round(current.temperature_2m) + '°C';

        if (humidityEl) humidityEl.textContent = Math.round(current.relative_humidity_2m) + '%';
        if (windEl) windEl.textContent = Math.round(current.wind_speed_10m) + ' km/h';
    }

    function showWeatherUnavailable() {
        const iconEl = document.getElementById('weatherIcon');
        const textEl = document.getElementById('weatherConditionText');
        const tempEl = document.getElementById('weatherTemp');
        if (iconEl) iconEl.className = 'fas fa-circle-exclamation';
        if (textEl) textEl.textContent = 'Weather unavailable';
        if (tempEl) tempEl.textContent = '';
    }

    async function fetchWeather() {
        const now = Date.now();
        if (weatherCache.data && weatherCache.ts && (now - weatherCache.ts < WEATHER_CACHE_MS)) {
            updateWeatherUI(weatherCache.data, false);
            return;
        }

        try {
            const url = 'https://api.open-meteo.com/v1/forecast?latitude=' + KATHMANDU_LAT + '&longitude=' + KATHMANDU_LON + '&current=temperature_2m,relative_humidity_2m,weather_code,wind_speed_10m&timezone=Asia/Kathmandu';
            const response = await fetch(url);
            if (!response.ok) throw new Error('API error: ' + response.status);
            const data = await response.json();
            weatherCache = { data: data, ts: now };
            updateWeatherUI(data, false);
        } catch (err) {
            console.error('Weather fetch failed:', err);
            if (weatherCache.data) {
                updateWeatherUI(weatherCache.data, true);
            } else {
                showWeatherUnavailable();
            }
        }
    }

    // Start clock immediately
    updateClock();
    setInterval(updateClock, 1000);

    // Fetch weather
    fetchWeather();
    setInterval(fetchWeather, WEATHER_CACHE_MS);
})();

/* === SURPRISE Diwali Light Effect for Sandesh Bhandari === */
(function() {
    const surpriseBtn = document.getElementById('surpriseBtn');
    const overlay = document.getElementById('surpriseOverlay');
    const particlesContainer = document.getElementById('surpriseParticles');

    if (!surpriseBtn || !overlay || !particlesContainer) return;

    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function createParticles() {
        particlesContainer.innerHTML = '';
        const colors = ['#22C55E', '#FACC15', '#FFFFFF', '#4ADE80', '#FDE047', '#86EFAC'];
        for (let i = 0; i < 50; i++) {
            const particle = document.createElement('span');
            particle.className = 'particle';
            const size = Math.random() * 6 + 3;
            const left = Math.random() * 100;
            const delay = Math.random() * 1.5;
            const duration = Math.random() * 1.5 + 1.5;
            const color = colors[Math.floor(Math.random() * colors.length)];

            particle.style.cssText = `
                left: ${left}%;
                width: ${size}px;
                height: ${size}px;
                background: ${color};
                box-shadow: 0 0 ${size * 2}px ${color};
                animation: particleFloat ${duration}s cubic-bezier(0.4, 0, 0.2, 1) ${delay}s forwards;
            `;

            particlesContainer.appendChild(particle);
        }
    }

    function triggerSurprise() {
        if (overlay.classList.contains('active')) {
            overlay.classList.remove('active');
            setTimeout(function() {
                createParticles();
                overlay.classList.add('active');
            }, 100);
        } else {
            createParticles();
            overlay.classList.add('active');
        }

        setTimeout(function() {
            overlay.classList.remove('active');
        }, 3000);
    }

    surpriseBtn.addEventListener('click', function(e) {
        e.preventDefault();
        triggerSurprise();
    });
})();

// ============================================
// Seed2Greens - Background Music Feature
// ============================================
(function() {
    const STORAGE_KEY = 'seed2greens_music_preference';
    const AUDIO_SRC = 'img/song.mp3';
    const VOLUME = 0.5;

    const modalOverlay = document.getElementById('musicModalOverlay');
    const yesBtn = document.getElementById('musicYesBtn');
    const noBtn = document.getElementById('musicNoBtn');
    const musicControl = document.getElementById('musicControl');
    const musicControlIcon = document.getElementById('musicControlIcon');
    const musicControlText = document.getElementById('musicControlText');

    let audio = null;
    let isPlaying = false;
    let hasEnded = false;

    function getPreference() {
        try {
            return sessionStorage.getItem(STORAGE_KEY);
        } catch (e) {
            return null;
        }
    }

    function setPreference(value) {
        try {
            sessionStorage.setItem(STORAGE_KEY, value);
        } catch (e) {
            // sessionStorage unavailable
        }
    }

    function closeModal() {
        if (modalOverlay) {
            modalOverlay.classList.remove('active');
            modalOverlay.setAttribute('aria-hidden', 'true');
        }
    }

    function showMusicControl() {
        if (musicControl) {
            musicControl.style.display = 'inline-flex';
            updateMusicControlUI();
        }
    }

    function hideMusicControl() {
        if (musicControl) {
            musicControl.style.display = 'none';
        }
    }

    function updateMusicControlUI() {
        if (!musicControlIcon || !musicControlText) return;
        if (hasEnded) {
            musicControlIcon.textContent = '🎵';
            musicControlText.textContent = 'Finished';
        } else if (isPlaying) {
            musicControlIcon.textContent = '⏸';
            musicControlText.textContent = 'Playing';
        } else {
            musicControlIcon.textContent = '▶';
            musicControlText.textContent = 'Paused';
        }
    }

    function createAudio() {
        if (audio) {
            audio.pause();
            audio.removeAttribute('src');
            audio.load();
        }

        audio = new Audio(AUDIO_SRC);
        audio.volume = VOLUME;
        audio.loop = false;
        audio.preload = 'none';

        audio.addEventListener('ended', function() {
            isPlaying = false;
            hasEnded = true;
            updateMusicControlUI();
        });

        audio.addEventListener('error', function() {
            console.warn('Seed2Greens: Unable to load music file.');
            isPlaying = false;
            hasEnded = true;
            hideMusicControl();
            closeModal();
        });

        return audio;
    }

    function playMusic() {
        try {
            if (!audio) {
                createAudio();
            }

            if (hasEnded) {
                audio.currentTime = 0;
                hasEnded = false;
            }

            const playPromise = audio.play();
            if (playPromise !== undefined) {
                playPromise.then(function() {
                    isPlaying = true;
                    showMusicControl();
                    updateMusicControlUI();
                }).catch(function(err) {
                    console.warn('Seed2Greens: Playback failed.', err);
                    isPlaying = false;
                    hideMusicControl();
                });
            }
        } catch (e) {
            console.warn('Seed2Greens: Music error.', e);
            hideMusicControl();
        }
    }

    function pauseMusic() {
        if (audio && isPlaying) {
            audio.pause();
            isPlaying = false;
            updateMusicControlUI();
        }
    }

    function resumeMusic() {
        if (audio && !isPlaying && !hasEnded) {
            const resumePromise = audio.play();
            if (resumePromise !== undefined) {
                resumePromise.then(function() {
                    isPlaying = true;
                    updateMusicControlUI();
                }).catch(function(err) {
                    console.warn('Seed2Greens: Resume failed.', err);
                });
            }
        }
    }

    function handleMusicControlClick() {
        if (!audio || hasEnded) {
            if (hasEnded) {
                hasEnded = false;
                playMusic();
            }
            return;
        }

        if (isPlaying) {
            pauseMusic();
        } else {
            resumeMusic();
        }
    }

    function showPrompt() {
        if (modalOverlay) {
            modalOverlay.classList.add('active');
            modalOverlay.setAttribute('aria-hidden', 'false');
        }
    }

    function initMusicFeature() {
        const preference = getPreference();

        if (preference === 'yes') {
            playMusic();
            return;
        }

        if (preference === 'no') {
            return;
        }

        // No preference stored yet; show prompt after a short delay
        setTimeout(showPrompt, 600);
    }

    if (yesBtn) {
        yesBtn.addEventListener('click', function() {
            setPreference('yes');
            closeModal();
            playMusic();
        });
    }

    if (noBtn) {
        noBtn.addEventListener('click', function() {
            setPreference('no');
            closeModal();
        });
    }

    if (musicControl) {
        musicControl.addEventListener('click', function(e) {
            e.preventDefault();
            handleMusicControlClick();
        });
    }

    // Initialize after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMusicFeature);
    } else {
        initMusicFeature();
    }
})();
