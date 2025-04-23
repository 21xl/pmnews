import Handlebars from "handlebars";
import review from "../../templates/single-review.html";
import single from "../../templates/single-row-review.html";
import statTemp from "../../templates/single-statistics.html";
import standTemp from "../../templates/single-standings.html";
import h2hTemp from "../../templates/single-h2h.html";
import oddsTemp from "../../templates/single-odds.html";
import matcheserror from "../../templates/matches-error.html";
import templatefav from "../../templates/fav-team-template.html";
import templatefavempty from "../../templates/fav-team-template-empty.html";
import favoriteTeamsStore from "../favoriteTeamsStore";
import scoreball from "./single-ball.html";
import scorebar from "./scorebar-main.html";
import { registerHandlebarsHelpers } from "../helpers.js";
import { updateMatchInDOM, updateIncident, getMatchTimeOrBreak, toggleHide, initToggleFav } from "../utils.js";
const apiUrl = process.env.API_URL || "http://localhost:3277";
const wsUrl = process.env.WEBSOCKET || "ws://localhost:3277/websocket";

import pinned from "../pinned.store";
import incidents from "./incidents.store";
import playerStore from "./player.store";
import competitionStore from "./competition.store";
import i18next from "../i18n";
import { DiffDOM } from "diff-dom";

const dd = new DiffDOM();

const reviewlate = Handlebars.compile(review);
const item = Handlebars.compile(single);
const ball = Handlebars.compile(scoreball);
const score = Handlebars.compile(scorebar);
const statTemplate = Handlebars.compile(statTemp);
const standTemplate = Handlebars.compile(standTemp);
const h2hTemplate = Handlebars.compile(h2hTemp);
const oddsTemplate = Handlebars.compile(oddsTemp);
const mEerortemplate = Handlebars.compile(matcheserror);

let match;
let tabs = [];
let p_series = false;
let away_t = null;
let home_t = null;
let coachesData = null;
let compId = "";
let html = "";
let tabHandlers = {};

