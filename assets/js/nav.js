const hamburger = document.getElementById('hamburger');
    const menu = document.getElementById('menu');
    
    hamburger.addEventListener('click', () => {
      hamburger.classList.toggle('active');
      menu.classList.toggle('active');
    });
    
    // Reset animation on logo hover
    const logo = document.querySelector('.logo');
    const animatedElements = document.querySelectorAll('.floor, .roof, .crane-base, .crane-arm, .crane-line');
    
    logo.addEventListener('mouseenter', () => {
      animatedElements.forEach(el => {
        el.style.animation = 'none';
        setTimeout(() => {
          el.style.animation = '';
        }, 10);
      });
    });