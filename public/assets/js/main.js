/**
 * Main
 */

'use strict';

let menu,
  animate,
  isHorizontalLayout = false;

if (document.getElementById('layout-menu')) {
  isHorizontalLayout = document.getElementById('layout-menu').classList.contains('menu-horizontal');
}

document.addEventListener('DOMContentLoaded', function () {
  // class for ios specific styles
  if (navigator.userAgent.match(/iPhone|iPad|iPod/i)) {
    document.body.classList.add('ios');
  }

  // Beautiful Mobile Menu Toggle Functionality
  initBeautifulMobileMenu();
  
  // Ensure menu is closed on page load (mobile)
  if (window.innerWidth < 1200) {
    const layoutMenu = document.getElementById('layout-menu');
    const layoutWrapper = document.querySelector('.layout-wrapper');
    if (layoutMenu && layoutWrapper) {
      layoutMenu.classList.remove('menu-open');
      layoutWrapper.classList.remove('menu-open');
    }
  }
});

// Beautiful Mobile Menu Function
function initBeautifulMobileMenu() {
  const menuToggle = document.querySelector('.layout-menu-toggle');
  const layoutMenu = document.getElementById('layout-menu');
  const layoutWrapper = document.querySelector('.layout-wrapper');
  const menuCloseBtn = document.querySelector('.layout-menu-toggle .ti-x');
  const menuOpenBtn = document.querySelector('.layout-menu-toggle .ti-menu-2');

  // Debug logging
  console.log('Beautiful Mobile Menu Init:', {
    menuToggle: !!menuToggle,
    layoutMenu: !!layoutMenu,
    layoutWrapper: !!layoutWrapper,
    hasWithoutMenu: layoutWrapper?.classList.contains('layout-without-menu'),
    hasHorizontal: layoutWrapper?.classList.contains('layout-horizontal')
  });

  // Check if we're in a layout that supports mobile menu
  if (!layoutMenu) {
    console.log('No layout menu found');
    return;
  }

  // For layout-without-menu, we need to ensure the menu is properly positioned
  if (layoutWrapper && layoutWrapper.classList.contains('layout-without-menu')) {
    // Add mobile menu support to layout-without-menu
    layoutWrapper.classList.add('mobile-menu-supported');
  }

  if (menuToggle && layoutMenu) {
    menuToggle.addEventListener('click', function(e) {
      e.preventDefault();
      e.stopPropagation();
      
      console.log('Beautiful menu toggle clicked');
      
      // Toggle menu visibility with animation
      if (layoutMenu.classList.contains('menu-open')) {
        // Close menu with animation
        closeBeautifulMobileMenu();
      } else {
        // Open menu with animation
        openBeautifulMobileMenu();
      }
    });
  }

  // Close menu when clicking outside
  document.addEventListener('click', function(e) {
    if (layoutMenu && layoutMenu.classList.contains('menu-open')) {
      if (!layoutMenu.contains(e.target) && !menuToggle.contains(e.target)) {
        closeBeautifulMobileMenu();
      }
    }
  });

  // Close menu on window resize (if screen becomes larger)
  window.addEventListener('resize', function() {
    if (window.innerWidth >= 1200 && layoutMenu) { // xl breakpoint
      closeBeautifulMobileMenu();
    }
  });

  // Close menu on escape key
  document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && layoutMenu && layoutMenu.classList.contains('menu-open')) {
      closeBeautifulMobileMenu();
    }
  });

  // Touch/swipe support for mobile with beautiful animations
  let touchStartX = 0;
  let touchEndX = 0;
  let touchStartY = 0;
  let touchEndY = 0;

  document.addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
    touchStartY = e.changedTouches[0].screenY;
  });

  document.addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    touchEndY = e.changedTouches[0].screenY;
    handleBeautifulSwipe();
  });

  function handleBeautifulSwipe() {
    const swipeThreshold = 50;
    const swipeDistanceX = touchEndX - touchStartX;
    const swipeDistanceY = touchEndY - touchStartY;

    // Only handle horizontal swipes (ignore vertical scrolling)
    if (Math.abs(swipeDistanceX) > Math.abs(swipeDistanceY) && Math.abs(swipeDistanceX) > swipeThreshold) {
      if (swipeDistanceX > 0 && layoutMenu && layoutMenu.classList.contains('menu-open')) {
        // Swipe right - close menu with animation
        closeBeautifulMobileMenu();
      } else if (swipeDistanceX < 0 && layoutMenu && !layoutMenu.classList.contains('menu-open')) {
        // Swipe left - open menu (only if we're on mobile)
        if (window.innerWidth < 1200) {
          openBeautifulMobileMenu();
        }
      }
    }
  }

  function openBeautifulMobileMenu() {
    console.log('Opening beautiful mobile menu');
    
    // Add classes for animation
    layoutMenu.classList.add('menu-open', 'menu-slide-in');
    layoutWrapper.classList.add('menu-open');
    
    // Toggle button icons
    if (menuCloseBtn) {
      menuCloseBtn.style.display = 'block';
      menuCloseBtn.classList.add('rotate-in');
    }
    if (menuOpenBtn) {
      menuOpenBtn.style.display = 'none';
    }
    
    // Prevent body scroll when menu is open
    document.body.style.overflow = 'hidden';
    
    // Add entrance animation to menu items
    const menuItems = layoutMenu.querySelectorAll('.menu-item');
    menuItems.forEach((item, index) => {
      item.style.animationDelay = `${index * 0.1}s`;
      item.classList.add('menu-item-slide-in');
    });
    
    // Remove animation classes after animation completes
    setTimeout(() => {
      layoutMenu.classList.remove('menu-slide-in');
      menuItems.forEach(item => {
        item.classList.remove('menu-item-slide-in');
        item.style.animationDelay = '';
      });
    }, 600);
  }

  function closeBeautifulMobileMenu() {
    console.log('Closing beautiful mobile menu');
    
    // Add closing animation
    layoutMenu.classList.add('menu-slide-out');
    
    // Toggle button icons
    if (menuCloseBtn) {
      menuCloseBtn.classList.remove('rotate-in');
      menuCloseBtn.classList.add('rotate-out');
    }
    if (menuOpenBtn) {
      menuOpenBtn.style.display = 'block';
    }
    
    // Animate menu items out
    const menuItems = layoutMenu.querySelectorAll('.menu-item');
    menuItems.forEach((item, index) => {
      item.style.animationDelay = `${index * 0.05}s`;
      item.classList.add('menu-item-slide-out');
    });
    
    // Close menu after animation
    setTimeout(() => {
      layoutMenu.classList.remove('menu-open', 'menu-slide-out');
      layoutWrapper.classList.remove('menu-open');
      
      if (menuCloseBtn) {
        menuCloseBtn.style.display = 'none';
        menuCloseBtn.classList.remove('rotate-out');
      }
      
      // Restore body scroll
      document.body.style.overflow = '';
      
      // Clean up animation classes
      menuItems.forEach(item => {
        item.classList.remove('menu-item-slide-out');
        item.style.animationDelay = '';
      });
    }, 400);
  }

  // Add hover effects to menu items
  const menuItems = layoutMenu.querySelectorAll('.menu-item');
  menuItems.forEach(item => {
    item.addEventListener('mouseenter', function() {
      if (!this.classList.contains('active')) {
        this.classList.add('menu-item-hover');
      }
    });
    
    item.addEventListener('mouseleave', function() {
      this.classList.remove('menu-item-hover');
    });
  });
}

