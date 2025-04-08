import incidents from "./incidents.store";
import i18next from "./i18n";

// Обновление DOM-элемента матча
export function updateMatchInDOM(updatedMatch) {
  const matchElement = document.querySelector(
    `.matches-tennis__item[data-match_id="${updatedMatch.id}"]`
  );
  if (!matchElement) return;

  const timeElement = matchElement.querySelector(".matches-tennis__item-time");
  if (timeElement) {
    timeElement.textContent = getMatchTimeOrBreak(
      updatedMatch.id,
      updatedMatch.status_id,
      updatedMatch.kickoff_timestamp
    );
  }

  const scoreElement = matchElement.querySelector(
    ".matches-tennis__item-score"
  );
  if (scoreElement) {
    const [homeScore, awayScore] = scoreElement.querySelectorAll("span");
    homeScore.textContent = updatedMatch.home_scores[0];
    awayScore.textContent = updatedMatch.away_scores[0];
  }

  const homeTeamWrapper = matchElement.querySelector(
    ".matches-tennis__item-team:first-child"
  );
  const awayTeamWrapper = matchElement.querySelector(
    ".matches-tennis__item-team:last-child"
  );

  updateWinnerClass(
    homeTeamWrapper,
    updatedMatch.home_scores[0],
    updatedMatch.away_scores[0]
  );
  updateWinnerClass(
    awayTeamWrapper,
    updatedMatch.away_scores[0],
    updatedMatch.home_scores[0]
  );
}

// Обновление класса победителя
export function updateWinnerClass(teamElement, teamScore, opponentScore) {
  teamElement.classList.remove("winner");
  if (teamScore > opponentScore) teamElement.classList.add("winner");
}

// Получение времени матча или перерыва
export function getMatchTimeOrBreak(id, status, kickoffTimestamp) {
  const currentTimestamp = Math.floor(Date.now() / 1000);
  const matchElement = document.querySelector(`[data-match_id="${id}"]`);
  let matchMinutes;

  switch (String(status)) {
    case "0":
      return i18next.t("hidden");
    case "1":
      const startTime = new Date(kickoffTimestamp * 1000);
      return startTime.toLocaleTimeString([], {
        hour: "2-digit",
        minute: "2-digit",
      });
    case "2":
      matchMinutes = Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 1;
      matchElement.classList.add("live");
      return `${matchMinutes}`;
    case "3":
      matchElement.classList.remove("live");
      matchElement.classList.add("pause");
      return i18next.t("half_time");
    case "4":
      matchElement.classList.remove("pause");
      matchMinutes =
        Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 46;
      matchElement.classList.add("live");
      return `${matchMinutes}`;
    case "5":
      matchMinutes =
        Math.floor((currentTimestamp - kickoffTimestamp) / 60) - 45;
      return `90+${matchMinutes}`;
    case "7":
      return i18next.t("penalty_shootout");
    case "8":
      if (matchElement) {
        setTimeout(() => {
          const parentElement = matchElement.closest(
            ".matches-tennis__ligue-content"
          );
          matchElement.remove();
          if (parentElement && parentElement.children.length === 0) {
            const leagueElement = parentElement.closest(
              ".matches-tennis__ligue"
            );
            if (leagueElement) leagueElement.remove();
          }
        }, 60000);
      }
      matchElement.classList.add("ended");
      return i18next.t("match_ended");
    case "9":
      return i18next.t("delay");
    case "10":
      return i18next.t("interrupt");
    case "11":
      return i18next.t("cut_in_half");
    case "12":
      return i18next.t("cancelled");
    case "13":
      return "--:--"; // или i18next.t("to_be_determined")
    case "51":
      return `1 ${i18next.t("set")}`;
    case "52":
      return `2 ${i18next.t("set")}`;
    case "53":
      return `3 ${i18next.t("set")}`;
    case "54":
      return `4 ${i18next.t("set")}`;
    case "55":
      return `5 ${i18next.t("set")}`;
    case "100":
      return i18next.t("match_ended");
    default:
      return i18next.t("hidden");
  }
}

