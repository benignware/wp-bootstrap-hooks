document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.dropdown-menu .dropend').forEach(function (item) {
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
