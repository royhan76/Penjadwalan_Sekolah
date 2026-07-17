/**
 * app.js - Global JavaScript for Sistem Penjadwalan Sekolah
 * Version: 2.0
 */

// =============================================
// ALERT / TOAST SYSTEM
// =============================================
function showAlert(type, message, duration = 4000) {
    const alertBox = document.getElementById('alertBox');
    if (!alertBox) return;
    
    const icons = {
        success: `<svg viewBox="0 0 24 24" fill="none" width="18" height="18"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><polyline points="22 4 12 14.01 9 11.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>`,
        danger:  `<svg viewBox="0 0 24 24" fill="none" width="18" height="18"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="16" r="1" fill="currentColor"/></svg>`,
        info:    `<svg viewBox="0 0 24 24" fill="none" width="18" height="18"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="16" x2="12" y2="12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="8" r="1" fill="currentColor"/></svg>`,
    };
    
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}`;
    alert.innerHTML = `${icons[type] || ''} <span>${message}</span>`;
    
    alertBox.appendChild(alert);
    
    // Auto remove
    setTimeout(() => {
        alert.style.opacity = '0';
        alert.style.transform = 'translateX(20px)';
        alert.style.transition = 'all 0.3s ease';
        setTimeout(() => alert.remove(), 300);
    }, duration);
    
    // Click to dismiss
    alert.addEventListener('click', () => alert.remove());
}

// =============================================
// NAVBAR MOBILE TOGGLE
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('navToggle');
    const links  = document.getElementById('navLinks');
    
    if (toggle && links) {
        toggle.addEventListener('click', function() {
            links.classList.toggle('open');
            // Animate hamburger
            const spans = toggle.querySelectorAll('span');
            if (links.classList.contains('open')) {
                spans[0].style.transform = 'rotate(45deg) translate(4px, 4px)';
                spans[1].style.opacity   = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(4px, -4px)';
            } else {
                spans[0].style.transform = '';
                spans[1].style.opacity   = '';
                spans[2].style.transform = '';
            }
        });
        
        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!toggle.contains(e.target) && !links.contains(e.target)) {
                links.classList.remove('open');
                toggle.querySelectorAll('span').forEach(s => {
                    s.style.transform = '';
                    s.style.opacity   = '';
                });
            }
        });
    }
    
    // =============================================
    // DROPDOWN MENU (click on mobile, hover on desktop)
    // =============================================
    document.querySelectorAll('.nav-dropdown > .nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            // Only intercept on mobile (toggle behavior)
            if (window.innerWidth <= 768) {
                e.preventDefault();
                const dropdown = this.closest('.nav-dropdown');
                const isOpen = dropdown.classList.contains('dropdown-open');
                // Close all others
                document.querySelectorAll('.nav-dropdown').forEach(d => d.classList.remove('dropdown-open'));
                if (!isOpen) dropdown.classList.add('dropdown-open');
            }
        });
    });

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.nav-dropdown')) {
            document.querySelectorAll('.nav-dropdown').forEach(d => d.classList.remove('dropdown-open'));
        }
    });

    
    // =============================================
    // TABLE SEARCH
    // =============================================
    const searchInput = document.getElementById('tableSearch');
    const dataTable   = document.getElementById('dataTable');
    
    if (searchInput && dataTable) {
        searchInput.addEventListener('input', function() {
            const term  = this.value.toLowerCase().trim();
            const rows  = dataTable.querySelectorAll('tbody tr');
            let visible = 0;
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                const show = !term || text.includes(term);
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            
            // Update count
            const countEl = document.querySelector('.table-count');
            if (countEl) {
                const total = rows.length;
                countEl.textContent = term ? `${visible} dari ${total} hasil` : `${total} data`;
            }
        });
    }
    
    // =============================================
    // ANIMATE PROGRESS BARS
    // =============================================
    const progressBars = document.querySelectorAll('.progress-fill');
    if (progressBars.length > 0) {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const fill = entry.target;
                    const targetWidth = fill.dataset.width || fill.style.width;
                    fill.style.width = '0%';
                    setTimeout(() => {
                        fill.style.width = targetWidth;
                    }, 100);
                    observer.unobserve(fill);
                }
            });
        }, { threshold: 0.1 });
        
        progressBars.forEach(bar => observer.observe(bar));
    }
    
    // =============================================
    // COUNTER ANIMATION
    // =============================================
    const counters = document.querySelectorAll('.counter');
    if (counters.length > 0) {
        const counterObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const el     = entry.target;
                    const target = parseInt(el.dataset.target, 10);
                    const dur    = 800;
                    const step   = target / (dur / 16);
                    let current  = 0;
                    
                    const timer = setInterval(() => {
                        current += step;
                        if (current >= target) {
                            el.textContent = target;
                            clearInterval(timer);
                        } else {
                            el.textContent = Math.floor(current);
                        }
                    }, 16);
                    
                    counterObserver.unobserve(el);
                }
            });
        });
        
        counters.forEach(c => counterObserver.observe(c));
    }
    
    // =============================================
    // ANIMATE ELEMENTS WITH DELAY
    // =============================================
    const animatedEls = document.querySelectorAll('[style*="--delay"]');
    animatedEls.forEach(el => {
        el.style.animationPlayState = 'running';
    });
});

// =============================================
// GLOBAL KEYBOARD SHORTCUTS
// =============================================
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + K: focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const search = document.getElementById('tableSearch');
        if (search) search.focus();
    }
});
