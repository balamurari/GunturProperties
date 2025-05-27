// Main JavaScript for Guntur Properties Website

document.addEventListener('DOMContentLoaded', function() {




  // Filter button functionality
  const filterButtons = document.querySelectorAll('.filter-button');
  
  filterButtons.forEach(button => {
    button.addEventListener('click', function() {
      // Create dropdown elements for different filter types
      const buttonText = this.textContent.trim();
      
      // Remove any existing dropdown
      const existingDropdown = document.querySelector('.filter-dropdown');
      if (existingDropdown) {
        existingDropdown.remove();
      }
      
      // Create dropdown based on filter type
      const dropdown = document.createElement('div');
      dropdown.className = 'filter-dropdown';
      
      let dropdownContent = '';
      
      if (buttonText.includes('Property Type')) {
        dropdownContent = `
          <div class="dropdown-item"><input type="checkbox" id="type-house"> <label for="type-house">House</label></div>
          <div class="dropdown-item"><input type="checkbox" id="type-apartment"> <label for="type-apartment">Apartment</label></div>
          <div class="dropdown-item"><input type="checkbox" id="type-villa"> <label for="type-villa">Villa</label></div>
          <div class="dropdown-item"><input type="checkbox" id="type-commercial"> <label for="type-commercial">Commercial</label></div>
          <div class="dropdown-item"><input type="checkbox" id="type-land"> <label for="type-land">Land</label></div>
        `;
      } else if (buttonText.includes('Pricing Range')) {
        dropdownContent = `
          <div class="dropdown-item"><input type="radio" name="price" id="price-1"> <label for="price-1">Below ₹30 Lac</label></div>
          <div class="dropdown-item"><input type="radio" name="price" id="price-2"> <label for="price-2">₹30 Lac - ₹50 Lac</label></div>
          <div class="dropdown-item"><input type="radio" name="price" id="price-3"> <label for="price-3">₹50 Lac - ₹80 Lac</label></div>
          <div class="dropdown-item"><input type="radio" name="price" id="price-4"> <label for="price-4">₹80 Lac - ₹1 Cr</label></div>
          <div class="dropdown-item"><input type="radio" name="price" id="price-5"> <label for="price-5">Above ₹1 Cr</label></div>
        `;
      } else if (buttonText.includes('Property Size')) {
        dropdownContent = `
          <div class="dropdown-item"><input type="radio" name="size" id="size-1"> <label for="size-1">Below 1000 sq.ft</label></div>
          <div class="dropdown-item"><input type="radio" name="size" id="size-2"> <label for="size-2">1000 - 1500 sq.ft</label></div>
          <div class="dropdown-item"><input type="radio" name="size" id="size-3"> <label for="size-3">1500 - 2000 sq.ft</label></div>
          <div class="dropdown-item"><input type="radio" name="size" id="size-4"> <label for="size-4">2000 - 3000 sq.ft</label></div>
          <div class="dropdown-item"><input type="radio" name="size" id="size-5"> <label for="size-5">Above 3000 sq.ft</label></div>
        `;
      }
      
      dropdown.innerHTML = dropdownContent + '<div class="dropdown-actions"><button class="apply-filter">Apply</button><button class="clear-filter">Clear</button></div>';
      
      // Position dropdown below button
      const buttonRect = this.getBoundingClientRect();
      dropdown.style.top = buttonRect.bottom + window.scrollY + 'px';
      dropdown.style.left = buttonRect.left + window.scrollX + 'px';
      dropdown.style.width = this.offsetWidth + 'px';
      
      // Add dropdown to DOM
      document.body.appendChild(dropdown);
      
      // Handle dropdown actions
      dropdown.querySelector('.apply-filter').addEventListener('click', function() {
        dropdown.remove();
      });
      
      dropdown.querySelector('.clear-filter').addEventListener('click', function() {
        // Clear all checkboxes and radio buttons in dropdown
        dropdown.querySelectorAll('input').forEach(input => {
          input.checked = false;
        });
      });
      
      // Close dropdown when clicking outside
      document.addEventListener('click', function closeDropdown(e) {
        if (!dropdown.contains(e.target) && e.target !== button) {
          dropdown.remove();
          document.removeEventListener('click', closeDropdown);
        }
      });
    });
  });
  
  // Search button functionality
  const searchButton = document.querySelector('.search-button');
  const searchInput = document.getElementById('property-search');
  
  searchButton.addEventListener('click', function() {
    const searchTerm = searchInput.value.trim();
    if (searchTerm) {
      console.log('Searching for:', searchTerm);
      // In a real application, you would redirect to a search results page
      alert('Searching for: ' + searchTerm);
      // window.location.href = 'search-results.html?q=' + encodeURIComponent(searchTerm);
    } else {
      // Show message to enter search term
      searchInput.placeholder = 'Please enter a search term...';
      searchInput.classList.add('error');
      setTimeout(() => {
        searchInput.classList.remove('error');
        searchInput.placeholder = 'Search for a property...';
      }, 2000);
    }
  });
  
  // Allow search by pressing Enter key
  searchInput.addEventListener('keyup', function(event) {
    if (event.key === 'Enter') {
      searchButton.click();
    }
  });
  
  // Property card favorite button functionality
  const favoriteButtons = document.querySelectorAll('.property-card-favorite');
  
  favoriteButtons.forEach(button => {
    button.addEventListener('click', function(e) {
      e.preventDefault();
      this.classList.toggle('active');
      
      // Toggle heart icon
      const icon = this.querySelector('i');
      if (this.classList.contains('active')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
      } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
      }
    });
  });
  
  // Scroll to top button
  const createScrollTopButton = () => {
    const scrollBtn = document.createElement('button');
    scrollBtn.innerHTML = '<i class="fas fa-arrow-up"></i>';
    scrollBtn.className = 'scroll-top-btn';
    document.body.appendChild(scrollBtn);
    
    // Show/hide button based on scroll position
    window.addEventListener('scroll', () => {
      if (window.pageYOffset > 300) {
        scrollBtn.classList.add('show');
      } else {
        scrollBtn.classList.remove('show');
      }
    });
    
    // Scroll to top when clicked
    scrollBtn.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  };
  
  createScrollTopButton();
  
  // Add animation on scroll
  const animateOnScroll = () => {
    const elements = document.querySelectorAll('.property-card, .location-card');
    
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('animate');
          observer.unobserve(entry.target);
        }
      });
    }, {
      threshold: 0.1
    });
    
    elements.forEach(element => {
      element.classList.add('animate-on-scroll');
      observer.observe(element);
    });
  };
  
  animateOnScroll();
  
  // Handle responsive navigation (would connect to navbar if included)
  const handleResponsiveNav = () => {
    // This would handle a mobile menu toggle if a navbar were included
    const body = document.body;
    const mobileNavBtn = document.createElement('button');
    mobileNavBtn.className = 'mobile-nav-toggle';
    mobileNavBtn.innerHTML = '<i class="fas fa-bars"></i>';
    
    // Append to body (would be in header if included)
    body.prepend(mobileNavBtn);
    
    mobileNavBtn.addEventListener('click', function() {
      body.classList.toggle('mobile-nav-open');
      const icon = this.querySelector('i');
      
      if (body.classList.contains('mobile-nav-open')) {
        icon.classList.remove('fa-bars');
        icon.classList.add('fa-times');
      } else {
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
      }
    });
  };
  
  // Only initialize if there's actually a navbar
  const navbar = document.querySelector('nav');
  if (navbar) {
    handleResponsiveNav();
  }
});

// Add window resize handler for responsive adjustments
window.addEventListener('resize', function() {
  // Any responsive handling that needs to be done on resize
  
  // Example: Readjust property card heights on window resize
  const propertyCards = document.querySelectorAll('.property-card');
  
  if (propertyCards.length > 0) {
    // Reset heights
    propertyCards.forEach(card => {
      card.style.height = 'auto';
    });
    
    // If on desktop, set equal heights
    if (window.innerWidth >= 768) {
      let maxHeight = 0;
      
      propertyCards.forEach(card => {
        const height = card.offsetHeight;
        if (height > maxHeight) {
          maxHeight = height;
        }
      });
      
      propertyCards.forEach(card => {
        card.style.height = maxHeight + 'px';
      });
    }
  }
});