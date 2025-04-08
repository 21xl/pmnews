import Handlebars from "handlebars";
import templateSource from "../../templates/match-template.html";
import templateSourceRound from "../../templates/match-round-template.html";
import matcheserror from "../../templates/matches-error.html";
import pinned from "./pinned.store";
import incidents from "./incidents.store";
import competitionStore from "./competition.store";
import i18next from "./i18n";

jQuery(document).ready(function ($) {
  const competitionId = $(".statistics-competition").data("competition");

  const template = Handlebars.compile(templateSource);
  const templateRound = Handlebars.compile(templateSourceRound);
  const mEerortemplate = Handlebars.compile(matcheserror);
  const matchesMain = $(".matches__main");
  const today = new Date();

  function getPinnedItems() {
    return JSON.parse(localStorage.getItem("pinned")) || [];
  }

  function updatePinnedItems(id) {
    const pinnedItems = getPinnedItems();
    const index = pinnedItems.indexOf(id);

    if (index === -1) {
      pinnedItems.push(id);
    } else {
      pinnedItems.splice(index, 1);
    }

    localStorage.setItem("pinned", JSON.stringify(pinnedItems));

    if (id) {
      pinned.updatePinned(id);
    } else {
      if (index !== -1) {
        pinned.delPinned(id);
      }
    }
  }

  document.addEventListener("click", function (event) {
    const target = event.target.closest(".pin");
    if (target) {
      const competitionId = target.getAttribute("data-competition_id");
      if (!competitionId) return;
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

  function renderMatches(data) {
    if (data.matches?.length > 0 && data.matches[0]?.round?.round_num !== 0) {
      const curRound = data.competition?.cur_round ?? 0;

      const groupedMatches = data.matches.reduce((acc, match) => {
        if (parseInt(match.status_id) !== 8) return acc;

        let round =
          typeof match.round === "string"
            ? JSON.parse(match.round || "{}")
            : match.round || {};
        const roundNum = round.round_num ?? "unknown";

        if (!acc[roundNum]) {
          acc[roundNum] = {
            round: {
              round_num: roundNum,
              stage_id: round.stage_id ?? null,
            },
            matches: [],
          };
        }

        acc[roundNum].matches.push(match);

        return acc;
      }, {});

      data.rounds = Object.values(groupedMatches)
        .map((roundGroup) => {
          roundGroup.matches.sort((a, b) => b.match_time - a.match_time);
          return roundGroup;
        })
        .sort((a, b) => b.round.round_num - a.round.round_num);

      if (data.rounds.length > 0) {
        matchesMain.html(templateRound(data));
      } else {
        matchesMain.html(mEerortemplate);
      }
    } else {
      data.matches = data.matches
        .filter((match) => parseInt(match.status_id) == 8)
        .sort((a, b) => a.match_time - b.match_time);

      matchesMain.html(template(data));
    }

    toggleHide();
    initToggleFav();
  }

  function renderMatchesWithoutSorting() {
    const data = competitionStore.getData();

    renderMatches(data);
  }

  async function onDateChange(newDateValue) {
    await fetchAndRenderMatches(newDateValue);
    const tabElement = document.querySelector(`.tabs__item[data-status="all"]`);

    if (tabElement) {
      document.querySelectorAll(".tabs__item").forEach((item) => {
        item.classList.remove("active");
      });

      tabElement.classList.add("active");
    } else {
      console.error(`Элемент с data-status="all" не найден.`);
    }
  }

  const observer = new MutationObserver((mutationsList) => {
    for (const mutation of mutationsList) {
      if (
        mutation.type === "attributes" &&
        mutation.attributeName === "data-value"
      ) {
        const newDateValue = mutation.target.getAttribute("data-value");

        if (tab && tab == "all") {
          onDateChange(newDateValue);
        }
      }
    }
  });

  const datePickerDisplay = document.querySelector(".date-picker__display");
  if (datePickerDisplay) {
    observer.observe(datePickerDisplay, {
      attributes: true,
    });
  }

  async function fetchAndRenderMatches() {
    try {
      const response = await fetch(
        `/wp-json/sports/v1/matches_by_competition_id?competition_id=${competitionId}`
      );

      if (!response.ok) {
        throw new Error(`Ошибка запроса: ${response.statusText}`);
      }

      const data = await response.json();

      competitionStore.setData(data);
      renderMatchesWithoutSorting();
    } catch (error) {
      console.error("Ошибка при загрузке данных:", error.message);
    }
  }

  if (matchesMain && competitionId) {
    fetchAndRenderMatches();
  }

  function updateLiveMatchesTime() {
    $(".matches__item.live").each(function () {
      const matchId = $(this).data("match_id");
      const matchTimeElement = $(this).find(".matches__item-time");

      const match = competitionStore
        .getData()
        ?.matches.find((m) => m.id === matchId);

      if (match) {
        const currentTimestamp = Math.floor(Date.now() / 1000);
        const status = String(match.status_id);
        const kickoffTimestamp = match.kickoff_timestamp || match.match_time;

        if (status === "2" || status === "4" || status === "5") {
          let matchMinutes;
          if (status === "2") {
            matchMinutes =
              Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 1;
            if (matchMinutes > 45) {
              matchMinutes = `45+${matchMinutes - 45}`;
            }
          } else if (status === "4") {
            matchMinutes =
              Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 46;
            if (matchMinutes > 90) {
              matchMinutes = `90+${matchMinutes - 90}`;
            }
          } else if (status === "5") {
            matchMinutes =
              Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 45;
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

  const socket = new WebSocket("wss://sync-stage.sport-pulse.kz");

  socket.onopen = () => {};

  socket.onmessage = (event) => {
    const data = JSON.parse(event.data);

    data.forEach((matchUpdate) => {
      if (matchUpdate.score) {
        competitionStore.updateMatchData(matchUpdate.score);
      }
      if (matchUpdate.incidents) {
        updateIncident(matchUpdate);
      }
    });
  };

  competitionStore.onDataChange(({ updatedMatch }) => {
    if (updatedMatch) {
      updateMatchInDOM(updatedMatch);
    }
  });

  function updateMatchInDOM(updatedMatch) {
    const matchElement = document.querySelector(
      `.matches__item[data-match_id="${updatedMatch.id}"]`
    );

    if (!matchElement) {
      return;
    }

    const timeElement = matchElement.querySelector(".matches__item-time");
    if (timeElement) {
      timeElement.textContent = getMatchTimeOrBreak(
        updatedMatch.id,
        updatedMatch.status_id,
        updatedMatch.kickoff_timestamp
      );
    }

    const scoreElement = matchElement.querySelector(".matches__item-score");
    if (scoreElement) {
      const [homeScore, awayScore] = scoreElement.querySelectorAll("span");
      homeScore.textContent = updatedMatch.home_scores[0];
      awayScore.textContent = updatedMatch.away_scores[0];
    }

    const homeTeamWrapper = matchElement.querySelector(
      ".matches__item-team:first-child"
    );
    const awayTeamWrapper = matchElement.querySelector(
      ".matches__item-team:last-child"
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

  function updateWinnerClass(teamElement, teamScore, opponentScore) {
    teamElement.classList.remove("winner");
    if (teamScore > opponentScore) {
      teamElement.classList.add("winner");
    }
  }

  function getMatchTimeOrBreak(id, status, kickoffTimestamp) {
    const currentTimestamp = Math.floor(Date.now() / 1000);
    let matchMinutes;
    const matchElement = document.querySelector(`[data-match_id="${id}"]`);

    switch (String(status)) {
      case "1":
        const startTime = new Date(kickoffTimestamp * 1000);
        return startTime.toLocaleTimeString([], {
          hour: "2-digit",
          minute: "2-digit",
        });

      case "2":
        matchMinutes =
          Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 1;
        matchElement.classList.add("live");
        return `${matchMinutes}`;

      case "3":
        if (matchElement.classList.contains("live")) {
          matchElement.classList.remove("live");
        }
        matchElement.classList.add("pause");
        return i18next.t("half_time");

      case "4":
        matchElement.classList.remove("pause");
        matchMinutes =
          Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 46;
        if (!matchElement.classList.contains("live")) {
          matchElement.classList.add("live");
        }
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
              ".matches__ligue-content"
            );
            matchElement.remove();

            if (parentElement && parentElement.children.length === 0) {
              const leagueElement = parentElement.closest(".matches__ligue");
              if (leagueElement) {
                leagueElement.remove();
              }
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
        return i18next.t("to_be_determined");

      default:
        return i18next.t("hidden");
    }
  }

  function updateIncident(incidentData) {
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

      default:
        break;
    }
  }

  function handleGoal(id) {
    const goalElement = document.querySelector(`[data-match_id="${id}"]`);

    if (goalElement) {
      goalElement.classList.add("goal");
      goalElement.classList.remove("penalty");

      setTimeout(() => {
        goalElement.classList.remove("goal");
      }, 25000);
    } else {
    }
  }

  function handlePenalty(id) {
    const goalElement = document.querySelector(`[data-match_id="${id}"]`);

    if (goalElement) {
      goalElement.classList.add("penalty");
    } else {
    }
  }
  function handlePenaltyEnd(id) {
    const goalElement = document.querySelector(`[data-match_id="${id}"]`);

    if (goalElement) {
      goalElement.classList.remove("penalty");
    } else {
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
      case "2":
        return "live";
      case "3":
        return "pause";
      case "4":
        return "live";
      case "5":
        return "overtime";
      case "6":
        return "overtime-deprecated";
      case "7":
        return "live";
      case "8":
        return "ended";
      case "9":
        return "delay";
      case "10":
        return "interrupt";
      case "11":
        return "cut-in-half";
      case "12":
        return "canceled";
      case "13":
        return "determinated";
      default:
        return "";
    }
  });

  Handlebars.registerHelper("winnerClass", function (homeScores, awayScores) {
    return homeScores[0] > awayScores[0] ? "winner" : "";
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
      ?.matches.find((m) => m.id === matchId);

    if (!match || String(match.status_id) !== "8") {
      return "";
    }

    const kickoffTimestamp =
      match.kickoff_timestamp && match.kickoff_timestamp !== "0"
        ? match.kickoff_timestamp
        : match.match_time;

    const kickoffDate = new Date(kickoffTimestamp * 1000);

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

    return `${kickoffDate.getDate().toString().padStart(2, "0")}.${(
      kickoffDate.getMonth() + 1
    )
      .toString()
      .padStart(2, "0")} ${kickoffDate.toLocaleTimeString([], {
      hour: "2-digit",
      minute: "2-digit",
    })}`;
  });

  Handlebars.registerHelper("matchTimeOrBreak", function (matchId) {
    const match = competitionStore
      .getData()
      ?.matches.find((m) => m.id === matchId);

    if (!match) {
      return "Матч не найден";
    }

    const currentTimestamp = Math.floor(Date.now() / 1000);
    const status = match.status_id;
    const kickoffTimestamp =
      match.kickoff_timestamp && match.kickoff_timestamp !== "0"
        ? match.kickoff_timestamp
        : match.match_time;
    let matchMinutes;

    switch (String(status)) {
      case "1":
        const startTime = new Date(kickoffTimestamp * 1000);

        const isToday =
          startTime.getDate() === today.getDate() &&
          startTime.getMonth() === today.getMonth() &&
          startTime.getFullYear() === today.getFullYear();

        if (isToday) {
          return startTime.toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
          });
        } else {
          return `${startTime.getDate().toString().padStart(2, "0")}.${(
            startTime.getMonth() + 1
          )
            .toString()
            .padStart(2, "0")} ${startTime.toLocaleTimeString([], {
            hour: "2-digit",
            minute: "2-digit",
          })}`;
        }

      case "2":
        matchMinutes =
          Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 1;
        if (matchMinutes > 45) {
          matchMinutes = `45+${matchMinutes - 45}`;
        }
        return `${matchMinutes}`;

      case "3":
        return i18next.t("half_time");

      case "4":
        matchMinutes =
          Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 46;
        if (matchMinutes > 90) {
          matchMinutes = `90+${matchMinutes - 90}`;
        }
        return `${matchMinutes}`;

      case "5":
        matchMinutes =
          Math.floor((currentTimestamp - kickoffTimestamp) / 60) + 45;
        return `90+${matchMinutes}`;

      case "7":
        return i18next.t("penalty_shootout");

      case "8":
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
        return i18next.t("to_be_determined");

      default:
        return i18next.t("hidden");
    }
  });

  function toggleHide() {
    document.querySelectorAll(".matches__ligue").forEach((parentBlock) => {
      const toggleButton = parentBlock.querySelector(".matches__ligue-hide");

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
  const pinned = JSON.parse(localStorage.getItem("pinned")) || [];
  return pinned.includes(competitionId) ? "active" : "";
});

function initToggleFav() {
  const matchesLigueBlocks = document.querySelectorAll(".matches__ligue");

  matchesLigueBlocks.forEach((block) => {
    const addFavButtons = block.querySelectorAll(".fav.addfav");
    const addLigueButton = block.querySelector(".fav.addligue");

    addFavButtons.forEach((button) => {
      button.addEventListener("click", (event) => {
        event.stopPropagation();
        event.preventDefault();
        toggleFav(
          button.closest(".matches__item").dataset.match_id,
          button,
          !button.classList.contains("active")
        );
      });
    });

    if (addLigueButton) {
      addLigueButton.addEventListener("click", () => {
        const shouldActivate = !addLigueButton.classList.contains("active");
        addLigueButton.classList.toggle("active", shouldActivate);
        const matchItems = block.querySelectorAll(".matches__item");

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
    const matchId = btn.closest(".matches__item").dataset.match_id;
    if (favorites.includes(matchId)) {
      btn.classList.add("active");
    }
    // Set .fav.addligue active if any matches are favorited
    btn
      .closest(".matches__ligue")
      .querySelector(".fav.addligue")
      .classList.toggle(
        "active",
        Array.from(
          btn.closest(".matches__ligue").querySelectorAll(".matches__item")
        ).some((item) => favorites.includes(item.dataset.match_id))
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
  const leagueButton = button
    .closest(".matches__ligue")
    .querySelector(".fav.addligue");
  const matchItems = button
    .closest(".matches__ligue")
    .querySelectorAll(".matches__item");

  // Set .fav.addligue active if any match is favorited
  const anyFav = Array.from(matchItems).some((item) =>
    item.querySelector(".fav.addfav").classList.contains("active")
  );
  leagueButton.classList.toggle("active", anyFav);
}

function getFavorites() {
  try {
    const favorites = JSON.parse(localStorage.getItem("favorit_fb"));
    return Array.isArray(favorites) ? favorites : [];
  } catch {
    return [];
  }
}

function saveFavorites(favorites) {
  localStorage.setItem("favorit_fb", JSON.stringify(favorites));
}
