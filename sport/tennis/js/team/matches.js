//tennis/js/main/matches.js
import Handlebars from "handlebars";
import favoriteTeamsStore from "../favoriteTeamsStore";
import templatefav from "../../templates/fav-team-template.html";
import templatefavempty from "../../templates/fav-team-template-empty.html";
import templateSource from "../../templates/match-template.html";
import templateMatchItem from "../../templates/match-row-template.html";
import matcheserror from "../../templates/matches-error.html";
import sceleton from "../../templates/matches-sceleton.html";
import pinned from "./pinned.store";
import incidents from "./incidents.store";
import competitionStore from "./competition.store";
import i18next from "./i18n";
import { DiffDOM } from "diff-dom";
const apiUrl = process.env.API_URL || "http://localhost:3277";
const wsUrl = process.env.WEBSOCKET || "ws://localhost:3277/websocket";

const dd = new DiffDOM();

jQuery(document).ready(function ($) {
	const template = Handlebars.compile(templateSource);
	const item = Handlebars.compile(templateMatchItem);
	const mEerortemplate = Handlebars.compile(matcheserror);
	const sceletontemplate = Handlebars.compile(sceleton);
	const matchesMain = $(".matches-tennis__main");
	const teamId = $(".statistics-competition").data("teamid");
	const status = $(".statistics-category__links").data("tab");

	let tab = "all";

	favoriteTeamsStore.initialize("tn");
	const favTemplate = Handlebars.compile(templatefav);
	const favTemplateEmpty = Handlebars.compile(templatefavempty);
	favoriteTeamsStore.onDataChange((teams) => {
		let newHtml;
		if (teams.length === 0) {
			newHtml = favTemplateEmpty();
		} else {
			newHtml = teams.map((team) => favTemplate(team)).join("");
		}

		// Находим целевой элемент на странице
		const targetElement = document.querySelector("#tennis-fav-team");
		if (!targetElement) {
			console.error("Элемент #tennis-fav-team не найден на странице");
			return;
		}

		// Создаём временный контейнер с тем же id и классом
		const tempContainer = document.createElement("ul");
		tempContainer.id = "tennis-fav-team"; // Устанавливаем тот же id
		tempContainer.className = "statistics-sidebar__list";
		tempContainer.innerHTML = newHtml;

		// Применяем diff и обновляем DOM
		const differences = dd.diff(targetElement, tempContainer);
		dd.apply(targetElement, differences);
	});

	function getPinnedItems() {
		return JSON.parse(localStorage.getItem("pinnedTennis")) || [];
	}

	async function updatePinnedItems(id) {
		const pinnedItems = getPinnedItems();
		const index = pinnedItems.indexOf(id);

		if (index === -1) {
			pinnedItems.push(id);
		} else {
			pinnedItems.splice(index, 1);
		}

		localStorage.setItem("pinnedTennis", JSON.stringify(pinnedItems));

		competitionStore.updatePinned(id, index === -1);

		if (id) {
			pinned.updatePinned(id);
		} else {
			if (index !== -1) {
				pinned.delPinned(id);
			}
		}

		renderMatchesWithUpdatedOrder();
	}

	function addPinnedFlag(data) {
		const pinnedItems = getPinnedItems();
		return data.map((item) => {
			const isPinned = pinnedItems.includes(item.tournamentId);
			return {
				...item,
				pinned: isPinned,
			};
		});
	}

	function renderMatches(data) {
		// Генерируем HTML для всех данных
		const html = data.length
			? data.map(template).join("") // Рендерим все элементы массива data
			: mEerortemplate; // Шаблон ошибки, если данных нет

		// Заменяем содержимое matchesMain
		matchesMain.html(html);

		initToggleFav();
		initializePinnedItems();
		toggleHide();
	}

	function renderMatchesWithUpdatedOrder() {
		const data = competitionStore.getData();
		const excludeStatuses = ["9", "13"];

		const filteredData = data.map((competition) => ({
			...competition,
			matches: competition.matches.filter((match) => !excludeStatuses.includes(String(match.status_id))),
		}));

		const nonEmptyData = filteredData.filter((competition) => competition.matches && competition.matches.length > 0);

		const sortedData = [...nonEmptyData].sort((a, b) => b.pinned - a.pinned);

		renderMatches(sortedData, false, false, true);
	}

	function initializePinnedItems() {
		const pinnedItems = getPinnedItems();
		$(".pin").each(function () {
			const competitionId = $(this).data("competition_id");
		});
	}

	const datePickerDisplay = document.querySelector(".date-picker__display");
	if (datePickerDisplay) {
		observer.observe(datePickerDisplay, {
			attributes: true,
		});
	}

	async function fetchAndRenderMatches(type = "single") {
		try {
			matchesMain.html(sceletontemplate);

			const response = await fetch(
				`${apiUrl}/api/tennis/player-matches?teamId=${teamId}&status=${status}&type=${type}`
			);

			if (!response.ok) {
				throw new Error(`Ошибка запроса: ${response.statusText}`);
			}

			const data = await response.json();
			const processedData = addPinnedFlag(data);
			competitionStore.setData(processedData);

			if (!tab) return renderMatchesWithUpdatedOrder();
			const endedMatches = competitionStore.filterDataByStatus(tab);
			renderMatches(endedMatches, false, false, true);
		} catch (error) {}
	}

	$(document).on("click", ".pin", function (event) {
		const competitionId = $(this).data("competition_id");

		updatePinnedItems(competitionId);
		$(this).toggleClass("active");
	});

	$(document).on("click", ".addfavteam", function (event) {
		const $element = $(this);
		const teamId = $element.data("teamid");
		if (teamId) {
			$element.toggleClass("active");
			favoriteTeamsStore.toggleFavoriteTeam(teamId, "tn");
		} else {
			console.error("Не удалось определить teamId из data-teamid");
		}
	});

	if (matchesMain) {
		fetchAndRenderMatches();
		initCheckbox();
	}

	function initCheckbox() {
		const statistics = document.querySelector(".custom-checkbox");

		if (statistics) {
			const checkboxes = statistics.querySelectorAll(".custom-checkbox__item");

			if (checkboxes.length > 0) {
				checkboxes.forEach((checkbox) => {
					const tabName = checkbox.getAttribute("data-checkbox-tab");
					const input = checkbox.querySelector(".custom-checkbox__input");
					const box = checkbox.querySelector(".custom-checkbox__box");

					if (tabName && input && box) {
						checkbox.addEventListener("click", () => {
							fetchAndRenderMatches(tabName);

							// Очищаем предыдущие состояния
							checkboxes.forEach((cb) => {
								const cbBox = cb.querySelector(".custom-checkbox__box");
								const cbInput = cb.querySelector(".custom-checkbox__input");
								if (cbBox && cbInput) {
									cbBox.setAttribute("data-checked", "false");
									cbInput.value = "false";
								}
							});

							// Устанавливаем новое состояние
							box.setAttribute("data-checked", "true");
							input.value = "true";
						});
					}
				});
			}
		}
	}

	function updateLiveMatchesTime() {
		$(".matches-tennis__item.live").each(function () {
			const matchId = $(this).data("match_id");
			const matchTimeElement = $(this).find(".matches-tennis__item-time");

			const match = competitionStore
				.getData()
				.flatMap((competition) => competition.matches)
				.find((m) => m.id === matchId);

			if (match) {
				const currentTimestamp = Math.floor(Date.now() / 1000);
				const status = String(match.status_id);
				const kickoffTimestamp = match.kickoff_timestamp || match.match_time;

				if (status === "2" || status === "4" || status === "5") {
					let matchMinutes;
					if (status === "2") {
						matchMinutes = Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 1;
						if (matchMinutes > 45) {
							matchMinutes = `45+${matchMinutes - 45}`;
						}
					} else if (status === "4") {
						matchMinutes = Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 46;
						if (matchMinutes > 90) {
							matchMinutes = `90+${matchMinutes - 90}`;
						}
					} else if (status === "5") {
						matchMinutes = Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 45;
						matchMinutes = `90+${matchMinutes - 90}`;
					}

					matchTimeElement.text(`${matchMinutes}`);
				}
			}
		});
	}

	function syncWithRealTime() {
		const now = new Date();
		const nextMinute = new Date(
			now.getFullYear(),
			now.getMonth(),
			now.getDate(),
			now.getHours(),
			now.getMinutes() + 1,
			0,
			0
		);
		const timeUntilNextMinute = nextMinute - now;

		setTimeout(() => {
			updateLiveMatchesTime();
			setInterval(updateLiveMatchesTime, 60000);
		}, timeUntilNextMinute);
	}

	syncWithRealTime();

	const socket = new WebSocket(wsUrl);

	socket.onopen = () => {
		console.log("WebSocket соединение установлено");

		// Подписываемся на топик тенниса
		socket.send(
			JSON.stringify({
				action: "subscribe",
				topic: "thesports/tennis/match/v1",
			})
		);
	};

	setInterval(() => {
		if (socket.readyState === WebSocket.OPEN) {
			socket.send(".");
		}
	}, 30000);

	socket.onmessage = (event) => {
		try {
			const data = JSON.parse(event.data);

			if (!Array.isArray(data)) {
				return;
			}

			data.forEach((matchUpdate, index) => {
				try {
					if (matchUpdate.score) {
						competitionStore.updateMatchData(matchUpdate.score);
					}
					if (matchUpdate.timeline) {
						competitionStore.updateMatchGame(matchUpdate);
					}
				} catch (innerError) {}
			});
		} catch (error) {}
	};

	competitionStore.onDataChange(({ updatedMatch }) => {
		if (updatedMatch) {
			updateMatchInDOM(updatedMatch);
		}
	});

	competitionStore.gameChanged(({ updatedMatch }) => {
		if (updatedMatch) {
			updateMatchInDOM(updatedMatch);
		}
	});

	function updateMatchInDOM(updatedMatch) {
		const matchElement = document.querySelector(`.matches-tennis__item[data-match_id="${updatedMatch.id}"]`);

		if (!matchElement) {
			return;
		}

		const newItemDom = item(updatedMatch); // Новый HTML как строка
		const tempDiv = document.createElement("div");
		tempDiv.innerHTML = newItemDom;
		const diff = dd.diff(matchElement, tempDiv.firstChild);
		if (!diff.length) return;
		dd.apply(matchElement, diff);

		const finishedStatuses = [3, 51, 52, 53, 54, 55];
		if (!finishedStatuses.includes(updatedMatch.status_id) && tab === "live") {
			const matchElement = document.querySelector(`a .matches-tennis__item[data-match_id="${updatedMatch.id}"]`);
			setTimeout(() => {
				const parentElement = matchElement.closest(".matches-tennis__ligue-content");
				matchElement.remove();
				if (parentElement && parentElement.children.length === 0) {
					const leagueElement = parentElement.closest(".matches-tennis__ligue");
					if (leagueElement) leagueElement.remove();
				}
			}, 60000);
		}
	}

	socket.onclose = () => {};

	Handlebars.registerHelper("t", function (key) {
		return i18next.t(key);
	});

	Handlebars.registerHelper("isHomeScoreGreater", function (homeScores) {
		return Array.isArray(homeScores) && homeScores[2] > 0;
	});

	Handlebars.registerHelper("redCards", function (homeScores) {
		if (Array.isArray(homeScores) && homeScores[2] > 1) return homeScores[2];
		if (Array.isArray(homeScores) && homeScores[2] == 1) return "";
	});

	Handlebars.registerHelper("statusClass", function (status) {
		switch (String(status)) {
			case "0": // ABNORMAL (Suggest Hiding)
				return "hidden";
			case "1": // NOT_STARTED
				return "";
			case "3": // IN_PROGRESS
				return "live";
			case "51": // FIRST_SET
				return "live";
			case "52": // SECOND_SET
				return "live";
			case "53": // THIRD_SET
				return "live";
			case "54": // FOURTH_SET
				return "live";
			case "55": // FIFTH_SET
				return "live";
			case "100": // ENDED
				return "ended";
			case "20": // WALKOVER
				return "ended walkover";
			case "21": // RETIRED
				return "ended retired";
			case "22": // WALKOVER1
				return "ended walkover";
			case "23": // WALKOVER2
				return "ended walkover";
			case "24": // RETIRED1
				return "ended retired";
			case "25": // RETIRED2
				return "ended retired";
			case "26": // DEFAULTED1
				return "ended defaulted";
			case "27": // DEFAULTED2
				return "ended defaulted";
			case "14": // POSTPONED
				return "postponed";
			case "15": // DELAYED
				return "delayed";
			case "16": // CANCELED
				return "canceled";
			case "17": // INTERRUPTED
				return "interrupted";
			case "18": // SUSPENSION
				return "suspension";
			case "19": // Cut in half
				return "cut-in-half";
			case "99": // To be determined
				return "to-be-determined";
			default:
				return ""; // Неизвестные статусы
		}
	});

	Handlebars.registerHelper("winnerClass", function (homeScores, awayScores) {
		return homeScores > awayScores ? "winner" : "";
	});

	Handlebars.registerHelper("displayScore", function (status, scores) {
		const statusesWithDash = ["1", "9", "10", "11", "12", "13"];

		if (statusesWithDash.includes(String(status))) {
			return "-";
		}
		return scores?.[0] ?? "-";
	});

	Handlebars.registerHelper("finishedMatchTime", function (matchId) {
		const match = competitionStore
			.getData()
			.flatMap((competition) => competition.matches)
			.find((m) => m.id === matchId);

		if (!match || String(match.status_id) !== "100") {
			return "";
		}

		const kickoffTimestamp =
			match.kickoff_timestamp && match.kickoff_timestamp !== "0" ? match.kickoff_timestamp : match.match_time;

		const kickoffDate = new Date(kickoffTimestamp * 1000);
		const today = new Date();

		const isToday =
			kickoffDate.getDate() === today.getDate() &&
			kickoffDate.getMonth() === today.getMonth() &&
			kickoffDate.getFullYear() === today.getFullYear();

		if (isToday) {
			return kickoffDate.toLocaleTimeString([], {
				hour: "2-digit",
				minute: "2-digit",
			});
		}

		return `${kickoffDate.getDate().toString().padStart(2, "0")}.${(kickoffDate.getMonth() + 1)
			.toString()
			.padStart(2, "0")} ${kickoffDate.toLocaleTimeString([], {
			hour: "2-digit",
			minute: "2-digit",
		})}`;
	});

	Handlebars.registerHelper("matchTimeOrBreak", function (matchId) {
		const match = competitionStore
			.getData()
			.flatMap((competition) => competition.matches)
			.find((m) => m.id === matchId);

		if (!match) {
			return "Матч не найден"; // Можно заменить на i18next.t("match_not_found")
		}

		const status = String(match.status_id); // Приводим к строке для switch
		const kickoffTimestamp =
			match.kickoff_timestamp && match.kickoff_timestamp !== "0" ? match.kickoff_timestamp : match.match_time;

		const startTime = new Date(kickoffTimestamp * 1000);
		const today = new Date();

		const isToday =
			startTime.getDate() === today.getDate() &&
			startTime.getMonth() === today.getMonth() &&
			startTime.getFullYear() === today.getFullYear();

		switch (status) {
			case "0": // ABNORMAL (Suggest Hiding)
				return i18next.t("hidden"); // "Скрыто"

			case "1": // NOT_STARTED
				if (isToday) {
					return startTime.toLocaleTimeString([], {
						hour: "2-digit",
						minute: "2-digit",
					});
				} else {
					return `${startTime.getDate().toString().padStart(2, "0")}.${(startTime.getMonth() + 1)
						.toString()
						.padStart(2, "0")} ${startTime.toLocaleTimeString([], {
						hour: "2-digit",
						minute: "2-digit",
					})}`;
				}

			case "3": // IN_PROGRESS
				return i18next.t("match_start"); // "Матч начался"

			case "51": // FIRST_SET
				return `1 ${i18next.t("set")}`; // "1 Сет"

			case "52": // SECOND_SET
				return `2 ${i18next.t("set")}`; // "2 Сет"

			case "53": // THIRD_SET
				return `3 ${i18next.t("set")}`; // "3 Сет"

			case "54": // FOURTH_SET
				return `4 ${i18next.t("set")}`; // "4 Сет"

			case "55": // FIFTH_SET
				return `5 ${i18next.t("set")}`; // "5 Сет"

			case "100": // ENDED
				return i18next.t("match_ended"); // "Завершён"

			case "20": // WALKOVER
				return i18next.t("walkover"); // Добавим этот ключ

			case "21": // RETIRED
				return i18next.t("retired"); // Добавим этот ключ

			case "22": // WALKOVER1
				return i18next.t("walkover1"); // Добавим этот ключ

			case "23": // WALKOVER2
				return i18next.t("walkover2"); // Добавим этот ключ

			case "24": // RETIRED1
				return i18next.t("retired1"); // Добавим этот ключ

			case "25": // RETIRED2
				return i18next.t("retired2"); // Добавим этот ключ

			case "26": // DEFAULTED1
				return i18next.t("defaulted1"); // Добавим этот ключ

			case "27": // DEFAULTED2
				return i18next.t("defaulted2"); // Добавим этот ключ

			case "14": // POSTPONED
				return i18next.t("postponed"); // Добавим этот ключ

			case "15": // DELAYED
				return i18next.t("delay"); // "Задержка"

			case "16": // CANCELED
				return i18next.t("cancelled"); // "Отменён"

			case "17": // INTERRUPTED
				return i18next.t("interrupt"); // "Прерван"

			case "18": // SUSPENSION
				return i18next.t("suspension"); // Добавим этот ключ

			case "19": // Cut in half
				return i18next.t("cut_in_half"); // "Остановлен"

			case "99": // To be determined
				return i18next.t("to_be_determined"); // "Определяется"

			default:
				return i18next.t("hidden"); // "Скрыто" для неизвестных статусов
		}
	});

	function toggleHide() {
		document.querySelectorAll(".matches-tennis__ligue").forEach((parentBlock) => {
			const toggleButton = parentBlock.querySelector(".matches-tennis__ligue-hide");

			if (toggleButton) {
				toggleButton.addEventListener("click", function (event) {
					if (event.target.tagName === "A" || event.target.closest("a")) {
						return;
					}
					parentBlock.classList.toggle("hidden");

					const linkElement = toggleButton.querySelector("a");
					const spanElement = toggleButton.querySelector("span");

					if (linkElement && spanElement) {
						linkElement.classList.toggle("hidden");
						spanElement.classList.toggle("hidden");
					}
				});
			}
		});
	}
});

