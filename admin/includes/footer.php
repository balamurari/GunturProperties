<footer class="admin-footer">
                <div class="container">
                    <p>&copy; <?php echo date('Y'); ?> Guntur Properties. All rights reserved.</p>
                </div>
            </footer>
        </div><!-- /.main-content -->
    </div><!-- /.admin-layout -->

    <!-- jQuery library -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Responsive functionality -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Mobile menu toggle functionality
        const mobileToggle = document.querySelector('.mobile-menu-toggle');
        const sidebarOverlay = document.querySelector('.sidebar-overlay');
        const sidebarClose = document.querySelector('.sidebar-close');
        
        if (mobileToggle) {
            mobileToggle.addEventListener('click', function() {
                document.body.classList.toggle('sidebar-open');
            });
        }
        
        if (sidebarOverlay) {
            sidebarOverlay.addEventListener('click', function() {
                document.body.classList.remove('sidebar-open');
            });
        }
        
        if (sidebarClose) {
            sidebarClose.addEventListener('click', function() {
                document.body.classList.remove('sidebar-open');
            });
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth >= 992) {
                document.body.classList.remove('sidebar-open');
            }
        });
        
        // User dropdown toggle
        const userDropdown = document.querySelector('.user-dropdown-toggle');
        if (userDropdown) {
            userDropdown.addEventListener('click', function(e) {
                e.stopPropagation();
                this.parentElement.querySelector('.user-dropdown-menu').classList.toggle('show');
            });
            
            // Close dropdown when clicking outside
            document.addEventListener('click', function() {
                document.querySelectorAll('.user-dropdown-menu.show').forEach(function(dropdown) {
                    dropdown.classList.remove('show');
                });
            });
        }
        
        // Make tables responsive
        document.querySelectorAll('table').forEach(function(table) {
            if (!table.closest('.table-responsive') && !table.closest('.table-container')) {
                const wrapper = document.createElement('div');
                wrapper.className = 'table-responsive';
                table.parentNode.insertBefore(wrapper, table);
                wrapper.appendChild(table);
            }
        });
        
        // Confirmation for delete/important actions
        document.querySelectorAll('.confirm-action').forEach(function(button) {
            button.addEventListener('click', function(e) {
                const message = this.getAttribute('data-confirm') || 'Are you sure you want to perform this action?';
                if (!confirm(message)) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    });
    </script>
    
    <!-- Main admin script file -->
    <script src="../assets/js/admin-script.js"></script>
</body>
</html>