/**
 * Guntur Properties Admin Panel - Mobile Sidebar Toggle
 * This script handles the responsive sidebar functionality
 */

document.addEventListener('DOMContentLoaded', function() {
    // Create the mobile menu toggle button if it doesn't exist
    if (!document.querySelector('.mobile-menu-toggle')) {
        var mobileToggle = document.createElement('button');
        mobileToggle.className = 'mobile-menu-toggle';
        mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
        document.body.appendChild(mobileToggle);
    }
    
    // Create the sidebar overlay if it doesn't exist
    if (!document.querySelector('.sidebar-overlay')) {
        var overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }
    
    // Attach event listeners to toggle elements
    var mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    var sidebarOverlay = document.querySelector('.sidebar-overlay');
    var sidebarCloseBtn = document.querySelector('.sidebar-close');
    
    // Mobile menu toggle click event
    if (mobileMenuToggle) {
        mobileMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-open');
        });
    }
    
    // Sidebar overlay click event (close sidebar when clicking overlay)
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            document.body.classList.remove('sidebar-open');
        });
    }
    
    // Sidebar close button click event
    if (sidebarCloseBtn) {
        sidebarCloseBtn.addEventListener('click', function() {
            document.body.classList.remove('sidebar-open');
        });
    }
    
    // Close sidebar on window resize (if viewport becomes large enough)
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 992) {
            document.body.classList.remove('sidebar-open');
        }
    });
    
    // Make tables responsive
    var tables = document.querySelectorAll('table');
    tables.forEach(function(table) {
        // Check if table is not already in a responsive container
        if (!table.closest('.table-responsive') && !table.closest('.table-container')) {
            var wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
    
    // Setup confirmation dialogs
    var confirmButtons = document.querySelectorAll('.confirm-action');
    confirmButtons.forEach(function(button) {
        button.addEventListener('click', function(e) {
            var message = this.getAttribute('data-confirm') || 'Are you sure you want to perform this action?';
            if (!confirm(message)) {
                e.preventDefault();
                return false;
            }
        });
    });
    
    // User dropdown toggle
    var userDropdownToggle = document.querySelector('.user-dropdown-toggle');
    var userDropdownMenu = document.querySelector('.user-dropdown-menu');
    
    if (userDropdownToggle && userDropdownMenu) {
        userDropdownToggle.addEventListener('click', function(e) {
            e.stopPropagation();
            userDropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking elsewhere
        document.addEventListener('click', function() {
            if (userDropdownMenu.classList.contains('show')) {
                userDropdownMenu.classList.remove('show');
            }
        });
    }
});