import store from "./pinned.store";
import Handlebars from "handlebars";
import templateSource from "../../templates/pinned-template.html";
import templateComp from "../../templates/side-template.html";
import i18next from "./i18n";

const apiUrl = process.env.API_URL || "http://localhost:3277";

const template = Handlebars.compile(templateSource);
const templateCompet = Handlebars.compile(templateComp);

export async function fetchAndStoreMatches() {
	const storedPinned = localStorage.getItem("pinnedTennis");
	const defaultPin = [];

	if (!storedPinned) {
		localStorage.setItem("pinnedTennis", JSON.stringify(defaultPin));
	}
	const pinned = JSON.parse(localStorage.getItem("pinnedTennis")) || [];
	const sceleton = document.querySelector(".sceleton_sb_pined");
	const empty = document.querySelector(".empty_sb_pined");

	if (pinned.length === 0) {
		if (sceleton) {
			sceleton.classList.add("hidden");
		}
		if (empty) {
			empty.classList.remove("hidden");
		}

		return;
	}

	try {
		const response = await fetch(`${apiUrl}/api/tennis/tournaments`, {
			method: "POST",
			headers: {
				"Content-Type": "application/json",
			},
			body: JSON.stringify({ tournamentIds: pinned }),
		});

		if (!response.ok) {
			throw new Error(`Server error: ${response.status} - ${response.statusText}`);
		}
		if (sceleton) {
			sceleton.classList.add("hidden");
		} else {
			console.error("Элемент с классом 'sceleton_sb_pined' не найден.");
		}
		const { data } = await response.json();

		store.setData(data);
	} catch (error) {
		console.error("Error fetching matches:", error);
	}
}

function addLeague(league, competitionList) {
	console.log("league", league);
	const html = template(league);
	competitionList.insertAdjacentHTML("beforeend", html);
}

function updateLeague(league) {
	const existingElement = document.querySelector(`[data-id="${league.competition.id}"]`);
	if (existingElement) {
		existingElement.outerHTML = template(league);
	}
}

function removeLeague(id) {
	const existingElement = document.querySelector(`[data-leagid="${id}"]`);

	if (existingElement) {
		existingElement.remove();
	}
}

document.addEventListener("DOMContentLoaded", () => {
	const countryItems = document.querySelectorAll(".countries__item");

	countryItems.forEach((item) => {
		item.addEventListener("click", async function (event) {
			const currentItem = event.currentTarget;
			const loader = currentItem.querySelector(".statistics-sidebar__item-loader");
			const competitionsContainer = currentItem.querySelector(".statistics-sidebar__submenu");

			document.querySelectorAll(".countries__item.active").forEach((activeItem) => {
				if (activeItem !== currentItem) {
					const activeCompetitionsContainer = activeItem.querySelector(".statistics-sidebar__submenu");
					if (activeCompetitionsContainer) {
						activeCompetitionsContainer.style.display = "none";
					}
					activeItem.classList.remove("active");
				}
			});

			if (currentItem.classList.contains("active")) {
				if (competitionsContainer) {
					competitionsContainer.style.display = "none";
				}
				currentItem.classList.remove("active");
				return;
			}

			currentItem.classList.add("active");
			if (loader) loader.style.display = "flex";

			if (competitionsContainer) {
				if (loader) loader.style.display = "none";
				competitionsContainer.style.display = "flex";
				return;
			}

			try {
				const response = await fetch(
					`${apiUrl}/api/tennis/categories/tournaments?category_id=${currentItem.dataset.id}&type=${currentItem.dataset.type}`,
					{
						method: "GET",
						headers: {
							"Content-Type": "application/json",
						},
					}
				);

				if (!response.ok) {
					throw new Error(`Ошибка сервера: ${response.status} ${response.statusText}`);
				}

				const data = await response.json();
				console.log(data);
				const pinned = JSON.parse(localStorage.getItem("pinnedTennis")) || [];

				const competitions = data.map((item) => ({
					...item,
					pinned: pinned.includes(item.id),
				}));

				const html = templateCompet({ competitions });
				const newContainer = document.createElement("div");
				newContainer.innerHTML = html;
				currentItem.appendChild(newContainer);
			} catch (error) {
				console.error("Ошибка запроса:", error);
			} finally {
				if (loader) loader.style.display = "none";
			}
		});
	});
});

Handlebars.registerHelper("isPinnedClass", function (isPined) {
	if (isPined) return "active";
	else return "";
});

document.addEventListener("DOMContentLoaded", () => {
	const toggleButton = document.getElementById("toggle-button");
	const hiddenItems = document.querySelectorAll(".countries__item.hidden");
	const competitionList = document.getElementById("pinned");
	const empty = document.querySelector(".empty_sb_pined");
	const otherList = document.querySelector(".statistics-sidebar__block--other");
	let hidden = true;

	if (toggleButton) {
		const toggleButtonText = toggleButton.querySelector("span");

		toggleButton.addEventListener("click", () => {
			const isHidden = !hidden;
			hiddenItems.forEach((item) => item.classList.toggle("hidden", isHidden));
			toggleButtonText.textContent = isHidden ? "Show more" : "Show less";
			toggleButton.classList.toggle("less", !isHidden);
			otherList.classList.toggle("hidden", isHidden);
			hidden = isHidden;
		});
	}

	document.addEventListener("DOMContentLoaded", function () {
		const tournamentsToggle = document.getElementById("tournaments-toggle");

		if (tournamentsToggle) {
			tournamentsToggle.addEventListener("click", function () {
				const hiddenItems = document.querySelectorAll("#tennis-tournaments .statistics-sidebar__item.hidden");
				const buttonText = this.querySelector("span");

				hiddenItems.forEach((item) => {
					item.classList.toggle("hidden");
				});

				// Переключение текста кнопки
				if (buttonText.textContent === "Show more") {
					buttonText.textContent = "Show less";
				} else {
					buttonText.textContent = "Show more";
				}
			});
		}
	});

	fetchAndStoreMatches();

	let previousData = new Map();

	store.onDataChange((newData) => {
		console.log("newData", newData);
		const newDataMap = new Map(newData.map((league) => [league.id, league]));

		newDataMap.forEach((newItem, id) => {
			const oldItem = previousData.get(id);
			if (!oldItem) {
				addLeague(newItem, competitionList);
			} else if (JSON.stringify(newItem) !== JSON.stringify(oldItem)) {
				updateLeague(newItem);
			}
		});

		previousData.forEach((_, id) => {
			if (!newDataMap.has(id)) {
				removeLeague(id);
			}
		});

		previousData = newDataMap;
		const pinned = JSON.parse(localStorage.getItem("pinnedTennis")) || [];

		if (pinned.length === 0) {
			if (empty) {
				empty.classList.remove("hidden");
			}
		} else {
			if (empty) {
				empty.classList.add("hidden");
			}
		}
	});
});

Handlebars.registerHelper("getLocalizedName", function (names) {
	if (!names || typeof names !== "object") return "No name provided";
	const currentLocale = i18next.language || "en";
	const localeKey = `name_${currentLocale.toLowerCase()}`;
	return names[localeKey] || names["name_en"] || "Unknown";
});
