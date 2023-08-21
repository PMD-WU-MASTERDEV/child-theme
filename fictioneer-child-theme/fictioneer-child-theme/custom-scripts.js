<script>
  window.addEventListener('DOMContentLoaded', function () {
    function alignMenuToHeader() {
      var headerContent = document.querySelector('.header__content');
      var mainNavigation = document.querySelector('.main-navigation');
      if (window.innerWidth >= 768) {
        mainNavigation.style.marginTop = headerContent.clientHeight + 'px';
      } else {
        mainNavigation.style.marginTop = '0';
      }
    }

    window.addEventListener('resize', alignMenuToHeader);
    alignMenuToHeader(); // Execute on page load
  });
</script>