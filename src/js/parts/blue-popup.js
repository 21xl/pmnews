document.addEventListener("DOMContentLoaded", function () {
	let popupBlue = document.querySelector(".blue-popup");
	let body = document.body;

	if (popupBlue && body && !sessionStorage.getItem("popupBlueClosed")) {
		let delay = parseInt(popupBlue.getAttribute("data-delay"), 10) || 0;

		setTimeout(function () {
			popupBlue.classList.add("blue-popup__visible");
			body.classList.add("blue-popup__active");
		}, delay * 1000);

		let cancelButton = document.querySelector(".blue-popup__cancel");

		if (cancelButton) {
			cancelButton.addEventListener("click", function () {
				closePopup();
			});
		}

		popupBlue.addEventListener("click", function (event) {
			if (event.target === popupBlue) {
				closePopup();
			}
		});
	}

	function closePopup() {
		popupBlue.classList.add("hide");
		setTimeout(function () {
			popupBlue.classList.remove("blue-popup__visible", "hide");
			body.classList.remove("blue-popup__active");

			sessionStorage.setItem("popupBlueClosed", "true");
		}, 500);
	}
});
