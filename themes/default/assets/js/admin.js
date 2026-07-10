/* ============================================
   JAMVINI ADMIN - COMPLETE SCRIPTS
   Version: 1.0.0
   Purpose: All backend dashboard interactions
   Dependencies: Chart.js, SweetAlert2
   ============================================ */

// ============================================
// NAMESPACE
// ============================================
const JamViniAdmin = {
    moneyCurrency: window.JamViniConfig?.currency || 'TZS',
    moneyDecimals: Number(window.JamViniConfig?.currencyDecimals ?? 0),

    init() {
        this.sidebar();
        this.dropdowns();
        this.modals();
        this.tabs();
        this.toasts();
        this.fileUpload();
        this.tables();
        this.charts();
        this.confirmations();
        this.autoCloseAlerts();
        this.tooltips();
        console.log('🚀 JamVini Admin initialized');
    },

    // ============================================
    // SIDEBAR
    // ============================================
    sidebar() {
        const toggleBtn = document.querySelector('.sidebar-toggle');
        const sidebar = document.querySelector('.admin-sidebar');
        const mainContent = document.querySelector('.admin-main');
        const overlay = document.createElement('div');

        if (!toggleBtn || !sidebar) return;

        // Create mobile overlay
        overlay.className = 'sidebar-overlay';
        overlay.style.cssText = `
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5); z-index: 99; display: none;
        `;
        document.body.appendChild(overlay);

        // Toggle sidebar
        toggleBtn.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                sidebar.classList.toggle('open');
                overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
            } else {
                sidebar.classList.toggle('collapsed');
            }
        });

        // Close on overlay click (mobile)
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.style.display = 'none';
        });

        // Close on window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 768) {
                sidebar.classList.remove('open');
                overlay.style.display = 'none';
            }
        });

        // Set active nav based on current URL
        const currentPath = window.location.pathname;
        document.querySelectorAll('.nav-link').forEach(link => {
            if (link.getAttribute('href') === currentPath) {
                link.classList.add('active');
            }
        });
    },

    // ============================================
    // DROPDOWNS
    // ============================================
    dropdowns() {
        document.addEventListener('click', (e) => {
            // Close all dropdowns when clicking outside
            document.querySelectorAll('.dropdown-menu.show').forEach(menu => {
                if (!menu.closest('.dropdown').contains(e.target)) {
                    menu.classList.remove('show');
                }
            });
        });

        // Toggle dropdown on button click
        document.querySelectorAll('.dropdown').forEach(dropdown => {
            const trigger = dropdown.querySelector('.dropdown-trigger, .topbar-btn, .user-dropdown');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (trigger && menu) {
                trigger.addEventListener('click', (e) => {
                    e.stopPropagation();
                    menu.classList.toggle('show');
                });
            }
        });
    },

    // ============================================
    // MODALS
    // ============================================
    modals() {
        // Open modal
        document.querySelectorAll('[data-modal]').forEach(btn => {
            btn.addEventListener('click', () => {
                const modalId = btn.getAttribute('data-modal');
                const modal = document.getElementById(modalId);
                if (modal) this.openModal(modal);
            });
        });

        // Close modal
        document.querySelectorAll('.modal-close, .modal-overlay').forEach(el => {
            el.addEventListener('click', (e) => {
                if (e.target === el || el.classList.contains('modal-close')) {
                    const modal = el.closest('.modal-overlay');
                    if (modal) this.closeModal(modal);
                }
            });
        });

        // Close on Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay').forEach(modal => {
                    this.closeModal(modal);
                });
            }
        });
    },

    openModal(modal) {
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        // Focus first input
        const firstInput = modal.querySelector('input, select, textarea');
        if (firstInput) setTimeout(() => firstInput.focus(), 100);
    },

    closeModal(modal) {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    },

    // ============================================
    // TABS
    // ============================================
    tabs() {
        document.querySelectorAll('.tabs').forEach(tabGroup => {
            const tabs = tabGroup.querySelectorAll('.tab');
            const contents = tabGroup.parentElement.querySelectorAll('.tab-content');

            tabs.forEach((tab, index) => {
                tab.addEventListener('click', () => {
                    tabs.forEach(t => t.classList.remove('active'));
                    contents.forEach(c => c.classList.remove('active'));
                    
                    tab.classList.add('active');
                    if (contents[index]) contents[index].classList.add('active');
                });
            });
        });
    },

    // ============================================
    // TOAST NOTIFICATIONS
    // ============================================
    toasts() {
        // Check for session flash messages (Laravel)
        const flashContainer = document.querySelector('.toast-container');
        if (!flashContainer) {
            const container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
    },

    showToast(title, message, type = 'success', duration = 4000) {
        const container = document.querySelector('.toast-container');
        if (!container) return;

        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };

        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <span class="toast-icon">${icons[type] || icons.info}</span>
            <div class="toast-content">
                <div class="toast-title">${title}</div>
                ${message ? `<div class="toast-message">${message}</div>` : ''}
            </div>
            <button class="toast-close">&times;</button>
        `;

        // Close button
        toast.querySelector('.toast-close').addEventListener('click', () => {
            toast.remove();
        });

        container.appendChild(toast);

        // Auto remove
        setTimeout(() => {
            toast.style.animation = 'slideIn 0.3s ease reverse';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    // ============================================
    // FILE UPLOAD (Drag & Drop)
    // ============================================
    fileUpload() {
        document.querySelectorAll('.file-upload').forEach(dropZone => {
            const input = dropZone.querySelector('input[type="file"]');
            if (!input) return;

            // Click to browse
            dropZone.addEventListener('click', () => input.click());

            // Drag events
            ['dragenter', 'dragover'].forEach(event => {
                dropZone.addEventListener(event, (e) => {
                    e.preventDefault();
                    dropZone.classList.add('dragover');
                });
            });

            ['dragleave', 'drop'].forEach(event => {
                dropZone.addEventListener(event, (e) => {
                    e.preventDefault();
                    dropZone.classList.remove('dragover');
                });
            });

            // Drop files
            dropZone.addEventListener('drop', (e) => {
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    input.files = files;
                    this.handleFileSelect(files[0], dropZone);
                }
            });

            // File selected via browse
            input.addEventListener('change', () => {
                if (input.files.length > 0) {
                    this.handleFileSelect(input.files[0], dropZone);
                }
            });
        });
    },

    handleFileSelect(file, dropZone) {
        const infoEl = dropZone.querySelector('.file-info');
        const sizeMB = (file.size / 1024 / 1024).toFixed(2);
        
        if (infoEl) {
            infoEl.innerHTML = `
                <span style="color: var(--jv-success); font-weight: 500;">
                    📄 ${file.name} (${sizeMB} MB)
                </span>
            `;
        }
    },

    // ============================================
    // TABLES (Search & Sort)
    // ============================================
    tables() {
        document.querySelectorAll('.table-search').forEach(searchInput => {
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase();
                const table = searchInput.closest('.card')?.querySelector('table');
                if (!table) return;

                table.querySelectorAll('tbody tr').forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(query) ? '' : 'none';
                });
            });
        });
    },

    // ============================================
    // CHARTS
    // ============================================
    charts() {
        this.initRevenueChart();
        this.initUsageChart();
        this.initProjectChart();
    },

    initRevenueChart() {
        const canvas = document.getElementById('revenueChart');
        if (!canvas) return;

        new Chart(canvas, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue (' + this.moneyCurrency + ')',
                    data: [120000, 190000, 250000, 310000, 420000, 580000],
                    borderColor: '#6C5CE7',
                    backgroundColor: 'rgba(108,92,231,0.1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true,
                    pointBackgroundColor: '#6C5CE7',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: value => this.formatCurrency(value)
                        }
                    }
                }
            }
        });
    },

    initUsageChart() {
        const canvas = document.getElementById('usageChart');
        if (!canvas) return;

        new Chart(canvas, {
            type: 'doughnut',
            data: {
                labels: ['PHP Projects', 'Python Projects', 'JavaScript Projects', 'Java Projects'],
                datasets: [{
                    data: [45, 25, 20, 10],
                    backgroundColor: ['#6C5CE7', '#00B894', '#F39C12', '#0984E3'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { padding: 20, usePointStyle: true }
                    }
                }
            }
        });
    },

    initProjectChart() {
        const canvas = document.getElementById('projectChart');
        if (!canvas) return;

        new Chart(canvas, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Projects Generated',
                    data: [12, 19, 15, 22, 28, 35, 18],
                    backgroundColor: 'rgba(108,92,231,0.8)',
                    borderRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    },

    // ============================================
    // CONFIRMATIONS (SweetAlert2)
    // ============================================
    confirmations() {
        document.querySelectorAll('[data-confirm]').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const message = btn.getAttribute('data-confirm') || 'Are you sure?';
                const title = btn.getAttribute('data-title') || 'Confirm Action';
                const confirmText = btn.getAttribute('data-confirm-text') || 'Yes';
                const cancelText = btn.getAttribute('data-cancel-text') || 'Cancel';
                const danger = btn.hasAttribute('data-danger');

                Swal.fire({
                    title: title,
                    text: message,
                    icon: danger ? 'warning' : 'question',
                    showCancelButton: true,
                    confirmButtonColor: danger ? '#E17055' : '#6C5CE7',
                    cancelButtonColor: '#64748B',
                    confirmButtonText: confirmText,
                    cancelButtonText: cancelText,
                    customClass: {
                        popup: 'jamvini-swal',
                        confirmButton: 'jamvini-swal-confirm',
                        cancelButton: 'jamvini-swal-cancel',
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // If it's a form, submit it
                        const form = btn.closest('form');
                        if (form) {
                            form.submit();
                        } else {
                            // Redirect if href exists
                            const href = btn.getAttribute('href');
                            if (href && href !== '#') {
                                window.location.href = href;
                            }
                        }
                    }
                });
            });
        });
    },

    // ============================================
    // AUTO-CLOSE ALERTS
    // ============================================
    autoCloseAlerts() {
        document.querySelectorAll('.alert').forEach(alert => {
            // Add close button handler
            const closeBtn = alert.querySelector('.alert-close');
            if (closeBtn) {
                closeBtn.addEventListener('click', () => {
                    alert.style.animation = 'fadeIn 0.3s ease reverse';
                    setTimeout(() => alert.remove(), 300);
                });
            }

            // Auto close after 5 seconds
            setTimeout(() => {
                if (alert.parentElement) {
                    alert.style.animation = 'fadeIn 0.3s ease reverse';
                    setTimeout(() => alert.remove(), 300);
                }
            }, 5000);
        });
    },

    // ============================================
    // TOOLTIPS
    // ============================================
    tooltips() {
        document.querySelectorAll('[data-tooltip]').forEach(el => {
            el.addEventListener('mouseenter', (e) => {
                const tooltip = document.createElement('div');
                tooltip.className = 'jamvini-tooltip';
                tooltip.textContent = el.getAttribute('data-tooltip');
                tooltip.style.cssText = `
                    position: absolute;
                    background: var(--jv-gray-900);
                    color: white;
                    padding: 6px 12px;
                    border-radius: var(--jv-radius-sm);
                    font-size: 12px;
                    z-index: 999;
                    white-space: nowrap;
                    pointer-events: none;
                `;
                document.body.appendChild(tooltip);

                const rect = el.getBoundingClientRect();
                tooltip.style.top = `${rect.top - tooltip.offsetHeight - 8}px`;
                tooltip.style.left = `${rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2)}px`;

                el.addEventListener('mouseleave', () => tooltip.remove(), { once: true });
            });
        });
    },

    // ============================================
    // AJAX HELPERS
    // ============================================
    async ajaxGet(url) {
        try {
            const response = await fetch(url, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                }
            });
            return await response.json();
        } catch (error) {
            console.error('AJAX Error:', error);
            this.showToast('Error', 'Something went wrong. Please try again.', 'error');
            throw error;
        }
    },

    async ajaxPost(url, data) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                },
                body: JSON.stringify(data),
            });
            return await response.json();
        } catch (error) {
            console.error('AJAX Error:', error);
            this.showToast('Error', 'Something went wrong. Please try again.', 'error');
            throw error;
        }
    },

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    },

    // ============================================
    // NUMBER FORMATTING
    // ============================================
    formatNumber(num) {
        if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
        if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
        return num.toLocaleString();
    },

    formatCurrency(amount, currency = this.moneyCurrency) {
        return currency + ' ' + Number(amount || 0).toLocaleString(undefined, {
            minimumFractionDigits: this.moneyDecimals,
            maximumFractionDigits: this.moneyDecimals
        });
    },

    // ============================================
    // DATE HELPERS
    // ============================================
    timeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const seconds = Math.floor((now - date) / 1000);

        const intervals = [
            { label: 'year', seconds: 31536000 },
            { label: 'month', seconds: 2592000 },
            { label: 'week', seconds: 604800 },
            { label: 'day', seconds: 86400 },
            { label: 'hour', seconds: 3600 },
            { label: 'minute', seconds: 60 },
        ];

        for (const interval of intervals) {
            const count = Math.floor(seconds / interval.seconds);
            if (count >= 1) {
                return count === 1 ? `1 ${interval.label} ago` : `${count} ${interval.label}s ago`;
            }
        }

        return 'Just now';
    },

    formatDate(dateString, format = 'short') {
        const date = new Date(dateString);
        const options = {
            short: { month: 'short', day: 'numeric', year: 'numeric' },
            long: { month: 'long', day: 'numeric', year: 'numeric' },
            withTime: { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' },
        };

        return date.toLocaleDateString('en-US', options[format] || options.short);
    },
};

// ============================================
// INITIALIZE ON DOM READY
// ============================================
document.addEventListener('DOMContentLoaded', () => {
    JamViniAdmin.init();
});

// ============================================
// EXPORT FOR GLOBAL USE
// ============================================
window.JamViniAdmin = JamViniAdmin;


// Check notification count every 30 seconds
// Only check notifications on user pages
function updateNotificationCount() {
    // Skip if on admin pages
    if (window.location.pathname.startsWith('/admin')) return;
    
    fetch('/user/notifications/count')
        .then(res => {
            if (res.status === 403) return { count: 0 };
            if (!res.ok) throw new Error('Failed');
            return res.json();
        })
        .then(data => {
            const badge = document.getElementById('notificationBadge');
            if (badge && data.count > 0) {
                badge.style.display = 'block';
                badge.textContent = data.count > 9 ? '9+' : data.count;
            } else if (badge) {
                badge.style.display = 'none';
            }
        })
        .catch(() => {});
}

// Only run on user pages
if (!window.location.pathname.startsWith('/admin')) {
    document.addEventListener('DOMContentLoaded', updateNotificationCount);
    setInterval(updateNotificationCount, 30000);
}

// Toggle submenu
function toggleSubmenu(event, submenuId) {
    event.preventDefault();
    const navItem = event.currentTarget.closest('.nav-item');
    navItem.classList.toggle('open');
}

// Auto-open submenu if current page matches a child
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.submenu .nav-link.active').forEach(link => {
        const submenu = link.closest('.submenu');
        const navItem = submenu?.closest('.nav-item');
        if (submenu && navItem) {
            navItem.classList.add('open');
        }
    });
});