jQuery(document).ready(function ($) {
	registerHandlebarsHelpers();
	const today = new Date();

	const matchId = $(".match").data("matchid");
	if (!matchId) {
		return;
	}

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

	const tabsItems = document.querySelectorAll(".tabs__item");
	const review =
		document.querySelectorAll(".match__review").length > 0 ? document.querySelectorAll(".match__review")[0] : null;
	const composition =
		document.querySelectorAll(".match__composition").length > 0
			? document.querySelectorAll(".match__composition")[0]
			: null;
	const statistics =
		document.querySelectorAll(".match__statistics-list").length > 0
			? document.querySelectorAll(".match__statistics-list")[0]
			: null;
	const standings =
		document.querySelectorAll(".table.standings").length > 0 ? document.querySelectorAll(".table.standings")[0] : null;
	const match__h2h =
		document.querySelectorAll(".match__h2h").length > 0 ? document.querySelectorAll(".match__h2h")[0] : null;
	const matchOdds =
		document.querySelectorAll(".odds .table__wrapper").length > 0
			? document.querySelectorAll(".odds .table__wrapper")[0]
			: null;
	const timeElement =
		document.querySelectorAll(".scoreboard__minute").length > 0
			? document.querySelectorAll(".scoreboard__minute")[0]
			: null;
	const scoreboard__goals =
		document.querySelectorAll(".scoreboard__goals").length > 0
			? document.querySelectorAll(".scoreboard__goals")[0]
			: null;

	function getPinnedItems() {
		return JSON.parse(localStorage.getItem("pinned")) || [];
	}

	if (tabsItems.length > 0) {
		tabsItems.forEach(async (item) => {
			if (item.classList.contains("active")) {
				const activeTab = item.getAttribute("data-status");

				tabsItems.forEach((tab) => {
					tab.style.pointerEvents = "none";
				});

				try {
					match = await fetchData(matchId, "review");
					competitionStore.setData(match);
					tabHandlers = {
						review: async () => handleReviewTab(),
						odds: async () => handleOddsTab(),
						h2h: async () => handleH2HTab(match),
						statistics: async () => handleStatisticsTab(match),
						standings: async () => handleStandingsTab(match),
						squad: async () => handleSquadTab(),
					};

					if (tabHandlers[activeTab]) {
						await tabHandlers[activeTab]();
					}
				} catch (error) {
					console.error("Ошибка при обработке вкладки:", error);
				} finally {
					// Убираем pointer-events: none после завершения (успешного или с ошибкой)
					tabsItems.forEach((tab) => {
						tab.style.pointerEvents = "";
					});
				}
			}
		});

		const handleReviewTab = async () => {
			if (!tabs.includes("review")) tabs.push("review");
			const data = competitionStore.getData();
			console.log(data);
			const html = reviewlate(data);
			review.innerHTML = html;

			initToggleFav();
		};

		const handleOddsTab = async () => {
			const { odds } = await fetchData(matchId, "odds");
			if (!odds || odds.length === 0) return;
			const row = odds.find((odd) => ["22", "2", "9"].includes(odd.company_id));
			if (!row) return;
			const rows = [row];
			matchOdds.innerHTML = oddsTemplate({ rows });
			if (!tabs.includes("odds")) tabs.push("odds");
		};

		// const handleH2HTab = async (data) => {
		//   console.log("test");
		//   let h2hresp = data;
		//   if (!data) {
		//     resp = await fetchData(matchId, "h2h");
		//     if (resp.status && resp.status !== 200)
		//       return (match__h2h.innerHTML = mEerortemplate());
		//     h2hresp = resp;
		//   }

		//   const { h2h_home, h2h_away, h2h } = h2hresp;

		//   const h2hHome = h2h_home.map((match) => ({
		//     ...match,
		//     result: determineResult(match, h2hresp.home_team_id),
		//   }));
		//   const h2hAway = h2h_away.map((match) => ({
		//     ...match,
		//     result: determineResult(match, h2hresp.away_team_id),
		//   }));

		//   const uniqueGroundIds = new Set();

		//   // Iterate through h2hHome
		//   h2hHome.forEach((match) => {
		//     if (match.tournament && match.tournament.ground_id) {
		//       uniqueGroundIds.add(match.tournament.ground_id);
		//     }
		//   });

		//   // Iterate through h2hAway
		//   h2hAway.forEach((match) => {
		//     if (match.tournament && match.tournament.ground_id) {
		//       uniqueGroundIds.add(match.tournament.ground_id);
		//     }
		//   });

		//   // Convert Set to array if needed
		//   const groundIds = Array.from(uniqueGroundIds);

		//   console.log("groundIds", groundIds);

		//   match__h2h.innerHTML = h2hTemplate({
		//     h2hHome,
		//     h2hAway,
		//     h2h: h2h.length > 0 ? h2h : null,
		//     away: h2hresp.awayTeam,
		//     home: h2hresp.homeTeam,
		//     groundIds,
		//   });
		// };

		const handleH2HTab = async (data) => {
			let h2hresp = data;
			if (!data) {
				resp = await fetchData(matchId, "h2h");
				if (resp.status && resp.status !== 200) return (match__h2h.innerHTML = mEerortemplate());
				h2hresp = resp;
			}

			const { h2h_home, h2h_away, h2h } = h2hresp;

			// Подготовка данных с результатами
			const h2hHome = h2h_home.map((match) => ({
				...match,
				result: determineResult(match, h2hresp.home_team_id),
			}));
			const h2hAway = h2h_away.map((match) => ({
				...match,
				result: determineResult(match, h2hresp.away_team_id),
			}));

			// Собираем уникальные ground_ids
			const uniqueGroundIds = new Set();
			h2hHome.forEach((match) => {
				if (
					match.tournament &&
					match.tournament.ground_id &&
					match.tournament.ground_id !== 4 &&
					match.tournament.ground_id !== 10
				) {
					uniqueGroundIds.add(match.tournament.ground_id);
				}
			});
			h2hAway.forEach((match) => {
				if (
					match.tournament &&
					match.tournament.ground_id &&
					match.tournament.ground_id !== 4 &&
					match.tournament.ground_id !== 10
				) {
					uniqueGroundIds.add(match.tournament.ground_id);
				}
			});
			const groundIds = Array.from(uniqueGroundIds);

			// Функция рендеринга с учетом фильтра
			const renderH2H = (selectedGroundId = 0) => {
				const groundId = parseInt(selectedGroundId, 10) || 0;
				const specialGroundIds = [3, 4, 10]; // Группа ground_id, которые нужно фильтровать вместе

				// Определяем, нужно ли фильтровать по группе specialGroundIds
				const filterByGroup = specialGroundIds.includes(groundId);

				// Фильтруем матчи
				const filteredH2hHome =
					groundId === 0
						? h2hHome // Если groundId = 0, возвращаем все
						: h2hHome.filter((match) =>
								match.tournament
									? filterByGroup
										? specialGroundIds.includes(match.tournament.ground_id) // Фильтр по 3, 4, 10
										: match.tournament.ground_id === groundId // Фильтр по конкретному groundId
									: false
							);

				const filteredH2hAway =
					groundId === 0
						? h2hAway
						: h2hAway.filter((match) =>
								match.tournament
									? filterByGroup
										? specialGroundIds.includes(match.tournament.ground_id)
										: match.tournament.ground_id === groundId
									: false
							);

				const filteredH2h =
					groundId === 0
						? h2h
						: h2h.filter((match) =>
								match.tournament
									? filterByGroup
										? specialGroundIds.includes(match.tournament.ground_id)
										: match.tournament.ground_id === groundId
									: false
							);

				// Рендерим отфильтрованный контент
				match__h2h.innerHTML = h2hTemplate({
					h2hHome: filteredH2hHome,
					h2hAway: filteredH2hAway,
					h2h: filteredH2h.length > 0 ? filteredH2h : null,
					away: h2hresp.awayTeam,
					home: h2hresp.homeTeam,
					groundIds,
					selectedGroundId: groundId, // Передаем выбранный groundId
				});
				initShowMore();
				addTabListeners(groundId); // Передаем текущий groundId
				initToggleFav();
			};

			// Функция добавления слушателей на табы
			const addTabListeners = (activeGroundId) => {
				const tabElements = match__h2h.querySelectorAll(".tabs__item");
				tabElements.forEach((tab) => {
					// Удаляем предыдущие слушатели во избежание дублирования
					tab.removeEventListener("click", tab.clickHandler);

					tab.clickHandler = (e) => {
						const groundId = parseInt(tab.getAttribute("data-ground"), 10) || 0;
						tabElements.forEach((t) => t.classList.remove("active"));
						tab.classList.add("active");
						renderH2H(groundId);
					};

					tab.addEventListener("click", tab.clickHandler);

					// Устанавливаем active класс для текущей вкладки
					const tabGroundId = parseInt(tab.getAttribute("data-ground"), 10) || 0;
					if (tabGroundId === activeGroundId) {
						tab.classList.add("active");
					}
				});
			};
			// Первоначальный рендеринг со всеми покрытиями
			renderH2H(0);

			if (!tabs.includes("h2h")) tabs.push("h2h");
		};

		const handleStatisticsTab = async (data) => {
			let stat;
			stat = data.stats;
			if (!stat) return (statistics.innerHTML = mEerortemplate());
			statistics.innerHTML = statTemplate({ stats: stat });
			initCheckbox();
		};

		const handleStandingsTab = async (match) => {
			const data = await fetchStandings(match.tournament_id);
			if (!data || !standings) return (standings.innerHTML = mEerortemplate());

			standings.innerHTML = standTemplate(data.length > 1 ? data[1] : data[0]);
			initBrackets();
		};

		const observer = new MutationObserver((mutationsList) => {
			mutationsList.forEach((mutation) => {
				if (mutation.type === "attributes" && mutation.attributeName === "class") {
					const target = mutation.target;
					if (target.classList.contains("active")) {
						const status = target.getAttribute("data-status");
						if (!tabs.includes(status)) {
							(async () => {
								try {
									tabs.push(status);
									if (tabHandlers[status]) {
										await tabHandlers[status]();
									}
								} catch (error) {
									console.error("Ошибка при получении данных:", error);
								}
							})();
						}
					}
				}
			});
		});

		const config = { attributes: true, attributeFilter: ["class"] };

		tabsItems.forEach((item) => {
			observer.observe(item, config);
		});
	}

	// function updatePinnedItems(id) {
	//   const pinnedItems = getPinnedItems();
	//   const index = pinnedItems.indexOf(id);

	//   if (index === -1) {
	//     pinnedItems.push(id);
	//   } else {
	//     pinnedItems.splice(index, 1);
	//   }

	//   localStorage.setItem("pinned", JSON.stringify(pinnedItems));

	//   if (id) {
	//     pinned.updatePinned(id);
	//   } else {
	//     if (index !== -1) {
	//       pinned.delPinned(id);
	//     }
	//   }
	// }

	// document.addEventListener("click", function (event) {
	//   const target = event.target.closest(".pin");
	//   if (target) {
	//     const competitionId = target.getAttribute("data-competition_id");
	//     if (!competitionId) return;
	//     updatePinnedItems(competitionId);

	//     const matchingPins = document.querySelectorAll(
	//       `.pin[data-competition_id="${competitionId}"]`
	//     );

	//     const isActive = target.classList.contains("active");

	//     matchingPins.forEach((matchingPin) => {
	//       if (isActive) {
	//         matchingPin.classList.remove("active");
	//       } else {
	//         matchingPin.classList.add("active");
	//       }
	//     });
	//   }
	// });

	function updateLiveMatchesTime() {
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
				const spanElement = timeElement.querySelector("span");
				spanElement.textContent = `${matchMinutes}`;
			}
		}
	}

	let isSyncing = true;

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
			setInterval(() => {
				if (!isSyncing) {
					clearInterval(); // Останавливаем выполнение, если флаг isSyncing false
					return;
				}
				updateLiveMatchesTime();
			}, 60000);
		}, timeUntilNextMinute);
	}

	function stopSync() {
		isSyncing = false; // Устанавливаем флаг для остановки дальнейших обновлений
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
		// Обновление matches__item элементов
		const matchElements = document.querySelectorAll(`.matches__item[data-match_id="${updatedMatch.id}"]`);

		if (matchElements.length) {
			const newItemDom = item(updatedMatch); // Предполагается, что item - это ваша функция генерации HTML
			const tempDiv = document.createElement("div");
			tempDiv.innerHTML = newItemDom;
			const newElement = tempDiv.firstChild;

			matchElements.forEach((matchElement) => {
				const diff = dd.diff(matchElement, newElement);
				if (diff.length) {
					dd.apply(matchElement, diff);
				}
			});
		}

		// Обновление scoreboard__status
		const statusElements = document.querySelectorAll(`.scoreboard__status`);

		if (statusElements.length) {
			const newScoreHtml = ball({
				serving_side: updatedMatch.serving_side,
			}); // Предполагается, что ball - это ваша функция генерации HTML

			const tempScoreDiv = document.createElement("div");
			tempScoreDiv.innerHTML = newScoreHtml;
			const newScoreElement = tempScoreDiv.firstChild;

			statusElements.forEach((statusElement) => {
				const diff = dd.diff(statusElement, newScoreElement);
				if (diff.length) {
					dd.apply(statusElement, diff);
				}
			});
		}

		// Обновление scoreboard__score
		const scoreElements = document.querySelectorAll(`.scoreboard__score`);
		console.log(scoreElements);
		if (scoreElements.length) {
			const newScoreHtml = score(updatedMatch);

			const tempScoreDiv = document.createElement("div");
			tempScoreDiv.innerHTML = newScoreHtml;
			const newScoreElement = tempScoreDiv.firstChild;

			scoreElements.forEach((scoreElement) => {
				const diff = dd.diff(scoreElement, newScoreElement);
				if (diff.length) {
					dd.apply(scoreElement, diff);
				}
			});
		}
	}

	socket.onclose = () => {};

	Handlebars.registerHelper("t", function (key) {
		return i18next.t(key);
	});

	Handlebars.registerHelper("isHomeScoreGreater", function (homeScores) {
		return Array.isArray(homeScores) && homeScores[2] > 0;
	});
});

