document.addEventListener("DOMContentLoaded", function () {
  let menuContainer =
    document.getElementById("menu-header-menu-english") ||
    document.getElementById("menu-header-menu-indonesia");

  if (!menuContainer) return;  

  function toggleSubMenu(event) {
    event.stopPropagation();
    const menuItem = event.currentTarget;
    const subMenu = menuItem.querySelector(".sub-menu");

    if (subMenu) {
      const isOpen = subMenu.classList.contains("open");

      closeAllSubMenus(); 

      if (isOpen) {
        subMenu.classList.remove("open");
        menuItem.classList.remove("open");
      } else {
        subMenu.classList.add("open");
        menuItem.classList.add("open");
      }
    }
  }

  function closeAllSubMenus() {
    const openSubMenus = document.querySelectorAll(
      "#" + menuContainer.id + " .sub-menu.open"
    );

    openSubMenus.forEach((subMenu) => {
      subMenu.classList.remove("open");
      const parentMenuItem = subMenu.closest(".menu-item-has-children");

      if (parentMenuItem) {
        parentMenuItem.classList.remove("open");
      }
    });
  }

  function removeCollapsedClass() {
    const collapsedItems = document.querySelectorAll(".collapsed");
    collapsedItems.forEach((item) => {
      item.classList.remove("collapsed");
    });
  }

  const menuItemsWithHover = document.querySelectorAll(
    "#" + menuContainer.id + " .menu-item .menu-item__name"
  );

  menuItemsWithHover.forEach((menuName) => {
    const parentMenuItem = menuName.parentElement;

    if (!parentMenuItem.querySelector(".hover-name")) {
      const hoverSpan = document.createElement("p");
      hoverSpan.classList.add("hover-name");
      hoverSpan.innerText = menuName.innerText;
      parentMenuItem.appendChild(hoverSpan);

      parentMenuItem.addEventListener("mouseover", function () {
        if (menuName.classList.contains("collapsed")) {
          hoverSpan.classList.add("visible");
        }
      });

      parentMenuItem.addEventListener("mouseout", function () {
        hoverSpan.classList.remove("visible");
      });
    }
  });

  const menuItemsWithSubMenus = document.querySelectorAll(
    "#" + menuContainer.id + " .menu-item-has-children"
  );

  menuItemsWithSubMenus.forEach((menuItem) => {
    const arrowSpan = document.createElement("span");
    arrowSpan.classList.add("menu-arrow");
    menuItem.appendChild(arrowSpan);

    menuItem.addEventListener("click", function (event) {
      toggleSubMenu(event);
      removeCollapsedClass();
    });
  });

  document.querySelectorAll('a[href="#"]').forEach(function (link) {
    link.addEventListener("click", function (event) {
      event.preventDefault();
    });
  });

  document.addEventListener("click", function (event) {
    if (event.target.closest(".sub-menu a")) {
      closeAllSubMenus();
    } else if (!event.target.closest("#" + menuContainer.id)) {
      closeAllSubMenus();
    }
  });

  document.addEventListener("keyup", function (event) {
    if (event.key === "Escape") {
      closeAllSubMenus();
    }
  });
});
