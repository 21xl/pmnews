document.addEventListener("DOMContentLoaded", function () {
  const trigger = document.querySelector(".statistics__mobile-sidebar");
  const sidebar = document.querySelector(".statistics-sidebar");
  const close = document.querySelector(".statistics-sidebar__close");

  if (trigger && sidebar && close) {
    trigger.addEventListener("click", function () {
      trigger.classList.toggle("active");
      sidebar.classList.toggle("active");
      document.body.classList.toggle("header__freez");
    });

    close.addEventListener("click", function () {
      sidebar.classList.toggle("active");
      document.body.classList.toggle("header__freez");
    });
  }
});