Handlebars.registerHelper("isPinned", function (competitionId) {
	const pinned = JSON.parse(localStorage.getItem("pinnedTennis")) || [];
	return pinned.includes(competitionId) ? "active" : "";
});

function initToggleFav() {
	const matchesLigueBlocks = document.querySelectorAll(".matches-tennis__ligue");

	matchesLigueBlocks.forEach((block) => {
		const addFavButtons = block.querySelectorAll(".fav.addfav");
		const addLigueButton = block.querySelector(".fav.addligue");

		addFavButtons.forEach((button) => {
			button.addEventListener("click", (event) => {
				event.stopPropagation();
				event.preventDefault();

				toggleFav(
					button.closest(".matches-tennis__item").dataset.match_id,
					button,
					!button.classList.contains("active")
				);
			});
		});

		if (addLigueButton) {
			addLigueButton.addEventListener("click", () => {
				const shouldActivate = !addLigueButton.classList.contains("active");
				addLigueButton.classList.toggle("active", shouldActivate);
				const matchItems = block.querySelectorAll(".matches-tennis__item");

				matchItems.forEach((item) => {
					const matchId = item.dataset.match_id;
					const button = item.querySelector(".fav.addfav");
					toggleFav(matchId, button, shouldActivate);
				});
			});
		}
	});

	// Load and apply the favorite matches from localStorage
	const favorites = getFavorites();
	const favElements = Array.from(document.querySelectorAll(".fav.addfav"));
	favElements.forEach((btn) => {
		const matchId = btn.closest(".matches-tennis__item").dataset.match_id;
		if (favorites.includes(matchId)) {
			btn.classList.add("active");
		}
		// Set .fav.addligue active if any matches are favorited
		btn
			.closest(".matches-tennis__ligue")
			.querySelector(".fav.addligue")
			.classList.toggle(
				"active",
				Array.from(btn.closest(".matches-tennis__ligue").querySelectorAll(".matches-tennis__item")).some((item) =>
					favorites.includes(item.dataset.match_id)
				)
			);
	});
}

