document.addEventListener("DOMContentLoaded", function () {
  let popup = document.querySelector(".popup");
  let body = document.body;

  if (sessionStorage.getItem("popupClosed") === "true") {
    return; 
  }

  if (popup && body) {
    let delay = parseInt(popup.getAttribute("data-delay"), 10);

    setTimeout(function () {
      popup.classList.add("popup__visible");
      body.classList.add("popup__active");
    }, delay * 1000);

    let cancelButton = document.querySelector(".popup__cancel");

    if (cancelButton) {
      cancelButton.addEventListener("click", function () {
        closePopup();
      });
    }

    popup.addEventListener("click", function (event) {
      if (event.target === popup) {
        closePopup();
      }
    });

    function closePopup() {
      popup.classList.remove("popup__visible");
      body.classList.remove("popup__active");
      sessionStorage.setItem("popupClosed", "true"); 
    }
  }
});
