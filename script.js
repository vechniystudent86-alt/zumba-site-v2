/**
 * Zumba Trainer Website - Interactive JavaScript
 * Alexander Melnikov
 * Версия: 2.1.0 (с оптимизациями производительности и a11y)
 */

/**
 * Utility: Throttle function
 * Ограничивает частоту вызова функции
 */
function throttle(fn, delay) {
    let lastCall = 0;
    let timeoutId = null;
    return function(...args) {
        const now = Date.now();
        const remaining = delay - (now - lastCall);
        if (remaining <= 0) {
            if (timeoutId) {
                clearTimeout(timeoutId);
                timeoutId = null;
            }
            lastCall = now;
            fn.apply(this, args);
        } else if (!timeoutId) {
            timeoutId = setTimeout(() => {
                lastCall = Date.now();
                timeoutId = null;
                fn.apply(this, args);
            }, remaining);
        }
    };
}

/**
 * Utility: Debounce function
 * Откладывает вызов функции до прекращения событий
 */
function debounce(fn, delay) {
    let timeoutId;
    return function(...args) {
        clearTimeout(timeoutId);
        timeoutId = setTimeout(() => fn.apply(this, args), delay);
    };
}

/**
 * Проверка prefers-reduced-motion
 * Для пользователей с ограниченной анимацией
 */
const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

document.addEventListener('DOMContentLoaded', () => {
    // Initialize all modules
    CustomCursor.init();
    Navigation.init();
    ScrollAnimations.init();
    CounterAnimation.init();
    TiltEffect.init();
    ContactForm.init();
    SmoothScroll.init();
    HeartAnimation.init();
    ReviewsSlider.init();
    FAQ.init();
    MetrikaGoals.init();
    ServiceWorkerHandler.init();
});

/**
 * Custom Cursor Module
 * Кастомный курсор с эффектом сердечек
 */
const CustomCursor = {
    cursor: null,
    follower: null,
    heartsContainer: null,
    lastHeartTime: 0,
    heartCount: 0,
    maxHearts: 30, // Уменьшено с 50 для производительности
    enabled: false,

    init() {
        // Отключаем для пользователей с prefers-reduced-motion
        if (prefersReducedMotion) {
            return;
        }
        
        if (window.matchMedia('(pointer: fine)').matches) {
            this.heartsContainer = document.getElementById('hearts-container');
            this.createCursor();
            this.addEventListeners();
            this.enabled = true;
        }
    },

    createCursor() {
        this.cursor = document.createElement('div');
        this.cursor.className = 'cursor';
        this.cursor.setAttribute('aria-hidden', 'true');
        this.follower = document.createElement('div');
        this.follower.className = 'cursor-follower';
        this.follower.setAttribute('aria-hidden', 'true');
        document.body.appendChild(this.cursor);
        document.body.appendChild(this.follower);
    },

    addEventListeners() {
        // Throttled mousemove для производительности
        const throttledMouseMove = throttle((e) => {
            this.cursor.style.left = e.clientX + 'px';
            this.cursor.style.top = e.clientY + 'px';

            // Уменьшена задержка follower для лучшей производительности
            requestAnimationFrame(() => {
                this.follower.style.left = e.clientX + 'px';
                this.follower.style.top = e.clientY + 'px';
            });

            // Создаём сердечки реже
            this.createHeart(e.clientX, e.clientY);
        }, 16); // ~60fps

        document.addEventListener('mousemove', throttledMouseMove, { passive: true });

        // Hover effects
        const hoverElements = document.querySelectorAll('a, button, .program-card, .review-card');
        hoverElements.forEach(el => {
            el.addEventListener('mouseenter', () => this.follower.classList.add('hover'));
            el.addEventListener('mouseleave', () => this.follower.classList.remove('hover'));
        });

        // Click effect - burst of hearts (уменьшено количество)
        document.addEventListener('click', (e) => {
            this.createHeartBurst(e.clientX, e.clientY);
        });
    },

    createHeart(x, y) {
        if (!this.enabled || !this.heartsContainer) return;
        
        const now = Date.now();
        // Увеличен интервал между сердечками
        if (now - this.lastHeartTime < 150) return;
        this.lastHeartTime = now;

        // Ограничиваем максимальное количество сердечек
        while (this.heartsContainer.children.length >= this.maxHearts) {
            const oldest = this.heartsContainer.firstElementChild;
            if (oldest) oldest.remove();
        }

        const heart = document.createElement('div');
        heart.className = 'heart-particle';
        heart.innerHTML = '❤';
        heart.style.left = (x + (Math.random() - 0.5) * 40) + 'px';
        heart.style.top = (y + (Math.random() - 0.5) * 40) + 'px';
        heart.style.fontSize = (12 + Math.random() * 16) + 'px';
        heart.style.color = this.getRandomHeartColor();
        heart.setAttribute('aria-hidden', 'true');

        this.heartsContainer.appendChild(heart);

        // Удаляем сердечко после анимации
        setTimeout(() => {
            if (heart.parentNode) {
                heart.remove();
            }
        }, 2000); // Уменьшено с 3000ms
    },

    createHeartBurst(x, y) {
        if (!this.enabled) return;
        
        // Уменьшено количество сердечек в burst
        const burstCount = 5;
        for (let i = 0; i < burstCount; i++) {
            setTimeout(() => {
                const offsetX = (Math.random() - 0.5) * 80;
                const offsetY = (Math.random() - 0.5) * 80;
                this.createHeart(x + offsetX, y + offsetY);
            }, i * 40);
        }
    },

    getRandomHeartColor() {
        const colors = [
            '#FF2D75',
            '#FF5B95',
            '#FFB800',
            '#FF6B6B',
            '#E61E5F',
        ];
        return colors[Math.floor(Math.random() * colors.length)];
    }
};