function toggleFav(matchId, button, activate) {
	const favorites = getFavorites();
	const index = favorites.indexOf(matchId);

	if (activate && index === -1) {
		favorites.push(matchId);
		button.classList.add("active");
	} else if (!activate && index !== -1) {
		favorites.splice(index, 1);
		button.classList.remove("active");
	}

	saveFavorites(favorites);

	// Update the league button active state based on current match item states
	const leagueButton = button.closest(".matches-tennis__ligue").querySelector(".fav.addligue");
	const matchItems = button.closest(".matches-tennis__ligue").querySelectorAll(".matches-tennis__item");

	// Set .fav.addligue active if any match is favorited
	const anyFav = Array.from(matchItems).some((item) => item.querySelector(".fav.addfav").classList.contains("active"));
	leagueButton.classList.toggle("active", anyFav);
}

function getFavorites() {
	try {
		const favorites = JSON.parse(localStorage.getItem("favorit_tn"));
		return Array.isArray(favorites) ? favorites : [];
	} catch {
		return [];
	}
}

function saveFavorites(favorites) {
	localStorage.setItem("favorit_tn", JSON.stringify(favorites));
}

Handlebars.registerHelper("getLocalizedName", function (names) {
	if (!names || typeof names !== "object") return "No name provided";
	const currentLocale = i18next.language || "en";
	const localeKey = `name_${currentLocale.toLowerCase()}`;
	return names[localeKey] || names["name_en"] || "Unknown";
});

