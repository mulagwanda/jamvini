/* ============================================
   JAMVINI FRONTEND - COMPLETE SCRIPTS
   Version: 1.0.0
   Purpose: All public-facing interactions
   ============================================ */

// ============================================
// NAMESPACE
// ============================================
const JamViniFront = {
    init() {
        this.navigation();
        this.mobileMenu();
        this.smoothScroll();
        this.scrollAnimations();
        this.faqAccordion();
        this.aiChatWidget();
        this.pricingToggle();
        this.counterAnimation();
        this.formValidation();
        this.forumInteractions();
        this.copyCodeBlocks();
        console.log('🚀 JamVini Frontend initialized');
    },

    // ============================================
    // NAVIGATION (Scroll Effect)
    // ============================================
    navigation() {
        const nav = document.querySelector('.jv-nav');
        if (!nav) return;

        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                nav.classList.add('scrolled');
            } else {
                nav.classList.remove('scrolled');
            }
        });
    },

    // ============================================
    // MOBILE MENU
    // ============================================
    mobileMenu() {
        const toggleBtn = document.querySelector('.jv-mobile-toggle');
        const mobileMenu = document.querySelector('.jv-mobile-menu');

        if (!toggleBtn || !mobileMenu) return;

        toggleBtn.addEventListener('click', () => {
            mobileMenu.classList.toggle('open');
            document.body.style.overflow = mobileMenu.classList.contains('open') ? 'hidden' : '';
        });

        // Close on link click
        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenu.classList.remove('open');
                document.body.style.overflow = '';
            });
        });
    },

    // ============================================
    // SMOOTH SCROLL (Anchor Links)
    // ============================================
    smoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href === '#') return;

                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start',
                        inline: 'nearest'
                    });
                }
            });
        });
    },

    // ============================================
    // SCROLL ANIMATIONS (Intersection Observer)
    // ============================================
    scrollAnimations() {
        const elements = document.querySelectorAll('.jv-animate-on-scroll');
        if (elements.length === 0) return;

        // Also animate cards and sections
        const allAnimated = document.querySelectorAll(
            '.jv-project-card, .jv-pricing-card, .jv-testimonial-card, .jv-pain-card, .jv-step-card, .jv-persona-card'
        );
        
        const combined = new Set([...elements, ...allAnimated]);
        
        combined.forEach(el => {
            el.classList.add('jv-animate-on-scroll');
        });

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('jv-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        combined.forEach(el => observer.observe(el));
    },

    // ============================================
    // FAQ ACCORDION
    // ============================================
    faqAccordion() {
        document.querySelectorAll('.jv-faq-item').forEach(item => {
            item.addEventListener('click', () => {
                // Close other items
                document.querySelectorAll('.jv-faq-item.active').forEach(active => {
                    if (active !== item) {
                        active.classList.remove('active');
                    }
                });
                
                // Toggle current
                item.classList.toggle('active');
            });
        });
    },

    
    // ============================================
    // PRICING TOGGLE (if monthly/annual toggle needed)
    // ============================================
    pricingToggle() {
        const toggle = document.querySelector('.jv-pricing-toggle');
        if (!toggle) return;

        const monthlyPrices = document.querySelectorAll('.jv-price-monthly');
        const annualPrices = document.querySelectorAll('.jv-price-annual');

        toggle.addEventListener('change', () => {
            const isAnnual = toggle.checked;
            monthlyPrices.forEach(el => el.style.display = isAnnual ? 'none' : '');
            annualPrices.forEach(el => el.style.display = isAnnual ? '' : 'none');
        });
    },

    // ============================================
    // COUNTER ANIMATION (Number counting up)
    // ============================================
    counterAnimation() {
        const counters = document.querySelectorAll('.jv-counter');
        if (counters.length === 0) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const counter = entry.target;
                    const target = parseInt(counter.getAttribute('data-target') || counter.textContent);
                    const duration = 2000; // 2 seconds
                    const frameRate = 30;
                    const frames = duration / (1000 / frameRate);
                    const increment = target / frames;
                    let current = 0;

                    const updateCounter = () => {
                        current += increment;
                        if (current < target) {
                            counter.textContent = Math.ceil(current).toLocaleString();
                            requestAnimationFrame(() => setTimeout(updateCounter, 1000 / frameRate));
                        } else {
                            counter.textContent = target.toLocaleString();
                        }
                    };

                    updateCounter();
                    observer.unobserve(counter);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(counter => observer.observe(counter));
    },

    // ============================================
    // FORM VALIDATION
    // ============================================
    formValidation() {
        document.querySelectorAll('.jv-validate').forEach(form => {
            form.addEventListener('submit', (e) => {
                let isValid = true;
                
                // Clear previous errors
                form.querySelectorAll('.jv-form-error').forEach(el => el.remove());
                form.querySelectorAll('.jv-input-error').forEach(el => el.classList.remove('jv-input-error'));

                // Validate required fields
                form.querySelectorAll('[required]').forEach(input => {
                    if (!input.value.trim()) {
                        isValid = false;
                        this.showFormError(input, 'This field is required');
                    }
                });

                // Validate email
                form.querySelectorAll('input[type="email"]').forEach(input => {
                    if (input.value.trim() && !this.isValidEmail(input.value)) {
                        isValid = false;
                        this.showFormError(input, 'Please enter a valid email address');
                    }
                });

                // Validate password match
                const password = form.querySelector('input[name="password"]');
                const confirm = form.querySelector('input[name="password_confirmation"]');
                if (password && confirm && password.value !== confirm.value) {
                    isValid = false;
                    this.showFormError(confirm, 'Passwords do not match');
                }

                if (!isValid) {
                    e.preventDefault();
                }
            });
        });
    },

    showFormError(input, message) {
        input.classList.add('jv-input-error');
        const error = document.createElement('div');
        error.className = 'jv-form-error';
        error.style.cssText = 'color: var(--jv-danger); font-size: 0.8rem; margin-top: 4px;';
        error.textContent = message;
        input.parentNode.appendChild(error);
    },

    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    },

    // ============================================
    // FORUM INTERACTIONS
    // ============================================
    forumInteractions() {
        // Like button
        document.querySelectorAll('.jv-post-action.jv-like').forEach(btn => {
            btn.addEventListener('click', () => {
                btn.classList.toggle('jv-liked');
                const count = btn.querySelector('.jv-like-count');
                if (count) {
                    const current = parseInt(count.textContent);
                    count.textContent = btn.classList.contains('jv-liked') ? current + 1 : current - 1;
                }
            });
        });

        // Mark as solution
        document.querySelectorAll('.jv-post-action.jv-solution-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const card = btn.closest('.jv-post-card');
                if (card) {
                    card.classList.toggle('jv-solution');
                    btn.classList.toggle('jv-solution');
                    btn.textContent = btn.classList.contains('jv-solution') ? '✅ Solution' : 'Mark as Solution';
                }
            });
        });

        // Quote reply
        document.querySelectorAll('.jv-post-action.jv-quote').forEach(btn => {
            btn.addEventListener('click', () => {
                const card = btn.closest('.jv-post-card');
                const body = card?.querySelector('.jv-post-body');
                const author = card?.querySelector('.jv-post-author');
                const textarea = document.querySelector('.jv-reply-form textarea');
                
                if (textarea && body && author) {
                    const quote = `> ${author.textContent} wrote:\n> ${body.textContent.trim().substring(0, 200)}...\n\n`;
                    textarea.value = quote + textarea.value;
                    textarea.focus();
                    textarea.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    },

    // ============================================
    // COPY CODE BLOCKS
    // ============================================
    copyCodeBlocks() {
        document.querySelectorAll('.jv-post-body pre').forEach(block => {
            // Add copy button
            const copyBtn = document.createElement('button');
            copyBtn.className = 'jv-copy-btn';
            copyBtn.innerHTML = '📋 Copy';
            copyBtn.style.cssText = `
                position: absolute; top: 8px; right: 8px;
                background: rgba(255,255,255,0.1); color: white;
                border: 1px solid rgba(255,255,255,0.2); border-radius: 6px;
                padding: 4px 10px; font-size: 12px; cursor: pointer;
                transition: all 0.2s;
            `;
            
            block.style.position = 'relative';
            block.appendChild(copyBtn);

            copyBtn.addEventListener('click', () => {
                const code = block.textContent.replace('📋 Copy', '').trim();
                navigator.clipboard.writeText(code).then(() => {
                    copyBtn.textContent = '✅ Copied!';
                    setTimeout(() => copyBtn.textContent = '📋 Copy', 2000);
                }).catch(() => {
                    copyBtn.textContent = '❌ Failed';
                    setTimeout(() => copyBtn.textContent = '📋 Copy', 2000);
                });
            });
        });
    },

    // ============================================
    // UTILITIES
    // ============================================
    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    debounce(func, wait = 300) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    throttle(func, limit = 300) {
        let inThrottle;
        return function(...args) {
            if (!inThrottle) {
                func.apply(this, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        };
    },

    // ============================================
    // LAZY LOADING IMAGES
    // ============================================
    lazyLoadImages() {
        if ('loading' in HTMLImageElement.prototype) return;

        const images = document.querySelectorAll('img[data-src]');
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.getAttribute('data-src');
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => observer.observe(img));
    },

    // ============================================
    // BACK TO TOP BUTTON
    // ============================================
    backToTop() {
        const btn = document.createElement('button');
        btn.innerHTML = '↑';
        btn.className = 'jv-back-to-top';
        btn.style.cssText = `
            position: fixed; bottom: 30px; left: 30px;
            width: 50px; height: 50px; border-radius: 50%;
            background: var(--jv-gradient-primary); color: white;
            border: none; font-size: 24px; cursor: pointer;
            box-shadow: 0 4px 15px rgba(108,92,231,0.3);
            display: none; z-index: 400;
            transition: all 0.3s;
        `;
        
        document.body.appendChild(btn);

        window.addEventListener('scroll', () => {
            btn.style.display = window.scrollY > 500 ? 'block' : 'none';
        });

        btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    },
};

// ============================================
// INITIALIZE ON DOM READY
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    JamViniFront.init();
    JamViniFront.backToTop();
    JamViniFront.lazyLoadImages();
});

// ============================================
// EXPORT FOR GLOBAL USE
// ============================================
window.JamViniFront = JamViniFront;