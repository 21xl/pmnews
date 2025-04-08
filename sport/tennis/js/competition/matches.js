import Handlebars from "handlebars";
import matcheserror from "../../templates/matches-error.html";
import templateSource from "../../templates/match-template.html";
import templateSourceRound from "../../templates/match-round-template.html";
import templatefav from "../../templates/fav-team-template.html";
import templatefavempty from "../../templates/fav-team-template-empty.html";
import favoriteTeamsStore from "../favoriteTeamsStore";
import pinned from "../pinned.store";
import competitionStore from "../competition.store";
import { registerHandlebarsHelpers } from "../helpers.js";
import {
  updateMatchInDOM,
  updateIncident,
  getMatchTimeOrBreak,
  toggleHide,
  initToggleFav,
} from "../utils.js";
import { DiffDOM } from "diff-dom";
const dd = new DiffDOM();
const apiUrl = process.env.API_URL || "http://localhost:3277";

jQuery(document).ready(function ($) {
  const competitionId = $(".statistics-competition").data("competition");
  const template = Handlebars.compile(templateSource);
  const mEerortemplate = Handlebars.compile(matcheserror);
  const templateRound = Handlebars.compile(templateSourceRound);
  const matchesMain = $(".matches-tennis__main");
  const tabValue = $(".statistics-category__links").data("tab");

  registerHandlebarsHelpers();
  Handlebars.registerHelper("isPinned", (competitionId) => {
    const pinned = JSON.parse(localStorage.getItem("pinnedTennis")) || [];
    return pinned.includes(competitionId) ? "active" : "";
  });

  function getPinnedItems() {
    return JSON.parse(localStorage.getItem("pinnedTennis")) || [];
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
    const differences = dd.diff(targetElement, tempContainer, {
      filterOuterDiff: (node) => !node.closest?.(".matches-tennis__item-fav"),
    });
    dd.apply(targetElement, differences);
  });

  function updatePinnedItems(id) {
    const pinnedItems = getPinnedItems();
    const index = pinnedItems.indexOf(id);
    if (index === -1) pinnedItems.push(id);
    else pinnedItems.splice(index, 1);
    localStorage.setItem("pinnedTennis", JSON.stringify(pinnedItems));
    pinned.updatePinned(id);
  }

  function renderMatches(data, filterType = null) {
    // Определяем группы статусов
    const statusGroups = {
      scheduled: ["1"], // NOT_STARTED
      live: ["3", "51", "52", "53", "54", "55"], // IN_PROGRESS и сеты
      results: ["100", "20", "21", "22", "23", "24", "25", "26", "27"], // ENDED и варианты завершения
    };

    // Фильтруем матчи
    data.matches = data.matches
      .filter((match) => {
        if (filterType && statusGroups[filterType]) {
          return statusGroups[filterType].includes(String(match.status_id));
        }
        // Универсальная фильтрация: исключаем нежелательные статусы
        const excludeStatuses = [
          "0",
          "9",
          "10",
          "11",
          "12",
          "13",
          "14",
          "15",
          "16",
          "17",
          "18",
          "19",
          "99",
        ];
        return !excludeStatuses.includes(String(match.status_id));
      })
      .sort((a, b) => {
        if (filterType) {
          // Если указан конкретный тип, сортируем только по времени
          return a.match_time - b.match_time;
        }

        // Универсальная сортировка: live -> scheduled -> ended
        const aIsLive = statusGroups.live.includes(String(a.status_id));
        const bIsLive = statusGroups.live.includes(String(b.status_id));
        const aIsEnded = statusGroups.ended.includes(String(a.status_id));
        const bIsEnded = statusGroups.ended.includes(String(b.status_id));

        if (aIsLive !== bIsLive) return aIsLive ? -1 : 1;
        if (aIsEnded !== bIsEnded) return aIsEnded ? 1 : -1;
        return a.match_time - b.match_time;
      });

    matchesMain.html(data.matches.length > 0 ? template(data) : mEerortemplate);

    toggleHide();
    initToggleFav("favorit_tn");
  }

  function renderMatchesWithoutSorting() {
    const data = competitionStore.getData();
    console.log(tabValue);
    if (tabValue) return renderMatches(data[0], tabValue);
    renderMatches(data[0], "scheduled");
  }

  async function fetchAndRenderMatches() {
    try {
      const response = await fetch(
        `${apiUrl}/api/tennis/matches/by-tournament?tournament_id=${competitionId}`
      );
      if (!response.ok)
        throw new Error(`Ошибка запроса: ${response.statusText}`);
      const data = await response.json();
      competitionStore.setData(data);
      renderMatchesWithoutSorting();
    } catch (error) {
      console.error("Ошибка при загрузке данных:", error.message);
    }
  }

  document.addEventListener("click", function (event) {
    const target = event.target.closest(".pin");
    if (target) {
      const competitionId = target.getAttribute("data-competition_id");

      updatePinnedItems(competitionId);

      const matchingPins = document.querySelectorAll(
        `.pin[data-competition_id="${competitionId}"]`
      );

      const isActive = target.classList.contains("active");

      matchingPins.forEach((matchingPin) => {
        if (isActive) {
          matchingPin.classList.remove("active");
        } else {
          matchingPin.classList.add("active");
        }
      });
    }
  });

  if (matchesMain && competitionId) fetchAndRenderMatches();

  const socket = new WebSocket("wss://sync-stage.sport-pulse.kz");
  socket.onopen = () => {};
  socket.onmessage = (event) => {
    const data = JSON.parse(event.data);
    data.forEach((matchUpdate) => {
      if (matchUpdate.score)
        competitionStore.updateMatchData(matchUpdate.score);
      if (matchUpdate.incidents) updateIncident(matchUpdate);
    });
  };
  socket.onclose = () => {};

  competitionStore.onDataChange(({ updatedMatch }) => {
    if (updatedMatch) updateMatchInDOM(updatedMatch);
  });
});