Handlebars.registerHelper("matchType", function (type) {
	// Маппинг типов матчей
	const typeMap = {
		1: { en: "Singles", ru: "Одиночный" },
		2: { en: "Doubles", ru: "Парный" },
		3: { en: "Mixed", ru: "Смешанный" },
	};

	// Получаем текущий язык из i18next
	const currentLanguage = i18next.language || "en"; // По умолчанию "en", если язык не определён

	// Проверяем, есть ли тип в маппинге
	if (!typeMap[type]) {
		return ""; // Возвращаем заглушку, если тип не найден
	}

	// Возвращаем перевод для текущего языка
	return typeMap[type][currentLanguage] || typeMap[type].en; // Фallback на английский, если язык не поддерживается
});

Handlebars.registerHelper("getSetScores", function (scores) {
	const periods = ["p1", "p2", "p3", "p4", "p5", "x1", "x2", "x3", "x4", "x5"];
	const setScores = [];

	periods.forEach((period) => {
		if (scores[period] && scores[period].length === 2 && (scores[period][0] > 0 || scores[period][1] > 0)) {
			const isExtraSet = period.startsWith("x");
			setScores.push({
				homeScore: scores[period][0],
				awayScore: scores[period][1],
				isExtraSet: isExtraSet,
				extraScore: isExtraSet ? scores[period][0] : null, // Дублируем homeScore для примера
			});
		}
	});

	return setScores;
});

