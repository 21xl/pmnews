import Handlebars from "handlebars";
import templateSource from "../../templates/standings-template.html";
import matcheserror from "../../templates/matches-error.html";
import templatefav from "../../templates/fav-team-template.html";
import templatefavempty from "../../templates/fav-team-template-empty.html";
import favoriteTeamsStore from "../favoriteTeamsStore";
import pinned from "../pinned.store";
import competitionStore from "../standings.store";
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
    const differences = dd.diff(targetElement, tempContainer);
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

  function renderMatches(data) {
    console.log(data);
    if (data.length == 0) {
      matchesMain.html(mEerortemplate);
    } else {
      matchesMain.html(template(data.length > 1 ? data[1] : data[0]));
    }

    toggleHide();
  }

  function renderMatchesWithoutSorting() {
    const data = competitionStore.getData();
    renderMatches(data);
  }

  async function fetchAndRenderMatches() {
    try {
      const response = await fetch(
        `${apiUrl}/api/tennis/matchups/by-tournament?tournament_id=${competitionId}`
      );
      if (!response.ok)
        throw new Error(`Ошибка запроса: ${response.statusText}`);
      const { data } = await response.json();
      competitionStore.setData(data);
      renderMatchesWithoutSorting();
    } catch (error) {
      console.error("Ошибка при загрузке данных:", error.message);
    }
  }

  document.addEventListener("click", (event) => {
    const target = event.target.closest(".pin");
    if (target) {
      const competitionId = target.getAttribute("data-competition_id");
      updatePinnedItems(competitionId);
      document
        .querySelectorAll(`.pin[data-competition_id="${competitionId}"]`)
        .forEach((pin) =>
          pin.classList.toggle("active", !pin.classList.contains("active"))
        );
    }
  });

  if (matchesMain && competitionId) fetchAndRenderMatches();

  // const socket = new WebSocket("wss://sync-stage.sport-pulse.kz");
  // socket.onopen = () => {};
  // socket.onmessage = (event) => {
  //   const data = JSON.parse(event.data);
  //   data.forEach((matchUpdate) => {
  //     if (matchUpdate.score)
  //       competitionStore.updateMatchData(matchUpdate.score);
  //     if (matchUpdate.incidents) updateIncident(matchUpdate);
  //   });
  // };
  // socket.onclose = () => {};

  // competitionStore.onDataChange(({ updatedMatch }) => {
  //   if (updatedMatch) updateMatchInDOM(updatedMatch);
  // });
});
