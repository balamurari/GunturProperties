/**
 * Guntur Properties Admin Panel JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    /**
     * Mobile Sidebar Toggle
     */
    const sidebarToggle = document.querySelector('.sidebar-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            sidebar.classList.toggle('show');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(event) {
            if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('show');
            }
        });
    }
    
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
        document.addEventListener('click', function() {
            userDropdownMenu.classList.remove('show');
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
});