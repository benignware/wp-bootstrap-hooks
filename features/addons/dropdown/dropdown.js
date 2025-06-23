document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.dropdown-menu').forEach(function (item) {
      item.addEventListener('mouseenter', function () {
        const submenu = this.querySelector('.dropdown-menu');
        if (submenu) submenu.classList.add('show');
      });

      item.addEventListener('mouseleave', function () {
        const submenu = this.querySelector('.dropdown-menu');
        if (submenu) submenu.classList.remove('show');
      });
  });
});

(() => {
    const { options } = window.bootstrapHooks || {};
    const caretSelector = `.${options?.caretClass || 'dropdown-toggle::after'}`;
    const handleClick = (e) => {
      const target = event.target.closest('a:where([href],[data-href]).dropdown-toggle');
      
      if (!target) {
        return;
      }

      const href = target.getAttribute('href') || target.getAttribute('data-href');

      if (!href || href.startsWith('#') || href.startsWith('javascript:')) {
        return;
      }

      const isOpen = !!target.classList.contains('show');
      const isHover = !!target.closest('.dropdown-hover');
      const isInShowingOffcanvas = !!target.closest('.offcanvas:where(.show, .showing)');

      if (isOpen && isHover && !isInShowingOffcanvas) {
        window.location.href = href;
        return;
      }

      if (isOpen) {
        return;
      }

      const isCaret = caretSelector && !!event.target.closest(caretSelector) || (() => {
        const after = getComputedStyle(target, ":after");
      
        if (after) {
          const w = Math.max(Number(after.getPropertyValue("width").slice(0, -2)), 16);
          const h = target.offsetHeight;
          const x = target.offsetWidth - w;
          const y = 0;
          const ex = e.layerX;
          const ey = e.layerY;
          
          if (ex > x && ex < x + w && ey > y && ey < y + h) {
            return true;
          }
        }
  
        return false;
      })();
  
      if (isCaret) {
        return false;
      }

      if (isOpen) {
        return;
      }

      const hasText = [...target.childNodes].some(node => node.nodeType === 3);
      const isTextHit = !hasText && event.target !== target || hasText && event.target === target;
  
      if (isTextHit) {
        window.location.href = href;
      }
    }
    window.addEventListener('click', handleClick);
  })();