/**
 * Navigation Module
 * Навигация с мобильным меню и активной ссылкой
 */
const Navigation = {
    navbar: null,
    toggle: null,
    menu: null,

    init() {
        this.navbar = document.querySelector('.navbar');
        this.toggle = document.querySelector('.nav-toggle');
        this.menu = document.querySelector('.nav-menu');

        if (!this.navbar || !this.toggle || !this.menu) {
            return;
        }

        this.addScrollListener();
        this.addToggleListener();
        this.addActiveLinkListener();
    },

    addScrollListener() {
        // Throttled scroll listener
        const throttledScroll = throttle(() => {
            if (window.scrollY > 100) {
                this.navbar.classList.add('scrolled');
            } else {
                this.navbar.classList.remove('scrolled');
            }
        }, 100);

        window.addEventListener('scroll', throttledScroll, { passive: true });
    },

    addToggleListener() {
        this.toggle.addEventListener('click', () => {
            this.toggle.classList.toggle('active');
            this.menu.classList.toggle('active');
            
            // A11y: обновляем aria-expanded
            const isExpanded = this.toggle.classList.contains('active');
            this.toggle.setAttribute('aria-expanded', isExpanded.toString());
        });

        // Close menu on link click
        this.menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                this.toggle.classList.remove('active');
                this.menu.classList.remove('active');
                this.toggle.setAttribute('aria-expanded', 'false');
            });
        });
        
        // Close menu on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.toggle.classList.contains('active')) {
                this.toggle.classList.remove('active');
                this.menu.classList.remove('active');
                this.toggle.setAttribute('aria-expanded', 'false');
                this.toggle.focus();
            }
        });
    },

    addActiveLinkListener() {
        const sections = document.querySelectorAll('section[id]');
        if (!sections.length) return;

        // Throttled scroll listener
        const throttledScroll = throttle(() => {
            const scrollY = window.scrollY;

            sections.forEach(section => {
                const sectionHeight = section.offsetHeight;
                const sectionTop = section.offsetTop - 150;
                const sectionId = section.getAttribute('id');

                if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
                    this.menu.querySelectorAll('a').forEach(link => {
                        link.classList.remove('active');
                        if (link.getAttribute('href') === `#${sectionId}`) {
                            link.classList.add('active');
                        }
                    });
                }
            });
        }, 100);

        window.addEventListener('scroll', throttledScroll, { passive: true });
    }
};