(function () {
  initPasswordToggle();
  // Window scroll function for navbar
  function onScroll() {
    var layoutPage = document.querySelector('.layout-page');
    if (layoutPage) {
      if (window.scrollY > 0) {
        layoutPage.classList.add('window-scrolled');
      } else {
        layoutPage.classList.remove('window-scrolled');
      }
    }
  }
  // On load time out
  setTimeout(() => {
    onScroll();
  }, 200);

  // On window scroll
  window.onscroll = function () {
    onScroll();
  };


  if (typeof Waves !== 'undefined') {
    Waves.init();
    Waves.attach(
      ".btn[class*='btn-']:not(.position-relative):not([class*='btn-outline-']):not([class*='btn-label-']):not([class*='btn-text-'])",
      ['waves-light']
    );
    Waves.attach("[class*='btn-outline-']:not(.position-relative)");
    Waves.attach("[class*='btn-label-']:not(.position-relative)");
    Waves.attach("[class*='btn-text-']:not(.position-relative)");
    Waves.attach('.pagination:not([class*="pagination-outline-"]) .page-item.active .page-link', ['waves-light']);
    Waves.attach('.pagination .page-item .page-link');
    Waves.attach('.dropdown-menu .dropdown-item');
    Waves.attach('[data-bs-theme="light"] .list-group .list-group-item-action');
    Waves.attach('[data-bs-theme="dark"] .list-group .list-group-item-action', ['waves-light']);
    Waves.attach('.nav-tabs:not(.nav-tabs-widget) .nav-item .nav-link');
    Waves.attach('.nav-pills .nav-item .nav-link', ['waves-light']);
  }





  // Display in main menu when menu scrolls
  let menuInnerContainer = document.getElementsByClassName('menu-inner'),
    menuInnerShadow = document.getElementsByClassName('menu-inner-shadow')[0];
  if (menuInnerContainer.length > 0 && menuInnerShadow) {
    menuInnerContainer[0].addEventListener('ps-scroll-y', function () {
      if (this.querySelector('.ps__thumb-y').offsetTop) {
        menuInnerShadow.style.display = 'block';
      } else {
        menuInnerShadow.style.display = 'none';
      }
    });
  }

  // Get style from local storage or use 'system' as default
 
  function getScrollbarWidth() {
    const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
    document.body.style.setProperty('--bs-scrollbar-width', `${scrollbarWidth}px`);
  }
  getScrollbarWidth();
  window.addEventListener('DOMContentLoaded', () => {
    getScrollbarWidth();
    // Toggle Universal Sidebar
    document.querySelectorAll('[data-bs-theme-value]').forEach(toggle => {
      toggle.addEventListener('click', () => {
        const theme = toggle.getAttribute('data-bs-theme-value');
        window.Helpers.setStoredTheme(templateName, theme);
        window.Helpers.setTheme(theme);
        window.Helpers.showActiveTheme(theme, true);
        window.Helpers.syncCustomOptions(theme);
        let currTheme = theme;
        if (theme === 'system') {
          currTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        const semiDarkL = document.querySelector('.template-customizer-semiDark');
        if (semiDarkL) {
          if (theme === 'dark') {
            semiDarkL.classList.add('d-none');
          } else {
            semiDarkL.classList.remove('d-none');
          }
        }
        window.Helpers.switchImage(currTheme);
      });
    });
  });



  let languageDropdown = document.getElementsByClassName('dropdown-language');

  if (languageDropdown.length) {
    let dropdownItems = languageDropdown[0].querySelectorAll('.dropdown-item');

    for (let i = 0; i < dropdownItems.length; i++) {
      dropdownItems[i].addEventListener('click', function () {
        let currentLanguage = this.getAttribute('data-language');
        let textDirection = this.getAttribute('data-text-direction');

        for (let sibling of this.parentNode.children) {
          var siblingEle = sibling.parentElement.parentNode.firstChild;

          // Loop through each sibling and push to the array
          while (siblingEle) {
            if (siblingEle.nodeType === 1 && siblingEle !== siblingEle.parentElement) {
              siblingEle.querySelector('.dropdown-item').classList.remove('active');
            }
            siblingEle = siblingEle.nextSibling;
          }
        }
        this.classList.add('active');

        i18next.changeLanguage(currentLanguage, (err, t) => {
          window.templateCustomizer ? window.templateCustomizer.setLang(currentLanguage) : '';
          directionChange(textDirection);
          if (err) return console.log('something went wrong loading', err);
          localize();
          window.Helpers.syncCustomOptionsRtl(textDirection);
        });
      });
    }
    function directionChange(textDirection) {
      document.documentElement.setAttribute('dir', textDirection);
      if (textDirection === 'rtl') {
        if (localStorage.getItem('templateCustomizer-' + templateName + '--Rtl') !== 'true')
          window.templateCustomizer ? window.templateCustomizer.setRtl(true) : '';
      } else {
        if (localStorage.getItem('templateCustomizer-' + templateName + '--Rtl') === 'true')
          window.templateCustomizer ? window.templateCustomizer.setRtl(false) : '';
      }
    }
  }

  // Notification
  // ------------
  const notificationMarkAsReadAll = document.querySelector('.dropdown-notifications-all');
  const notificationMarkAsReadList = document.querySelectorAll('.dropdown-notifications-read');

  // Notification: Mark as all as read
  if (notificationMarkAsReadAll) {
    notificationMarkAsReadAll.addEventListener('click', event => {
      notificationMarkAsReadList.forEach(item => {
        item.closest('.dropdown-notifications-item').classList.add('marked-as-read');
      });
    });
  }
  // Notification: Mark as read/unread onclick of dot
  if (notificationMarkAsReadList) {
    notificationMarkAsReadList.forEach(item => {
      item.addEventListener('click', event => {
        item.closest('.dropdown-notifications-item').classList.toggle('marked-as-read');
      });
    });
  }

  // Notification: Mark as read/unread onclick of dot
  const notificationArchiveMessageList = document.querySelectorAll('.dropdown-notifications-archive');
  notificationArchiveMessageList.forEach(item => {
    item.addEventListener('click', event => {
      item.closest('.dropdown-notifications-item').remove();
    });
  });

  // Init helpers & misc
  // --------------------

  // Init BS Tooltip
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Accordion active class
  const accordionActiveFunction = function (e) {
    if (e.type == 'show.bs.collapse' || e.type == 'show.bs.collapse') {
      e.target.closest('.accordion-item').classList.add('active');
    } else {
      e.target.closest('.accordion-item').classList.remove('active');
    }
  };

  const accordionTriggerList = [].slice.call(document.querySelectorAll('.accordion'));
  const accordionList = accordionTriggerList.map(function (accordionTriggerEl) {
    accordionTriggerEl.addEventListener('show.bs.collapse', accordionActiveFunction);
    accordionTriggerEl.addEventListener('hide.bs.collapse', accordionActiveFunction);
  });



  // Toggle Password Visibility
// Init Password Toggle

                        





})();

function initPasswordToggle() {
    var toggler = document.querySelectorAll('.form-password-toggle i');  
    if (typeof toggler !== 'undefined' && toggler !== null) {
          toggler.forEach(function (el) {
            el.addEventListener('click', function (e) {
               e.preventDefault();
               var formPasswordToggle = el.closest('.form-password-toggle');
               var formPasswordToggleIcon = formPasswordToggle.querySelector('i');
                  var formPasswordToggleInput = formPasswordToggle.querySelector('input');
                      if (formPasswordToggleInput.getAttribute('type') === 'text') {
                         formPasswordToggleInput.setAttribute('type', 'password');
                         formPasswordToggleIcon.classList.replace('tabler-eye', 'tabler-eye-off');
                         } else if (formPasswordToggleInput.getAttribute('type') === 'password') {
                          formPasswordToggleInput.setAttribute('type', 'text');
                          formPasswordToggleIcon.classList.replace('tabler-eye-off', 'tabler-eye');
                        } 
                      });
                      });
                       }
                       };

// Search state and data
let data = {};
let currentFocusIndex = -1;

// Utils
function isMacOS() {
  return /Mac|iPod|iPhone|iPad/.test(navigator.userAgent);
}


