(function () {
	const savedState = localStorage.getItem("collapsedState");
	if (savedState) {
		const collapsedElements = JSON.parse(savedState);

		function applyState(element, state) {
			if (element && state) element.classList.add("collapsed");
		}

		applyState(document.querySelector(".aside"), collapsedElements.aside);
		applyState(document.querySelector(".aside__collapse"), collapsedElements.collapseButton);
		applyState(document.querySelector(".content"), collapsedElements.content);
		applyState(document.querySelector(".logo"), collapsedElements.fullLogo);
		applyState(document.querySelector(".header__search"), collapsedElements.searchButton);
		applyState(document.querySelector(".modal-search"), collapsedElements.searchPopup);
		applyState(document.querySelector(".aside__banner"), collapsedElements.adBanner);

		document.querySelectorAll(".menu-item").forEach((item, index) => {
			if (collapsedElements.menuItems?.[index]) item.classList.add("collapsed");
		});

		document.querySelectorAll(".menu-item__name").forEach((item, index) => {
			if (collapsedElements.menuName?.[index]) item.classList.add("collapsed");
		});

		document.querySelectorAll(".blue-popup").forEach((item, index) => {
			if (collapsedElements.bluePopup?.[index]) item.classList.add("collapsed");
		});
	}
})();

document.addEventListener("DOMContentLoaded", function () {
	const asideElement = document.querySelector(".aside");
	const collapseButton = document.querySelector(".aside__collapse");
	const fullLogo = document.querySelector(".logo");
	const searchButton = document.querySelector(".header__search");
	const contentElement = document.querySelector(".content");
	const menuItems = document.querySelectorAll(".menu-item");
	const adBanner = document.querySelector(".aside__banner");
	const menuName = document.querySelectorAll(".menu-item__name");
	const bluePopup = document.querySelectorAll(".blue-popup");
	const searchPopup = document.querySelector(".modal-search");

	function saveState() {
		localStorage.setItem(
			"collapsedState",
			JSON.stringify({
				aside: asideElement?.classList.contains("collapsed"),
				collapseButton: collapseButton?.classList.contains("collapsed"),
				content: contentElement?.classList.contains("collapsed"),
				fullLogo: fullLogo?.classList.contains("collapsed"),
				searchButton: searchButton?.classList.contains("collapsed"),
				searchPopup: searchPopup?.classList.contains("collapsed"),
				adBanner: adBanner?.classList.contains("collapsed"),
				menuItems: Array.from(menuItems).map((item) => item.classList.contains("collapsed")),
				menuName: Array.from(menuName).map((item) => item.classList.contains("collapsed")),
				bluePopup: Array.from(bluePopup).map((item) => item.classList.contains("collapsed")),
			})
		);
	}

	if (collapseButton) {
		collapseButton.addEventListener("click", function () {
			[asideElement, collapseButton, contentElement, fullLogo, searchButton, searchPopup, adBanner].forEach((el) =>
				el?.classList.toggle("collapsed")
			);

			[...bluePopup, ...menuItems, ...menuName].forEach((el) => el.classList.toggle("collapsed"));

			saveState();
		});
	}

	if (adBanner) {
		adBanner.addEventListener("click", function (event) {
			if (adBanner.classList.contains("collapsed")) {
				event.preventDefault();
			}
		});
	}
});
