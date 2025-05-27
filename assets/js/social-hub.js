document.addEventListener('DOMContentLoaded', function() {
  // Get elements
  const socialHub = document.querySelector('.social-hub');
  const socialHubToggle = document.querySelector('.social-hub-toggle');
  const socialHubMenu = document.querySelector('.social-hub-menu');
  
  if (socialHubToggle && socialHubMenu) {
    // Toggle menu when button is clicked
    socialHubToggle.addEventListener('click', function(e) {
      e.stopPropagation(); // Prevent this click from reaching the document
      socialHub.classList.toggle('active');
    });
    
    // Create staggered animation effect for menu items
    const socialHubItems = document.querySelectorAll('.social-hub-item');
    socialHubItems.forEach((item, index) => {
      item.style.transitionDelay = (index * 0.05) + 's';
    });
    
    // Allow clicks on menu items without closing the menu
    socialHubMenu.addEventListener('click', function(e) {
      e.stopPropagation();
    });
    
    // Close menu when clicking anywhere else on the document
    document.addEventListener('click', function() {
      socialHub.classList.remove('active');
    });
  }
});