document.addEventListener("DOMContentLoaded", (e) => {
  const header = document.querySelector('header');

  const observer = new MutationObserver((mutationsList) => {
    for (let mutation of mutationsList) {
      if (mutation.attributeName === 'class') {
        if (!header.classList.contains('header__active')) {
          removeAllOpenedClasses(); 
          enableChildArrowClicks();  
        }
      }
    }
  });

  observer.observe(header, { attributes: true });

  function removeAllOpenedClasses() {
    const openedElements = document.querySelectorAll('.sub-menu-container-box.opened');
    openedElements.forEach(el => {
      el.classList.remove('opened');
    });
  }

  function disableChildArrowClicks() {
    const children_arrows = document.querySelectorAll('li.menu-item-has-children');
    children_arrows.forEach(el => {
      el.removeEventListener('click', goToChildMenu);
    });
  }

  function enableChildArrowClicks() {
    const children_arrows = document.querySelectorAll('li.menu-item-has-children');
    children_arrows.forEach(el => {
      el.addEventListener('click', goToChildMenu);
    });
  }

  if (window.innerWidth < 541) {
    const li_items = document.querySelectorAll('li.menu-item-has-children');
    li_items.forEach(el => {
      el.addEventListener('click', goToChildMenu);
    });

    function goToChildMenu(event) {
      event.stopPropagation();  
      const li_tag = event.currentTarget;
      li_tag.querySelector('.sub-menu-container-box').classList.add('opened');
      disableChildArrowClicks();  
    }

    const parent_arrows = document.querySelectorAll('.menu-goto-parent');
    parent_arrows.forEach(el => {
      el.addEventListener('click', goToParentMenu);
    });

    function goToParentMenu(event) {
      event.stopPropagation(); 
      removeAllOpenedClasses(); 
      enableChildArrowClicks(); 
    }
  }
});