/**
 * Scroll Animations Module
 * Анимации при скролле с использованием IntersectionObserver
 */
const ScrollAnimations = {
    elements: null,
    observer: null,

    init() {
        this.elements = document.querySelectorAll('.fade-in, .program-card, .review-card, .stat-item');
        if (!this.elements.length) return;
        
        this.addScrollListener();
        this.checkVisibility(); // Check on load
    },

    addScrollListener() {
        // Используем IntersectionObserver вместо scroll event
        if ('IntersectionObserver' in window) {
            this.observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        // Перестаем наблюдать после появления
                        this.observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '0px 0px -15% 0px',
                threshold: 0.1
            });

            this.elements.forEach(el => this.observer.observe(el));
        } else {
            // Fallback для старых браузеров
            window.addEventListener('scroll', throttle(() => {
                this.checkVisibility();
            }, 100), { passive: true });
        }
    },

    checkVisibility() {
        const triggerBottom = window.innerHeight * 0.85;

        this.elements.forEach(el => {
            const elementTop = el.getBoundingClientRect().top;
            if (elementTop < triggerBottom) {
                el.classList.add('visible');
            }
        });
    }
};

/**
 * Counter Animation Module
 * Анимация счётчиков статистики
 */
const CounterAnimation = {
    counters: null,
    animated: false,
    observer: null,

    init() {
        this.counters = document.querySelectorAll('.stat-number');
        if (!this.counters.length) return;
        
        this.addScrollListener();
    },

    addScrollListener() {
        const statsSection = document.querySelector('.stats');
        if (!statsSection) return;

        // Отключаем для prefers-reduced-motion
        if (prefersReducedMotion) {
            // Просто показываем финальные значения
            this.counters.forEach(counter => {
                const target = parseInt(counter.getAttribute('data-target'));
                counter.textContent = target.toLocaleString() + '+';
            });
            return;
        }

        this.observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.animated) {
                    this.animateCounters();
                    this.animated = true;
                    this.observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        this.observer.observe(statsSection);
    },

    animateCounters() {
        this.counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000;
            const startTime = performance.now();
            const startValue = 0;

            const updateCounter = (currentTime) => {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Easing function (ease-out)
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const current = startValue + (target * easeOut);

                counter.textContent = Math.floor(current).toLocaleString();

                if (progress < 1) {
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target.toLocaleString() + '+';
                }
            };

            requestAnimationFrame(updateCounter);
        });
    }
};

/**
 * Tilt Effect Module (3D Card Effect)
 */