// Обработка инцидентов
export function updateIncident(incidentData) {
  const result = incidents.addIncidents(incidentData);
  if (!result) return;
  const { id, incident } = result;

  switch (incident.type) {
    case 1:
      handleGoal(id);
      break;
    case 8:
      handlePenalty(id);
      break;
    case 27:
      handlePenaltyEnd(id);
      break;
  }
}

export function handleGoal(id) {
  const goalElement = document.querySelector(`[data-match_id="${id}"]`);
  if (goalElement) {
    goalElement.classList.add("goal");
    goalElement.classList.remove("penalty");
    setTimeout(() => goalElement.classList.remove("goal"), 25000);
  }
}

export function handlePenalty(id) {
  const goalElement = document.querySelector(`[data-match_id="${id}"]`);
  if (goalElement) goalElement.classList.add("penalty");
}

export function handlePenaltyEnd(id) {
  const goalElement = document.querySelector(`[data-match_id="${id}"]`);
  if (goalElement) goalElement.classList.remove("penalty");
}

// Переключение видимости лиг
export function toggleHide() {
  document.querySelectorAll(".matches-tennis__ligue").forEach((parentBlock) => {
    const toggleButton = parentBlock.querySelector(
      ".matches-tennis__ligue-hide"
    );
    if (toggleButton) {
      toggleButton.addEventListener("click", function (event) {
        if (event.target.tagName === "A" || event.target.closest("a")) return;
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

// Работа с избранным
export function initToggleFav(getFavoritesKey = "favorit_tn") {
  const matchesLigueBlocks = document.querySelectorAll(
    ".matches-tennis__ligue"
  );

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
          !button.classList.contains("active"),
          getFavoritesKey
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
          toggleFav(matchId, button, shouldActivate, getFavoritesKey);
        });
      });
    }
  });

  const favorites = getFavorites(getFavoritesKey);
  document.querySelectorAll(".fav.addfav").forEach((btn) => {
    const matchId = btn.closest(".matches-tennis__item").dataset.match_id;
    if (favorites.includes(matchId)) btn.classList.add("active");

    const ligue = btn.closest(".matches-tennis__ligue");
    if (ligue) {
      // Проверяем, существует ли ligue
      const ligueButton = ligue.querySelector(".fav.addligue");
      if (ligueButton) {
        // Проверяем, существует ли ligueButton
        ligueButton.classList.toggle(
          "active",
          Array.from(ligue.querySelectorAll(".matches-tennis__item")).some(
            (item) => favorites.includes(item.dataset.match_id)
          )
        );
      }
    }
  });
}

export function toggleFav(
  matchId,
  button,
  activate,
  favoritesKey = "favorit_tn"
) {
  const favorites = getFavorites(favoritesKey);
  const index = favorites.indexOf(matchId);

  if (activate && index === -1) {
    favorites.push(matchId);
    button.classList.add("active");
  } else if (!activate && index !== -1) {
    favorites.splice(index, 1);
    button.classList.remove("active");
  }

  saveFavorites(favorites, favoritesKey);

  const leagueButton = button
    .closest(".matches-tennis__ligue")
    ?.querySelector(".fav.addligue");
  const matchItems = button
    .closest(".matches-tennis__ligue")
    ?.querySelectorAll(".matches-tennis__item");
  if (leagueButton && matchItems) {
    const anyFav = Array.from(matchItems).some((item) =>
      item.querySelector(".fav.addfav").classList.contains("active")
    );
    leagueButton.classList.toggle("active", anyFav);
  }
}

export function getFavorites(favoritesKey = "favorit_tn") {
  try {
    const favorites = JSON.parse(localStorage.getItem(favoritesKey));
    return Array.isArray(favorites) ? favorites : [];
  } catch {
    return [];
  }
}

export function saveFavorites(favorites, favoritesKey = "favorit_tn") {
  localStorage.setItem(favoritesKey, JSON.stringify(favorites));
}