Handlebars.registerHelper("isMatchLive", function (statusId) {
	const liveStatuses = ["3", "51", "52", "53", "54", "55"]; // IN_PROGRESS и сеты
	return liveStatuses.includes(String(statusId));
});

Handlebars.registerHelper("isMatchEnded", function (statusId) {
	const liveStatuses = ["100"]; // IN_PROGRESS и сеты
	return liveStatuses.includes(String(statusId));
});

Handlebars.registerHelper("isBall", function (serving_side, data) {
	if (serving_side !== data) return "";
	return "ball";
});

Handlebars.registerHelper("or", function (value, fallback) {
	return value !== undefined && value !== null && value !== "" ? value : fallback;
});

Handlebars.registerHelper("hasSubs", function (subs) {
	return Array.isArray(subs) && subs.length > 0;
});

Handlebars.registerHelper("getRoundWin", function (match, side) {
	if (!match || !("round_win" in match)) {
		return "";
	}
	return match.round_win === side ? "win" : "";
});

Handlebars.registerHelper("getSetdWin", function (match, side) {
	if (!match || !("set_win" in match)) {
		return "";
	}
	return match.set_win === side ? "active" : "";
});

Handlebars.registerHelper("extra", function (key, index, scores) {
	// Маппинг соответствий ключей
	const keyMap = {
		p1: "x1",
		p2: "x2",
		p3: "x3",
		p4: "x4",
		p5: "x5",
	};

	// Если ключ есть в маппинге
	if (keyMap[key]) {
		const targetArray = scores[keyMap[key]];
		// Проверяем, существует ли массив и есть ли в нем элементы
		if (Array.isArray(targetArray) && targetArray.length > index) {
			return targetArray[index];
		}
		return ""; // Возвращаем пустую строку, если данных нет
	}
	return ""; // Возвращаем пустую строку, если данных нет
});

Handlebars.registerHelper("hasP", function (key) {
	return typeof key === "string" && key.toLowerCase().includes("p") && key.toLowerCase() !== "pt";
});

Handlebars.registerHelper("isNotEmpty", function (array) {
	return Array.isArray(array) && array.length > 0;
});

Handlebars.registerHelper("getMatchLink", function (matchIds, homeTeam, awayTeam) {
	if (matchIds && matchIds[0] && homeTeam && awayTeam) {
		// Явная проверка matchIds и matchIds[0]
		return `/statistics/tennis/match/${matchIds[0]}`;
	}
	return "#";
});

document.addEventListener("DOMContentLoaded", () => {
	const storedFavorites = localStorage.getItem("favorite_teams");
	const favoriteIds = storedFavorites ? JSON.parse(storedFavorites) : {};
	document.querySelectorAll(".addfavteam").forEach((element) => {
		const teamId = element.getAttribute("data-teamid");
		const sportType = "tn";
		if (teamId && favoriteIds[sportType] && favoriteIds[sportType].includes(teamId)) {
			element.classList.add("active");
		}
	});
});