const TiltEffect = {
    cards: null,
    
    init() {
        this.cards = document.querySelectorAll('[data-tilt]');
        this.addEventListeners();
    },
    
    addEventListeners() {
        this.cards.forEach(card => {
            card.addEventListener('mousemove', (e) => this.handleMouseMove(e, card));
            card.addEventListener('mouseleave', () => this.handleMouseLeave(card));
        });
    },
    
    handleMouseMove(e, card) {
        const rect = card.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;
        
        const centerX = rect.width / 2;
        const centerY = rect.height / 2;
        
        const rotateX = (y - centerY) / 10;
        const rotateY = (centerX - x) / 10;
        
        card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-10px)`;
    },
    
    handleMouseLeave(card) {
        card.style.transform = 'perspective(1000px) rotateX(0) rotateY(0) translateY(0)';
    }
};

/**
 * Contact Form Module
 * Отправляет данные через серверный PHP-обработчик (send-form.php)
 */
const ContactForm = {
    form: null,
    submitUrl: 'send-form.php',
    csrfToken: null,

    init() {
        this.form = document.getElementById('contactForm');
        if (this.form) {
            this.loadCsrfToken();
            this.addSubmitListener();
        }
    },

    loadCsrfToken() {
        // Получаем токен из скрытого поля (если сервер уже добавил его)
        const csrfInput = document.getElementById('csrf_token');
        if (csrfInput && csrfInput.value) {
            this.csrfToken = csrfInput.value;
        }
    },

    addSubmitListener() {
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            this.handleSubmit();
        });
    },

    async handleSubmit() {
        const submitBtn = this.form.querySelector('button[type="submit"]');
        
        // Проверка на существование кнопки
        if (!submitBtn) {
            this.showInlineError('Ошибка формы. Попробуйте обновить страницу.');
            return;
        }
        
        const originalText = submitBtn.innerHTML;

        // Clear previous messages
        this.clearMessages();

        // Get form values
        const formData = new FormData(this.form);
        const data = Object.fromEntries(formData);

        // Client-side validation
        if (!this.validateForm(data)) {
            return;
        }

        // Добавляем CSRF токен
        if (!this.csrfToken) {
            this.showInlineError('Ошибка безопасности. Обновите страницу и попробуйте снова.');
            return;
        }
        data.csrf_token = this.csrfToken;

        // Disable button and show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '⏳ Отправка...';

        try {
            const response = await fetch(this.submitUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(data).toString()
            });

            const result = await response.json();

            if (response.ok && result.success) {
                // Отправка цели в Яндекс.Метрику
                if (typeof ym !== 'undefined') {
                    ym(106970869, 'reachGoal', 'form_submit');
                }
                // Обновляем CSRF токен для следующей отправки
                if (result.csrf_token) {
                    this.csrfToken = result.csrf_token;
                    const csrfInput = document.getElementById('csrf_token');
                    if (csrfInput) csrfInput.value = result.csrf_token;
                }
                this.showSuccessMessage();
                this.form.reset();
            } else {
                throw new Error(result.error || 'Ошибка отправки');
            }
        } catch (error) {
            this.showInlineError(error.message || 'Ошибка отправки. Позвоните нам!');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    },

    validateForm(data) {
        let isValid = true;

        if (!data.name || data.name.trim().length < 2 || data.name.trim().length > 50) {
            this.showInlineError('Пожалуйста, введите корректное имя (2-50 символов)');
            isValid = false;
        }

        // Валидация телефона - российский формат
        const phoneClean = data.phone.replace(/[^\d+]/g, '');
        const phoneValid = /^(\+7|8)\d{10}$/.test(phoneClean);
        if (!data.phone || !phoneValid) {
            this.showInlineError('Введите корректный номер телефона (например, +7 999 123-45-67)');
            isValid = false;
        }

        // Проверка чекбокса согласия
        const privacyCheckbox = document.getElementById('privacy');
        if (privacyCheckbox && !privacyCheckbox.checked) {
            this.showInlineError('Необходимо согласие на обработку персональных данных');
            isValid = false;
        }

        return isValid;
    },

    showSuccessMessage() {
        const successMsg = document.createElement('div');
        successMsg.className = 'form-message success';
        successMsg.innerHTML = `
            <div style="text-align: center; padding: 40px;">
                <svg style="width: 60px; height: 60px; color: #00D9FF; margin-bottom: 20px;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                <h3 style="font-size: 1.5rem; margin-bottom: 10px;">Спасибо за заявку!</h3>
                <p style="color: var(--color-text-secondary);">Я свяжусь с вами в ближайшее время</p>
            </div>
        `;

        this.form.innerHTML = '';
        this.form.appendChild(successMsg);
    },

    showInlineError(message) {
        let messageEl = this.form.querySelector('.form-message');
        if (!messageEl) {
            messageEl = document.createElement('div');
            messageEl.className = 'form-message';
            this.form.insertBefore(messageEl, this.form.firstChild);
        }

        messageEl.className = 'form-message error';
        messageEl.textContent = message;
        messageEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
    },

    clearMessages() {
        const messageEl = this.form.querySelector('.form-message');
        if (messageEl) {
            messageEl.remove();
        }
        this.form.querySelectorAll('.form-group.error').forEach(group => {
            group.classList.remove('error');
        });
    }
};

/**
 * Smooth Scroll Module
 */
const SmoothScroll = {
    init() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', (e) => {
                e.preventDefault();
                const targetId = anchor.getAttribute('href');
                
                if (targetId === '#') return;
                
                const target = document.querySelector(targetId);
                if (target) {
                    const offsetTop = target.offsetTop - 80;
                    window.scrollTo({
                        top: offsetTop,
                        behavior: 'smooth'
                    });
                }
            });
        });
    }
};

/**
 * Parallax Effect for Hero Section
 * Параллакс эффект с throttle для производительности
 */
const ParallaxEffect = {
    hero: null,
    shapes: null,
    
    init() {
        this.hero = document.querySelector('.hero');
        this.shapes = document.querySelectorAll('.floating-shape');
        
        if (!this.hero || !this.shapes.length) return;
        
        // Отключаем для prefers-reduced-motion
        if (prefersReducedMotion) return;
        
        this.addScrollListener();
    },
    
    addScrollListener() {
        const throttledParallax = throttle(() => {
            const scrolled = window.scrollY;
            const heroHeight = this.hero.offsetHeight;

            if (scrolled < heroHeight) {
                this.shapes.forEach((shape, index) => {
                    const speed = (index + 1) * 0.1;
                    shape.style.transform = `translateY(${scrolled * speed}px)`;
                });
            }
        }, 16);
        
        window.addEventListener('scroll', throttledParallax, { passive: true });
    }
};

// Инициализируем вместо глобального обработчика
ParallaxEffect.init();

/**
 * Reviews Slider Module
 * Слайдер отзывов с автоплеем и поддержкой touch/keyboard
 */
const ReviewsSlider = {
    track: null,
    cards: [],
    dotsContainer: null,
    prevBtn: null,
    nextBtn: null,
    currentIndex: 0,
    totalSlides: 0,
    visibleSlides: 3,
    autoPlayInterval: null,
    autoPlayDelay: 5000,
    isAnimating: false,
    resizeObserver: null,

    init() {
        this.track = document.querySelector('.reviews-track');
        this.cards = document.querySelectorAll('.review-card');
        this.dotsContainer = document.querySelector('.slider-dots');
        this.prevBtn = document.querySelector('.slider-prev');
        this.nextBtn = document.querySelector('.slider-next');
        this.totalSlides = this.cards.length;

        if (this.totalSlides === 0) {
            return;
        }

        this.updateVisibleSlides();
        this.createDots();
        this.addEventListeners();
        this.addResizeListener();
        this.startAutoPlay();
        this.updateSlider();
    },

    updateVisibleSlides() {
        if (window.innerWidth <= 768) {
            this.visibleSlides = 1;
        } else if (window.innerWidth <= 1024) {
            this.visibleSlides = 2;
        } else {
            this.visibleSlides = 3;
        }
    },

    addResizeListener() {
        // Используем debounce для resize
        const debouncedResize = debounce(() => {
            this.updateVisibleSlides();
            this.updateDots();
            this.currentIndex = 0;
            this.updateSlider();
        }, 250);
        
        window.addEventListener('resize', debouncedResize);
    },

    createDots() {
        if (!this.dotsContainer) return;
        
        this.dotsContainer.innerHTML = '';
        const totalDots = Math.max(1, this.totalSlides - this.visibleSlides + 1);
        
        for (let i = 0; i < totalDots; i++) {
            const dot = document.createElement('button');
            dot.className = 'slider-dot';
            dot.setAttribute('type', 'button');
            dot.setAttribute('aria-label', `Показать слайд ${i + 1}`);
            dot.setAttribute('aria-controls', 'reviews-track');
            if (i === 0) dot.classList.add('active');
            
            dot.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.goToSlide(i);
            });
            
            this.dotsContainer.appendChild(dot);
        }
    },

    addEventListeners() {
        if (this.prevBtn) {
            this.prevBtn.setAttribute('aria-label', 'Предыдущий отзыв');
            this.prevBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.prev();
                this.resetAutoPlay();
            });
        }

        if (this.nextBtn) {
            this.nextBtn.setAttribute('aria-label', 'Следующий отзыв');
            this.nextBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.next();
                this.resetAutoPlay();
            });
        }

        // Pause on hover
        const sliderContainer = document.querySelector('.reviews-slider-container');
        if (sliderContainer) {
            sliderContainer.addEventListener('mouseenter', () => this.stopAutoPlay());
            sliderContainer.addEventListener('mouseleave', () => this.startAutoPlay());
        }

        // Keyboard navigation - только когда слайдер в фокусе
        if (sliderContainer) {
            sliderContainer.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    this.prev();
                    this.resetAutoPlay();
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.next();
                    this.resetAutoPlay();
                }
            });
        }

        // Touch support
        this.initTouch();
    },

    initTouch() {
        const slider = document.querySelector('.reviews-slider');
        if (!slider) return;

        let touchStartX = 0;
        let touchEndX = 0;

        slider.addEventListener('touchstart', (e) => {
            touchStartX = e.changedTouches[0].screenX;
        }, { passive: true });

        slider.addEventListener('touchend', (e) => {
            touchEndX = e.changedTouches[0].screenX;
            this.handleSwipe(touchStartX, touchEndX);
        }, { passive: true });
    },

    handleSwipe(startX, endX) {
        const diff = startX - endX;
        if (Math.abs(diff) > 50) {
            if (diff > 0) {
                this.next();
            } else {
                this.prev();
            }
            this.resetAutoPlay();
        }
    },

    updateSlider() {
        if (!this.track || this.cards.length === 0) return;

        const cardWidth = this.cards[0].getBoundingClientRect().width;
        const gap = 30; // из CSS gap
        const offset = this.currentIndex * (cardWidth + gap);

        this.track.style.transform = `translateX(-${offset}px)`;
        this.updateDots();
    },

    goToSlide(index) {
        if (this.isAnimating) return;

        const maxIndex = Math.max(0, this.totalSlides - this.visibleSlides);
        this.currentIndex = Math.max(0, Math.min(index, maxIndex));

        this.isAnimating = true;
        setTimeout(() => {
            this.isAnimating = false;
        }, 600);

        this.updateSlider();
    },

    next() {
        if (this.isAnimating) return;
        
        const maxIndex = Math.max(0, this.totalSlides - this.visibleSlides);
        if (this.currentIndex >= maxIndex) {
            this.currentIndex = 0; // Циклически возвращаемся в начало
        } else {
            this.currentIndex++;
        }
        this.isAnimating = true;
        this.updateSlider();
        setTimeout(() => {
            this.isAnimating = false;
        }, 600);
    },

    prev() {
        if (this.isAnimating) return;
        
        const maxIndex = Math.max(0, this.totalSlides - this.visibleSlides);
        if (this.currentIndex <= 0) {
            this.currentIndex = maxIndex; // Циклически в конец
        } else {
            this.currentIndex--;
        }
        this.isAnimating = true;
        this.updateSlider();
        setTimeout(() => {
            this.isAnimating = false;
        }, 600);
    },

    updateDots() {
        const dots = this.dotsContainer.querySelectorAll('button');
        const maxIndex = Math.max(0, this.totalSlides - this.visibleSlides);
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === this.currentIndex);
        });
    },

    startAutoPlay() {
        this.stopAutoPlay();
        this.autoPlayInterval = setInterval(() => {
            this.next();
        }, this.autoPlayDelay);
    },

    stopAutoPlay() {
        if (this.autoPlayInterval) {
            clearInterval(this.autoPlayInterval);
            this.autoPlayInterval = null;
        }
    },

    resetAutoPlay() {
        this.startAutoPlay();
    }
};

/**
 * Heart Animation Module
 */
const HeartAnimation = {
    heartsContainer: null,

    init() {
        this.heartsContainer = document.getElementById('hearts-container');
        if (!this.heartsContainer) return;
        // Hearts are handled by CustomCursor module
    }
};

/**
 * FAQ Accordion Module
 * Аккордеон для вопросов/ответов с ARIA
 */
const FAQ = {
    items: null,

    init() {
        this.items = document.querySelectorAll('.faq-item');
        if (!this.items.length) return;
        this.addEventListeners();
    },

    addEventListeners() {
        this.items.forEach((item, index) => {
            const question = item.querySelector('.faq-question');
            const answer = item.querySelector('.faq-answer');
            
            if (question && answer) {
                // Устанавливаем ARIA атрибуты
                const questionId = `faq-question-${index}`;
                const answerId = `faq-answer-${index}`;
                
                question.setAttribute('id', questionId);
                question.setAttribute('aria-controls', answerId);
                question.setAttribute('aria-expanded', 'false');
                question.setAttribute('role', 'button');
                question.setAttribute('tabindex', '0');
                
                answer.setAttribute('id', answerId);
                answer.setAttribute('aria-labelledby', questionId);
                answer.setAttribute('role', 'region');
                
                // Обработчик клика
                question.addEventListener('click', () => this.toggleItem(item));
                
                // Обработчик клавиатуры (Enter и Space)
                question.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.toggleItem(item);
                    }
                });
            }
        });
    },

    toggleItem(item) {
        const question = item.querySelector('.faq-question');
        const isExpanded = question.getAttribute('aria-expanded') === 'true';

        // Закрываем все остальные
        this.items.forEach(otherItem => {
            if (otherItem !== item) {
                otherItem.classList.remove('active');
                otherItem.querySelector('.faq-question').setAttribute('aria-expanded', 'false');
            }
        });

        // Переключаем текущий
        item.classList.toggle('active');
        question.setAttribute('aria-expanded', (!isExpanded).toString());
    }
};

/**
 * Yandex.Metrika Goals Module
 * Трекинг целей Яндекс.Метрики
 */
const MetrikaGoals = {
    metrikaId: 106970869,
    
    init() {
        if (typeof ym === 'undefined') {
            return;
        }
        this.trackPhoneClicks();
        this.trackNavClicks();
        this.trackFormSubmits();
    },

    trackPhoneClicks() {
        const phoneLinks = document.querySelectorAll('a[href^="tel:"]');
        phoneLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (typeof ym !== 'undefined') {
                    ym(this.metrikaId, 'reachGoal', 'phone_click');
                }
            });
        });
    },

    trackNavClicks() {
        const navLinks = document.querySelectorAll('.nav-menu a[href^="#"]');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (typeof ym !== 'undefined') {
                    const section = link.getAttribute('href').substring(1);
                    ym(this.metrikaId, 'reachGoal', 'nav_click', { section });
                }
            });
        });
    },
    
    trackFormSubmits() {
        // Формы уже трекаются в ContactForm модуле
    }
};

/**
 * Service Worker Handler Module
 * Регистрация и обновление Service Worker
 */
const ServiceWorkerHandler = {
    init() {
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', async () => {
                try {
                    const registration = await navigator.serviceWorker.register('/sw.js', {
                        scope: '/'
                    });
                    console.log('[SW] Service Worker registered:', registration.scope);

                    // Check for updates
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        if (newWorker) {
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    this.showUpdateNotification();
                                }
                            });
                        }
                    });
                } catch (error) {
                    console.error('[SW] Service Worker registration failed:', error);
                }
            });
        }
    },

    showUpdateNotification() {
        const notification = document.createElement('div');
        notification.className = 'sw-update-notification';
        notification.innerHTML = `
            <div style="position: fixed; bottom: 20px; right: 20px; background: var(--color-primary); color: white; padding: 16px 24px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 9999; display: flex; align-items: center; gap: 12px;">
                <span>🔄 Доступна новая версия сайта!</span>
                <button onclick="window.location.reload()" style="background: white; color: var(--color-primary); border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer; font-weight: 600;">Обновить</button>
                <button onclick="this.closest('.sw-update-notification').remove()" style="background: transparent; color: white; border: 1px solid white; padding: 8px 12px; border-radius: 4px; cursor: pointer;">✕</button>
            </div>
        `;
        document.body.appendChild(notification);
    }
};
