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

// ============================================
// Seed2Greens - Background Music Feature
// ============================================
(function() {
    const PREFERENCE_KEY = 'seed2greens_music_preference';
    const STATE_KEY = 'seed2greens_music_state';
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
    let currentTime = 0;

    function getPreference() {
        try {
            return sessionStorage.getItem(PREFERENCE_KEY);
        } catch (e) {
            return null;
        }
    }

    function setPreference(value) {
        try {
            sessionStorage.setItem(PREFERENCE_KEY, value);
        } catch (e) {
            // sessionStorage unavailable
        }
    }

    function getSavedState() {
        try {
            const raw = sessionStorage.getItem(STATE_KEY);
            if (!raw) return null;
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    }

    function saveState() {
        try {
            const state = {
                currentTime: audio ? audio.currentTime : currentTime,
                isPlaying: isPlaying,
                hasEnded: hasEnded,
                timestamp: Date.now()
            };
            sessionStorage.setItem(STATE_KEY, JSON.stringify(state));
        } catch (e) {
            // sessionStorage unavailable
        }
    }

    function clearState() {
        try {
            sessionStorage.removeItem(STATE_KEY);
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

    function createAudio(resumeFromTime) {
        if (audio) {
            audio.pause();
            audio.removeAttribute('src');
            audio.load();
        }

        audio = new Audio(AUDIO_SRC);
        audio.volume = VOLUME;
        audio.loop = false;
        audio.preload = 'none';

        if (typeof resumeFromTime === 'number' && resumeFromTime > 0) {
            audio.currentTime = resumeFromTime;
        }

        audio.addEventListener('ended', function() {
            isPlaying = false;
            hasEnded = true;
            currentTime = 0;
            clearState();
            updateMusicControlUI();
        });

        audio.addEventListener('error', function() {
            console.warn('Seed2Greens: Unable to load music file.');
            isPlaying = false;
            hasEnded = true;
            hideMusicControl();
            closeModal();
            clearState();
        });

        return audio;
    }

    function playMusic(resumeFromTime) {
        try {
            if (!audio) {
                createAudio(resumeFromTime);
            } else if (typeof resumeFromTime === 'number' && resumeFromTime > 0) {
                audio.currentTime = resumeFromTime;
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
            currentTime = audio.currentTime;
            isPlaying = false;
            updateMusicControlUI();
            saveState();
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

    function restoreMusicState() {
        const preference = getPreference();
        if (preference !== 'yes') return;

        const saved = getSavedState();
        if (!saved) return;

        if (saved.hasEnded) {
            hasEnded = true;
            currentTime = 0;
            hideMusicControl();
            clearState();
            return;
        }

        const resumeFrom = typeof saved.currentTime === 'number' ? saved.currentTime : 0;
        hasEnded = false;
        isPlaying = false;
        currentTime = resumeFrom;

        playMusic(resumeFrom);
    }

    function initMusicFeature() {
        const preference = getPreference();

        if (preference === 'yes') {
            restoreMusicState();
            return;
        }

        if (preference === 'no') {
            return;
        }

        setTimeout(showPrompt, 600);
    }

    if (yesBtn) {
        yesBtn.addEventListener('click', function() {
            setPreference('yes');
            closeModal();
            currentTime = 0;
            hasEnded = false;
            clearState();
            playMusic(0);
        });
    }

    if (noBtn) {
        noBtn.addEventListener('click', function() {
            setPreference('no');
            closeModal();
            hasEnded = false;
            isPlaying = false;
            currentTime = 0;
            clearState();
        });
    }

    if (musicControl) {
        musicControl.addEventListener('click', function(e) {
            e.preventDefault();
            handleMusicControlClick();
        });
    }

    window.addEventListener('beforeunload', function() {
        if (audio && isPlaying) {
            currentTime = audio.currentTime;
            saveState();
        } else if (!isPlaying && !hasEnded && currentTime > 0) {
            saveState();
        }
    });

    // Initialize after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initMusicFeature);
    } else {
        initMusicFeature();
    }
})();

// ============================================
// Seed2Greens - FAQ Accordion
// ============================================
(function() {
    const toggleBtn = document.getElementById('faqToggleBtn');
    const hiddenItems = document.querySelectorAll('.faq-hidden');
    const allQuestions = document.querySelectorAll('.faq-question');

    function toggleAnswer(button) {
        const item = button.closest('.faq-item');
        const answer = item.querySelector('.faq-answer');
        const isExpanded = button.getAttribute('aria-expanded') === 'true';

        if (isExpanded) {
            button.setAttribute('aria-expanded', 'false');
            answer.style.maxHeight = '0px';
            answer.style.opacity = '0';
            answer.hidden = true;
        } else {
            button.setAttribute('aria-expanded', 'true');
            answer.hidden = false;
            answer.style.maxHeight = answer.scrollHeight + 'px';
            answer.style.opacity = '1';
        }
    }

    function showMoreFaqs() {
        hiddenItems.forEach(function(item) {
            item.style.display = 'block';
            const question = item.querySelector('.faq-question');
            question.setAttribute('aria-expanded', 'false');
            const answer = item.querySelector('.faq-answer');
            answer.hidden = true;
            answer.style.maxHeight = '0px';
            answer.style.opacity = '0';
        });

        if (toggleBtn) {
            toggleBtn.textContent = 'Show Less';
        }

        hiddenItems = document.querySelectorAll('.faq-hidden');
    }

    function showLessFaqs() {
        const allHidden = document.querySelectorAll('.faq-item.faq-hidden');
        allHidden.forEach(function(item) {
            item.style.display = 'none';
        });

        if (toggleBtn) {
            toggleBtn.textContent = 'More FAQs';
        }

        hiddenItems = allHidden;
    }

    if (toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            const isHidden = hiddenItems.length > 0 && hiddenItems[0].style.display !== 'block';

            if (isHidden) {
                showMoreFaqs();
            } else {
                showLessFaqs();
            }
        });
    }

    allQuestions.forEach(function(button) {
        button.addEventListener('click', function() {
            toggleAnswer(button);
        });
    });
})();

// ============================================
// Seed2Greens - Search Functionality
// ============================================
(function() {
    const desktopInput = document.getElementById('searchInput');
    const desktopSubmit = document.getElementById('searchSubmit');
    const mobileInput = document.getElementById('mobileSearchInput');
    const mobileSubmit = document.getElementById('mobileSearchSubmit');
    const searchDropdown = document.getElementById('searchDropdown');
    const searchResults = document.getElementById('searchResults');

    let debounceTimer = null;
    let currentQuery = '';

    function getProductImageSrc(product) {
        const image = product.image || '';
        if (!image) return '';
        if (image.startsWith('http')) return image;
        if (image.startsWith('img/')) return image;
        return 'img/' + image;
    }

    function renderResults(data, query) {
        if (!searchResults || !searchDropdown) return;

        const results = data.results || [];
        const count = data.count || 0;

        if (count === 0) {
            searchResults.innerHTML = '<div class="search-no-results">No products found for "' + query + '"</div>';
        } else {
            let html = '';
            results.forEach(function(product) {
                const imgSrc = getProductImageSrc(product);
                const imgHtml = imgSrc 
                    ? '<img src="' + imgSrc + '" alt="' + product.name + '" loading="lazy">'
                    : '<i class="fas fa-box"></i>';
                
                html += '<a href="product.php?id=' + product.id + '" class="search-item">' +
                    '<div class="search-item-image">' + imgHtml + '</div>' +
                    '<div class="search-item-info">' +
                        '<div class="search-item-name">' + product.name + '</div>' +
                        '<div class="search-item-category">' + (product.category || '') + '</div>' +
                    '</div>' +
                '</a>';
            });

            if (count >= 5) {
                html += '<div class="search-view-all" data-query="' + query + '">View all search results →</div>';
            }

            searchResults.innerHTML = html;

            const viewAll = searchResults.querySelector('.search-view-all');
            if (viewAll) {
                viewAll.addEventListener('click', function() {
                    window.location.href = 'products.php?search=' + encodeURIComponent(this.dataset.query);
                });
            }
        }

        searchDropdown.hidden = false;
        requestAnimationFrame(function() {
            searchDropdown.classList.add('visible');
        });
    }

    function performSearch(query) {
        currentQuery = query;
        
        if (!searchResults || !searchDropdown) return;
        
        if (query.length < 2) {
            searchResults.innerHTML = '';
            searchDropdown.classList.remove('visible');
            setTimeout(function() {
                if (searchDropdown && searchDropdown.classList.contains('visible') === false) {
                    searchDropdown.hidden = true;
                }
            }, 300);
            return;
        }

        searchResults.innerHTML = '<div class="search-loading">Searching...</div>';
        searchDropdown.hidden = false;
        searchDropdown.classList.add('visible');

        fetch('search.php?q=' + encodeURIComponent(query))
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (query === currentQuery) {
                    renderResults(data, query);
                }
            })
            .catch(function() {
                if (query === currentQuery) {
                    searchResults.innerHTML = '<div class="search-no-results">Search unavailable</div>';
                }
            });
    }

    function closeDropdown() {
        if (searchDropdown) {
            searchDropdown.classList.remove('visible');
            setTimeout(function() {
                if (searchDropdown && searchDropdown.classList.contains('visible') === false) {
                    searchDropdown.hidden = true;
                }
            }, 300);
        }
    }

    function setupSearch(input, submitBtn) {
        if (!input) return;

        input.addEventListener('input', function() {
            const query = input.value.trim();
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                performSearch(query);
            }, 250);
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const query = input.value.trim();
                if (query) {
                    window.location.href = 'products.php?search=' + encodeURIComponent(query);
                }
            } else if (e.key === 'Escape') {
                closeDropdown();
                input.blur();
            }
        });

        if (submitBtn) {
            submitBtn.addEventListener('click', function() {
                const query = input.value.trim();
                if (query) {
                    window.location.href = 'products.php?search=' + encodeURIComponent(query);
                }
            });
        }
    }

    setupSearch(desktopInput, desktopSubmit);
    setupSearch(mobileInput, mobileSubmit);

    document.addEventListener('click', function(e) {
        const navSearch = document.getElementById('navSearch');
        const mobileSearchRow = document.querySelector('.mobile-search-row');
        
        if (searchDropdown && searchDropdown.classList.contains('visible')) {
            if (navSearch && !navSearch.contains(e.target) && 
                mobileSearchRow && !mobileSearchRow.contains(e.target)) {
                closeDropdown();
            }
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeDropdown();
        }
    });
})();

