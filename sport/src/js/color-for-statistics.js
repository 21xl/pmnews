document.addEventListener("DOMContentLoaded", () => {
	const canonicalLink = document.querySelector('link[rel="canonical"]');
	const canonicalHref = canonicalLink ? canonicalLink.href : window.location.href;

	if (canonicalHref.includes("/statistics/")) {
		const menuItems = document.querySelectorAll(".menu-item a");

		menuItems.forEach((menuItem) => {
			if (menuItem.href === canonicalHref) {
				menuItem.closest(".menu-item").classList.add("current-menu-item");
			} else {
				menuItem.closest(".menu-item").classList.remove("current-menu-item");
			}
		});
	}
});
