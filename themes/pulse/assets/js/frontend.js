/**
 * Pulse Theme - Frontend JavaScript
 */

document.documentElement.dataset.pulsePublicReady = "true";
document.addEventListener('DOMContentLoaded', function() {
    // ============================================================
    // 1. MOBILE MENU TOGGLE
    // ============================================================
    const toggle = document.querySelector('.pulse-mobile-toggle');
    const mobileNav = document.querySelector('.pulse-nav-mobile');
    
    if (toggle && mobileNav) {
        toggle.addEventListener('click', function() {
            this.classList.toggle('active');
            mobileNav.classList.toggle('open');
            document.body.style.overflow = mobileNav.classList.contains('open') ? 'hidden' : '';
        });
        
        // Close mobile menu when a link is clicked
        mobileNav.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                toggle.classList.remove('active');
                mobileNav.classList.remove('open');
                document.body.style.overflow = '';
            });
        });
    }
    
    // ============================================================
    // 2. SMOOTH SCROLL
    // ============================================================
    document.querySelectorAll('a[href^="#"]').forEach(function(anchor) {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth' });
            }
        });
    });
    
    // ============================================================
    // 3. SCROLL ANIMATIONS (Intersection Observer)
    // ============================================================
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(function(entries) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-up');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });
        
        document.querySelectorAll('.animate-fade-up, .animate-fade-in, .animate-slide-left').forEach(function(el) {
            observer.observe(el);
        });
    }
    
    // ============================================================
    // 4. HERO SEARCH (with domain validation)
    // ============================================================
    const searchForm = document.querySelector('.pulse-hero-search');
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            const input = this.querySelector('input[type="text"]');
            if (input && input.value.trim().length < 2) {
                e.preventDefault();
                input.style.borderColor = 'var(--pulse-accent)';
                setTimeout(function() {
                    input.style.borderColor = '';
                }, 2000);
            }
        });
    }
    
    // ============================================================
    // 5. HEADER SHADOW ON SCROLL
    // ============================================================
    const header = document.querySelector('.pulse-site-header');
    if (header && !document.body.classList.contains('pulse-header-transparent')) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.style.boxShadow = 'var(--pulse-shadow-md)';
            } else {
                header.style.boxShadow = 'none';
            }
        });
    }
});