/**
 * Zumba Trainer Website - Interactive JavaScript
 * Alexander Melnikov
 */

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
});

/**
 * Custom Cursor Module
 */
const CustomCursor = {
    cursor: null,
    follower: null,
    heartsContainer: null,
    lastHeartTime: 0,
    
    init() {
        if (window.matchMedia('(pointer: fine)').matches) {
            this.heartsContainer = document.getElementById('hearts-container');
            this.createCursor();
            this.addEventListeners();
        }
    },
    
    createCursor() {
        this.cursor = document.createElement('div');
        this.cursor.className = 'cursor';
        this.follower = document.createElement('div');
        this.follower.className = 'cursor-follower';
        document.body.appendChild(this.cursor);
        document.body.appendChild(this.follower);
    },
    
    addEventListeners() {
        document.addEventListener('mousemove', (e) => {
            this.cursor.style.left = e.clientX + 'px';
            this.cursor.style.top = e.clientY + 'px';
            
            setTimeout(() => {
                this.follower.style.left = e.clientX + 'px';
                this.follower.style.top = e.clientY + 'px';
            }, 50);
            
            // Create hearts on mouse move
            this.createHeart(e.clientX, e.clientY);
        });
        
        // Hover effects
        const hoverElements = document.querySelectorAll('a, button, .program-card, .review-card');
        hoverElements.forEach(el => {
            el.addEventListener('mouseenter', () => this.follower.classList.add('hover'));
            el.addEventListener('mouseleave', () => this.follower.classList.remove('hover'));
        });
        
        // Click effect - burst of hearts
        document.addEventListener('click', (e) => {
            this.createHeartBurst(e.clientX, e.clientY);
        });
    },
    
    createHeart(x, y) {
        const now = Date.now();
        // Limit heart creation rate
        if (now - this.lastHeartTime < 100) return;
        
        this.lastHeartTime = now;
        
        const heart = document.createElement('div');
        heart.className = 'heart-particle';
        heart.innerHTML = '❤';
        heart.style.left = (x + (Math.random() - 0.5) * 40) + 'px';
        heart.style.top = (y + (Math.random() - 0.5) * 40) + 'px';
        heart.style.fontSize = (12 + Math.random() * 16) + 'px';
        heart.style.color = this.getRandomHeartColor();
        
        this.heartsContainer.appendChild(heart);
        
        // Remove heart after animation
        setTimeout(() => {
            heart.remove();
        }, 3000);
    },
    
    createHeartBurst(x, y) {
        for (let i = 0; i < 8; i++) {
            setTimeout(() => {
                const offsetX = (Math.random() - 0.5) * 100;
                const offsetY = (Math.random() - 0.5) * 100;
                this.createHeart(x + offsetX, y + offsetY);
            }, i * 50);
        }
    },
    
    getRandomHeartColor() {
        const colors = [
            '#FF2D75', // Primary pink
            '#FF5B95', // Light pink
            '#FFB800', // Gold
            '#FF6B6B', // Coral
            '#E61E5F', // Dark pink
        ];
        return colors[Math.floor(Math.random() * colors.length)];
    }
};

/**
 * Navigation Module
 */
const Navigation = {
    navbar: null,
    toggle: null,
    menu: null,
    
    init() {
        this.navbar = document.querySelector('.navbar');
        this.toggle = document.querySelector('.nav-toggle');
        this.menu = document.querySelector('.nav-menu');
        
        this.addScrollListener();
        this.addToggleListener();
        this.addActiveLinkListener();
    },
    
    addScrollListener() {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 100) {
                this.navbar.classList.add('scrolled');
            } else {
                this.navbar.classList.remove('scrolled');
            }
        });
    },
    
    addToggleListener() {
        this.toggle.addEventListener('click', () => {
            this.toggle.classList.toggle('active');
            this.menu.classList.toggle('active');
        });
        
        // Close menu on link click
        this.menu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                this.toggle.classList.remove('active');
                this.menu.classList.remove('active');
            });
        });
    },
    
    addActiveLinkListener() {
        const sections = document.querySelectorAll('section[id]');
        
        window.addEventListener('scroll', () => {
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
        });
    }
};

/**
 * Scroll Animations Module
 */
const ScrollAnimations = {
    elements: null,
    
    init() {
        this.elements = document.querySelectorAll('.fade-in, .program-card, .review-card, .stat-item');
        this.addScrollListener();
        this.checkVisibility(); // Check on load
    },
    
    addScrollListener() {
        window.addEventListener('scroll', () => {
            this.checkVisibility();
        });
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
 */
const CounterAnimation = {
    counters: null,
    animated: false,
    
    init() {
        this.counters = document.querySelectorAll('.stat-number');
        this.addScrollListener();
    },
    
    addScrollListener() {
        const statsSection = document.querySelector('.stats');
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting && !this.animated) {
                    this.animateCounters();
                    this.animated = true;
                }
            });
        }, { threshold: 0.5 });
        
        if (statsSection) {
            observer.observe(statsSection);
        }
    },
    
    animateCounters() {
        this.counters.forEach(counter => {
            const target = parseInt(counter.getAttribute('data-target'));
            const duration = 2000;
            const increment = target / (duration / 16);
            let current = 0;
            
            const updateCounter = () => {
                current += increment;
                if (current < target) {
                    counter.textContent = Math.floor(current).toLocaleString();
                    requestAnimationFrame(updateCounter);
                } else {
                    counter.textContent = target.toLocaleString() + '+';
                }
            };
            
            updateCounter();
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
 */
const ContactForm = {
    form: null,
    
    init() {
        this.form = document.getElementById('contactForm');
        this.addSubmitListener();
    },
    
    addSubmitListener() {
        this.form.addEventListener('submit', (e) => {
            e.preventDefault();
            
            // Get form values
            const formData = new FormData(this.form);
            const data = Object.fromEntries(formData);
            
            // Simple validation
            if (!this.validateForm(data)) {
                return;
            }
            
            // Show success message
            this.showSuccessMessage();
            
            // Reset form
            this.form.reset();
            
            // Log data (in production, send to server)
            console.log('Form submitted:', data);
        });
    },
    
    validateForm(data) {
        if (!data.name || data.name.trim().length < 2) {
            this.showError('Пожалуйста, введите корректное имя');
            return false;
        }
        
        if (!data.phone || data.phone.trim().length < 10) {
            this.showError('Пожалуйста, введите корректный номер телефона');
            return false;
        }
        
        return true;
    },
    
    showSuccessMessage() {
        const successMsg = document.createElement('div');
        successMsg.className = 'success-message';
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
    
    showError(message) {
        alert(message);
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
 */
window.addEventListener('scroll', () => {
    const hero = document.querySelector('.hero');
    const scrolled = window.scrollY;

    if (scrolled < hero.offsetHeight) {
        const shapes = document.querySelectorAll('.floating-shape');
        shapes.forEach((shape, index) => {
            const speed = (index + 1) * 0.1;
            shape.style.transform = `translateY(${scrolled * speed}px)`;
        });
    }
});
