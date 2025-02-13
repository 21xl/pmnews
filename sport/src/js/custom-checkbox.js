document.addEventListener("DOMContentLoaded", () => {
	const statistics = document.querySelector(".match__statistics");

	if (statistics) {
		const checkboxes = statistics.querySelectorAll(".custom-checkbox__item");
		const tabs = statistics.querySelectorAll(".custom-checkbox__tab");

		if (checkboxes.length > 0 && tabs.length > 0) {
			const firstCheckbox = checkboxes[0];
			const firstTabName = firstCheckbox.getAttribute("data-checkbox-tab");
			const firstTab = statistics.querySelector(`.custom-checkbox__tab[data-checkbox-tab="${firstTabName}"]`);
			const firstInput = firstCheckbox.querySelector(".custom-checkbox__input");
			const firstBox = firstCheckbox.querySelector(".custom-checkbox__box");

			if (firstTabName && firstTab && firstInput && firstBox) {
				firstBox.setAttribute("data-checked", "true");
				firstInput.value = "true";
				firstTab.classList.remove("hidden");
			}

			checkboxes.forEach((checkbox) => {
				const tabName = checkbox.getAttribute("data-checkbox-tab");
				const input = checkbox.querySelector(".custom-checkbox__input");
				const box = checkbox.querySelector(".custom-checkbox__box");

				if (tabName && input && box) {
					checkbox.addEventListener("click", () => {
						checkboxes.forEach((cb) => {
							const cbBox = cb.querySelector(".custom-checkbox__box");
							const cbInput = cb.querySelector(".custom-checkbox__input");
							if (cbBox && cbInput) {
								cbBox.setAttribute("data-checked", "false");
								cbInput.value = "false";
							}
						});

						box.setAttribute("data-checked", "true");
						input.value = "true";

						tabs.forEach((tab) => tab.classList.add("hidden"));

						const activeTab = statistics.querySelector(`.custom-checkbox__tab[data-checkbox-tab="${tabName}"]`);
						if (activeTab) {
							activeTab.classList.remove("hidden");
						}
					});
				}
			});
		}
	}
});