// ============================================
// Seed2Greens - Customer Reviews
// ============================================
(function() {
    const STORAGE_KEY = 'seed2greens_reviews';
    const track = document.getElementById('reviewsTrack');
    const modalOverlay = document.getElementById('reviewModalOverlay');
    const openModalBtn = document.getElementById('openReviewModal');
    const closeModalBtn = document.getElementById('reviewModalClose');
    const reviewForm = document.getElementById('reviewForm');
    const reviewName = document.getElementById('reviewName');
    const reviewText = document.getElementById('reviewText');
    const starsInput = document.getElementById('reviewStarsInput');
    const starButtons = starsInput ? starsInput.querySelectorAll('.review-star') : [];

    let selectedRating = 0;

    const seedReviews = [
        { name: 'Samir Bhandari', rating: 5, review: 'I ordered a few vegetable seeds for my home garden and was really impressed with the quality. The seeds arrived well packed and the growing results have been great.' },
        { name: 'Sambhab Karna', rating: 5, review: 'Fresh produce at my doorstep in Kathmandu — what more could I ask for? The tomatoes and spinach were honestly the best I have had in a while.' },
        { name: 'Shital Adhikari', rating: 4, review: 'Good variety of organic fertilizers and tools. Ordering was simple and delivery was on time. Would love to see more seed options in the future.' },
        { name: 'Sabita Maharjan', rating: 5, review: 'The vermicompost I bought transformed my balcony garden. My plants are healthier and producing more than ever. Highly recommended for home gardeners.' },
        { name: 'Amit Rai', rating: 5, review: 'Seed quality is consistently good. I have ordered multiple times and every packet has had high germination rates. Packaging is also neat and secure.' },
        { name: 'Sumila Shakya', rating: 4, review: 'I appreciate the focus on organic products. The fertilizers work well and customer support was helpful when I had questions about application.' },
        { name: 'Karan Timalsina', rating: 5, review: 'The gardening gloves and hand trowel I ordered are sturdy and comfortable. Great build quality for the price. Will definitely order more tools from here.' },
        { name: 'Sadish Thapa', rating: 5, review: 'Fast delivery and genuine organic products. The fresh cauliflower and carrots were crisp and lasted much longer than supermarket produce.' },
        { name: 'Shovit Shrestha', rating: 4, review: 'Simple ordering process and good product range. The cucumber seeds gave a nice yield. Minor suggestion: add more seasonal items during festivals.' },
        { name: 'Shobhindra Budhathoki', rating: 5, review: 'As a commercial grower, I need reliable supplies. Seed 2 Greens has become my go-to for bulk seeds and fertilizers. Consistent quality every time.' }
    ];

    function getReviews() {
        try {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw) {
                const data = JSON.parse(raw);
                if (Array.isArray(data) && data.length > 0) {
                    return data;
                }
            }
        } catch (e) {
            // ignore
        }
        return seedReviews.slice();
    }

    function saveReviews(reviews) {
        try {
            localStorage.setItem(STORAGE_KEY, JSON.stringify(reviews));
        } catch (e) {
            // ignore
        }
    }

    function getInitials(name) {
        const parts = name.trim().split(/\s+/);
        if (parts.length >= 2) {
            return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
        }
        return name.substring(0, 2).toUpperCase();
    }

    function renderStars(rating) {
        let html = '';
        for (let i = 1; i <= 5; i++) {
            html += i <= rating ? '★' : '☆';
        }
        return html;
    }

    function createReviewCard(review) {
        const card = document.createElement('div');
        card.className = 'review-card';
        card.innerHTML = '' +
            '<div class="review-card-stars" aria-label="' + review.rating + ' out of 5 stars">' + renderStars(review.rating) + '</div>' +
            '<div class="review-card-text">' + escapeHtml(review.review) + '</div>' +
            '<div class="review-card-divider"></div>' +
            '<div class="review-card-author">' +
                '<div class="review-avatar" aria-hidden="true">' + getInitials(review.name) + '</div>' +
                '<div class="review-author-name">' + escapeHtml(review.name) + '</div>' +
            '</div>';
        return card;
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function renderCarousel() {
        if (!track) return;
        track.innerHTML = '';
        const reviews = getReviews();
        const fragment = document.createDocumentFragment();
        reviews.forEach(function(review) {
            fragment.appendChild(createReviewCard(review));
        });
        track.appendChild(fragment);

        const clone = document.createDocumentFragment();
        reviews.forEach(function(review) {
            clone.appendChild(createReviewCard(review));
        });
        track.appendChild(clone);
    }

    function initReviews() {
        if (!track) return;
        renderCarousel();
    }

    function openModal() {
        if (!modalOverlay) return;
        modalOverlay.classList.add('active');
        modalOverlay.setAttribute('aria-hidden', 'false');
        reviewName.focus();
    }

    function closeModal() {
        if (!modalOverlay) return;
        modalOverlay.classList.remove('active');
        modalOverlay.setAttribute('aria-hidden', 'true');
        reviewForm.reset();
        selectedRating = 0;
        updateStarDisplay();
        clearErrors();
    }

    function clearErrors() {
        document.querySelectorAll('.review-error').forEach(function(el) {
            el.classList.remove('visible');
        });
    }

    function showError(id) {
        const el = document.getElementById(id);
        if (el) el.classList.add('visible');
    }

    function updateStarDisplay() {
        starButtons.forEach(function(btn) {
            const value = parseInt(btn.getAttribute('data-value'), 10);
            btn.classList.toggle('selected', value <= selectedRating);
            btn.textContent = value <= selectedRating ? '★' : '☆';
        });
    }

    if (openModalBtn) {
        openModalBtn.addEventListener('click', openModal);
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }

    if (modalOverlay) {
        modalOverlay.addEventListener('click', function(e) {
            if (e.target === modalOverlay) {
                closeModal();
            }
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modalOverlay && modalOverlay.classList.contains('active')) {
            closeModal();
        }
    });

    starButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            selectedRating = parseInt(this.getAttribute('data-value'), 10);
            updateStarDisplay();
        });

        btn.addEventListener('mouseenter', function() {
            const value = parseInt(this.getAttribute('data-value'), 10);
            starButtons.forEach(function(b) {
                const v = parseInt(b.getAttribute('data-value'), 10);
                if (v <= value) {
                    b.classList.add('hovered');
                    b.textContent = '★';
                } else {
                    b.classList.remove('hovered');
                    b.textContent = '☆';
                }
            });
        });

        btn.addEventListener('mouseleave', function() {
            starButtons.forEach(function(b) {
                b.classList.remove('hovered');
            });
            updateStarDisplay();
        });
    });

    reviewForm.addEventListener('submit', function(e) {
        e.preventDefault();
        clearErrors();

        const name = reviewName.value.trim();
        const text = reviewText.value.trim();
        let valid = true;

        if (!name) {
            showError('reviewNameError');
            valid = false;
        }

        if (!selectedRating) {
            showError('reviewRatingError');
            valid = false;
        }

        if (!text) {
            showError('reviewTextError');
            valid = false;
        }

        if (!valid) return;

        const reviews = getReviews();
        reviews.unshift({ name: name, rating: selectedRating, review: text, date: new Date().toISOString() });
        saveReviews(reviews);
        renderCarousel();
        closeModal();
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initReviews);
    } else {
        initReviews();
    }
})();