Handlebars.registerHelper("hasSubs", (subs) => Array.isArray(subs) && subs.length > 0);

Handlebars.registerHelper("isPinned", function (competitionId) {
	const pinned = JSON.parse(localStorage.getItem("pinned")) || [];
	return pinned.includes(competitionId) ? "active" : "";
});

async function fetchData(id, tab = "review") {
	try {
		const response = await fetch(`${apiUrl}/api/tennis/match-details?match_id=${id}&tab=${tab}`);

		if (!response.ok) {
			throw new Error(`Ошибка запроса: ${response.statusText}`);
		}

		const { data } = await response.json();
		return data;
	} catch (error) {
		console.error("Ошибка при загрузке данных:", error.message);
	}
}

Handlebars.registerHelper("switch", function (value, options) {
	this._switch_value_ = value;
	return options.fn(this);
});

Handlebars.registerHelper("case", function (value, options) {
	if (value === this._switch_value_) {
		return options.fn(this);
	}
});

Handlebars.registerHelper("default", function (options) {
	return options.fn(this);
});

Handlebars.registerHelper("teamClass", function (status) {
	switch (String(status)) {
		case "1":
			return "home-team";
		case "2":
			return "away-team";
		default:
			return "";
	}
});

Handlebars.registerHelper("groundSurface", function (groundId) {
	const surfaceMap = {
		1: i18next.t("surface.hard"),
		2: i18next.t("surface.grass"),
		3: i18next.t("surface.clay"),
		4: i18next.t("surface.clay"),
		5: i18next.t("surface.hardcourtOutdoor"),
		6: i18next.t("surface.carpetIndoor"),
		7: i18next.t("surface.syntheticIndoor"),
		8: i18next.t("surface.syntheticOutdoor"),
		9: i18next.t("surface.hardcourtIndoor"),
		10: i18next.t("surface.clay"),
	};

	return surfaceMap[groundId] || "";
});

