/**
 * Guntur Properties Admin Panel - Combined JavaScript
 * This file combines all necessary JavaScript functionality for the admin panel
 */

document.addEventListener('DOMContentLoaded', function() {
    /**
     * Mobile Sidebar Toggle
     */
    const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.querySelector('.sidebar-overlay');
    const sidebarCloseBtn = document.querySelector('.sidebar-close');
    
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
    
    /**
     * User Dropdown Menu
     */
    const userDropdownToggle = document.querySelector('.user-dropdown-toggle');
    const userDropdownMenu = document.querySelector('.user-dropdown-menu');
    
    if (userDropdownToggle && userDropdownMenu) {
        userDropdownToggle.addEventListener('click', function(event) {
            event.stopPropagation();
            userDropdownMenu.classList.toggle('show');
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!userDropdownToggle.contains(event.target)) {
                userDropdownMenu.classList.remove('show');
            }
        });
    }
    
    /**
     * Confirmation Dialog
     */
    const confirmBtns = document.querySelectorAll('.confirm-action');
    
    if (confirmBtns.length > 0) {
        confirmBtns.forEach(function(btn) {
            btn.addEventListener('click', function(event) {
                const confirmMessage = this.getAttribute('data-confirm') || 'Are you sure you want to perform this action?';
                
                if (!confirm(confirmMessage)) {
                    event.preventDefault();
                }
            });
        });
    }
    
    /**
     * Make tables responsive
     */
    const tables = document.querySelectorAll('table');
    
    tables.forEach(function(table) {
        // Check if table is not already in a responsive container
        if (!table.closest('.table-responsive') && !table.closest('.table-container')) {
            const wrapper = document.createElement('div');
            wrapper.className = 'table-responsive';
            table.parentNode.insertBefore(wrapper, table);
            wrapper.appendChild(table);
        }
    });
    
    /**
     * Image Preview
     */
    const imageInputs = document.querySelectorAll('.image-input');
    
    if (imageInputs.length > 0) {
        imageInputs.forEach(function(input) {
            input.addEventListener('change', function() {
                const previewId = this.getAttribute('data-preview');
                const preview = document.getElementById(previewId);
                
                if (preview && this.files && this.files[0]) {
                    const reader = new FileReader();
                    
                    reader.onload = function(e) {
                        preview.src = e.target.result;
                    };
                    
                    reader.readAsDataURL(this.files[0]);
                }
            });
        });
    }
    
    /**
     * Dynamic Form Fields
     */
    const addFieldBtn = document.querySelector('.add-field-btn');
    
    if (addFieldBtn) {
        addFieldBtn.addEventListener('click', function() {
            const template = document.querySelector(this.getAttribute('data-template'));
            const container = document.querySelector(this.getAttribute('data-container'));
            
            if (template && container) {
                const newField = template.content.cloneNode(true);
                container.appendChild(newField);
                
                // Add event listener to remove button
                const removeBtn = container.querySelector('.remove-field-btn:last-child');
                if (removeBtn) {
                    removeBtn.addEventListener('click', function() {
                        this.closest('.dynamic-field').remove();
                    });
                }
            }
        });
        
        // Add event listener to existing remove buttons
        document.querySelectorAll('.remove-field-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                this.closest('.dynamic-field').remove();
            });
        });
    }
    
    // Log confirmation of script loading
    console.log('Admin panel JavaScript initialized');
});