Handlebars.registerHelper("Player", function (id) {
	if (id == null || typeof id === "undefined") return "";
	const squad = playerStore.getData();
	const player = squad.find((player) => player.id === id);
	if (!player) return "";

	const { name } = player;
	return name;
});

function initCheckbox() {
	const statistics = document.querySelector(".match__statistics-list");

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
		document.querySelectorAll(".match__statistics-bar").forEach((container) => {
			const segments = container.querySelectorAll(".match__statistics-segment");

			const values = Array.from(segments).map((segment) => {
				const value = segment.getAttribute("data-value");
				return !isNaN(value) ? parseFloat(value) : 0;
			});

			const total = values.reduce((sum, value) => sum + value, 0);

			if (total > 0) {
				segments.forEach((segment, index) => {
					const proportion = (values[index] / total) * 100;

					const inner = segment.querySelector(".match__statistics-progress");
					if (inner) inner.style.width = `${proportion}%`;
				});
			}
		});
	}
}

async function fetchStandings(id) {
	try {
		const response = await fetch(`${apiUrl}/api/tennis/matchups/by-tournament?tournament_id=${id}`);
		if (!response.ok) throw new Error(`Ошибка запроса: ${response.statusText}`);
		const { data } = await response.json();

		return data;
	} catch (error) {
		console.error("Ошибка при загрузке данных:", error);
	}
}

Handlebars.registerHelper("inlineStyle", function (color) {
	if (color) {
		return new Handlebars.SafeString(`style="background: ${color};"`);
	}
	return "";
});

Handlebars.registerHelper("groupName", function (group) {
	const letters = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

	const index = parseInt(group, 10) - 1;

	if (index >= 0 && index < letters.length) {
		return letters[index];
	}
	return "";
});

Handlebars.registerHelper("isNotEmpty", function (array) {
	return !Array.isArray(array);
});

Handlebars.registerHelper("add", function (a, b) {
	return a + b;
});

const determineResult = (match, teamId) => {
	const homeScore = match.scores.ft ? match.scores.ft[0] : 0;
	const awayScore = match.scores.ft ? match.scores.ft[1] : 0;

	if (match.home_team_id === teamId) {
		if (homeScore > awayScore) return "win";
		if (homeScore < awayScore) return "loss";
		return "";
	} else if (match.away_team_id === teamId) {
		if (awayScore > homeScore) return "win";
		if (awayScore < homeScore) return "loss";
		return "";
	}

	return "unknown";
};

Handlebars.registerHelper("getLocalized", function (object) {
	const lang = document.documentElement.lang;

	if (object && object[`name_${lang}`]) {
		return object[`name_${lang}`];
	}

	if (object && object.name) {
		return object.name;
	}

	return "";
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

Handlebars.registerHelper("formatDate", function (timestamp) {
	if (!timestamp) return "";

	const date = new Date(timestamp * 1000);
	const day = String(date.getDate()).padStart(2, "0");
	const month = String(date.getMonth() + 1).padStart(2, "0");
	const hours = String(date.getHours()).padStart(2, "0");
	const minutes = String(date.getMinutes()).padStart(2, "0");

	return `${day}.${month} ${hours}:${minutes}`;
});

Handlebars.registerHelper("formatFloat", function (value) {
	if (typeof value !== "number") {
		return value;
	}
	const formatted = value.toFixed(2).toString();
	return formatted.replace(".", ",");
});

Handlebars.registerHelper("checkState", function (state_id) {
	return state_id === 0 || state_id === 1 ? false : true;
});

Handlebars.registerHelper("checkPenalty", function (match) {
	if (!match || typeof match !== "object") return "";
	if (match.parsed_note && match.parsed_note.PEN) return "penalty";
	return "";
});

Handlebars.registerHelper("checkWinner", function (str1, str2) {
	if (str1 === str2) {
		return "win";
	}
	return "";
});

function newIncident(data, block) {
	let html = reviewlate({ incident: data.incident });
	if (!p_series && (data.incident.type === 29 || data.incident.type === 30)) {
		p_series = true;
		html = reviewlate({ incident: { type: 33 } }) + reviewlate({ incident: data.incident });
	}
	block.insertAdjacentHTML("beforeend", html);
}

Handlebars.registerHelper("stattype", function (type) {
	if (type === "percentage") return "%";
	return "";
});

Handlebars.registerHelper("addHiddenClass", function (homeSub, awaySub) {
	return !homeSub && !awaySub ? "hidden" : "";
});

Handlebars.registerHelper("ne", function (a, b) {
	return a !== b;
});

Handlebars.registerHelper("formatDate", function (timestamp, format) {
	const date = new Date(timestamp * 1000);
	const pad = (n) => n.toString().padStart(2, "0");
	const replacements = {
		dd: pad(date.getDate()),
		MM: pad(date.getMonth() + 1),
		HH: pad(date.getHours()),
		mm: pad(date.getMinutes()),
	};
	return format.replace(/(dd|MM|HH|mm)/g, (match) => replacements[match]);
});

Handlebars.registerHelper("displayTime", function (statusId, matchTime) {
	statusId = String(statusId || "0");
	const dt = new Date(matchTime * 1000);
	const isToday = dt.toDateString() === new Date().toDateString();

	const months = {
		January: "January",
		February: "February",
		March: "March",
		April: "April",
		May: "May",
		June: "June",
		July: "July",
		August: "August",
		September: "September",
		October: "October",
		November: "November",
		December: "December",
	};
	

	switch (statusId) {
		case "0":
			return "Hidden";
		case "1":
			return isToday
				? dt.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" })
				: `${dt.getDate()} ${months[dt.toLocaleString("en", { month: "long" })]} ${dt.toLocaleTimeString([], {
						hour: "2-digit",
						minute: "2-digit",
					})}`;
		case "3":
			return "Match Started";
		case "51":
			return "Set 1";
		case "52":
			return "Set 2";
		case "53":
			return "Set 3";
		case "54":
			return "Set 4";
		case "55":
			return "Set 5";
		case "100":
			return "Finished";
		case "20":
			return "Technical Win";
		case "21":
			return "Withdrawal";
		case "14":
			return "Postponed";
		case "15":
			return "Delayed";
		case "16":
			return "Cancelled";
		case "17":
			return "Interrupted";
		case "18":
			return "Suspended";
		case "19":
			return "Halved";
		case "99":
			return "To Be Determined";
		default:
			return `${dt.getDate()} ${months[dt.toLocaleString("en", { month: "long" })]}`;
	}
	
});

Handlebars.registerHelper("active", function (a, b) {
	if (a === b) return "active";
	return "";
});

Handlebars.registerHelper("showMore", function (a) {
	if (a > 5) return "hidden";
	return "";
});

function initShowMore(selector = ".show-more", initialCount = 5) {
	document.querySelectorAll(selector).forEach((button) => {
		const { type } = button.dataset;
		const parent = button.closest(".matches__ligue-content");

		// Проверка: если родительский элемент не найден, пропускаем итерацию
		if (!parent) {
			button.classList.add("hidden"); // Скрываем кнопку
			return;
		}

		const items = parent.querySelectorAll(`a.${type}-show-more`);

		// Проверка: если элементов нет или их меньше initialCount, скрываем кнопку
		if (items.length === 0 || items.length <= initialCount) {
			button.classList.add("hidden");
			return;
		}

		let isExpanded = false;

		const updateVisibility = () =>
			items.forEach((item, i) => item.classList.toggle("hidden", i >= initialCount && !isExpanded));

		updateVisibility();
		button.classList.remove("hidden"); // Показываем кнопку, если элементов достаточно
		button.addEventListener("click", () => {
			isExpanded = !isExpanded;
			updateVisibility();
			button.textContent = isExpanded ? "Show less" : "Show more";
		});
	});
}

function initBrackets() {
	const wrapper = document.querySelector(".brackets__wrapper");
	const prevBtn = document.querySelector(".brackets__navigation-prev");
	const nextBtn = document.querySelector(".brackets__navigation-next");
	const navigation = document.querySelector(".brackets__navigation");

	if (!wrapper || !prevBtn || !nextBtn || !navigation) return;

	let currentIndex = 0;

	const remToPx = (rem) => rem * parseFloat(getComputedStyle(document.documentElement).fontSize);
	const getItemsCount = () => document.querySelectorAll(".brackets__head-item").length;
	const getVisibleItemsCount = () => (window.innerWidth <= 541 ? 1 : 4);
	const getMaxIndex = () => Math.max(0, getItemsCount() - getVisibleItemsCount());

	const updateNavigationVisibility = () => {
		const totalItems = getItemsCount();
		const visibleItems = getVisibleItemsCount();
		const showNavigation = totalItems > visibleItems;

		navigation.classList.toggle("active", showNavigation);
		prevBtn.classList.toggle("active", currentIndex > 0);
		nextBtn.classList.toggle("active", currentIndex < getMaxIndex());
	};

	const scrollToItem = (index) => {
		const offset = -index * remToPx(window.innerWidth <= 541 ? 20.4 : 15.5);
		wrapper.style.transform = `translateX(${offset}px)`;
	};

	nextBtn.addEventListener("click", () => {
		if (currentIndex < getMaxIndex()) {
			currentIndex++;
			scrollToItem(currentIndex);
			updateNavigationVisibility();
		}
	});

	prevBtn.addEventListener("click", () => {
		if (currentIndex > 0) {
			currentIndex--;
			scrollToItem(currentIndex);
			updateNavigationVisibility();
		}
	});

	window.addEventListener("resize", () => {
		currentIndex = Math.min(currentIndex, getMaxIndex());
		scrollToItem(currentIndex);
		updateNavigationVisibility();
	});

	updateNavigationVisibility();
}
