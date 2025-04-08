import Handlebars from "handlebars";
import incidentTemp from "../../templates/single-review.html";
import squadTemp from "../../templates/single-squad.html";
import statTemp from "../../templates/single-statistics.html";
import standTemp from "../../templates/single-standings.html";
import h2hTemp from "../../templates/single-h2h.html";
import oddsTemp from "../../templates/single-odds.html";
import matcheserror from "../../templates/matches-error.html";

import pinned from "./pinned.store";
import incidents from "./incidents.store";
import playerStore from "./player.store";
import competitionStore from "./competition.store";
import i18next from "./i18n";

const incidentTemplate = Handlebars.compile(incidentTemp);
const squadTemplate = Handlebars.compile(squadTemp);
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
  const today = new Date();

  const matchId = $(".match").data("matchid");
  if (!matchId) {
    return;
  }

  const tabsItems = document.querySelectorAll(".tabs__item");
  const review = document.querySelectorAll(".match__review")[0];
  const composition = document.querySelectorAll(".match__composition")[0];
  const statistics = document.querySelectorAll(".match__statistics-list")[0];
  const standings = document.querySelectorAll(".table.standings")[0];
  const match__h2h = document.querySelectorAll(".match__h2h")[0];
  const matchOdds = document.querySelectorAll(".odds .table__wrapper")[0];
  const timeElement = document.querySelectorAll(".scoreboard__minute")[0];
  const scoreboard__goals = document.querySelectorAll(".scoreboard__goals")[0];

  // if (tabsItems.length > 0) {
  //   tabsItems.forEach(async (item) => {
  //     if (item.classList.contains("active")) {
  //       const activeTab = item.getAttribute("data-status");
  //
  //       match = await fetchData(matchId, "review");
  //       const {
  //         incidents: incidentsData,
  //         home_team,
  //         away_team,
  //         competition_id,
  //       } = match;
  //       away_t = away_team;
  //       home_t = home_team;
  //       compId = competition_id;

  //       switch (activeTab) {
  //         case "review":
  //           incidents.setData(incidentsData);
  //           const { squad, coaches } = await fetchData(matchId, "squad");
  //           if (coaches) coachesData = coaches;
  //           playerStore.setData(squad);
  //           if (!tabs.includes(activeTab)) tabs.push(activeTab);
  //           const newIncident = { type: 33 };
  //           const fitstHalves = { type: 999 };
  //           if (!incidentsData) return;
  //           incidentsData.unshift(fitstHalves);
  //           const inc = processIncidents(incidentsData);
  //           html = inc
  //             .map((incident) => {
  //               if (
  //                 !p_series &&
  //                 (incident.type === 29 || incident.type === 30)
  //               ) {
  //                 p_series = true;
  //                 return (
  //                   incidentTemplate({ incident: newIncident }) +
  //                   incidentTemplate({ incident })
  //                 );
  //               }
  //               return incidentTemplate({ incident });
  //             })
  //             .join("");
  //           review.innerHTML = html;
  //           break;
  //         case "odds":
  //           const { odds } = await fetchData(matchId, "odds");
  //           if (!odds || odds.length == 0) return;
  //           const row = odds.find(
  //             (odd) =>
  //               odd.company_id == "22" ||
  //               odd.company_id == "2" ||
  //               odd.company_id == "9"
  //           );

  //           if (!row) return;
  //           const rows = [row];
  //           matchOdds.innerHTML = oddsTemplate({ rows });
  //           if (!tabs.includes(activeTab)) tabs.push(activeTab);
  //         case "h2h":
  //           const { matches } = await fetchData(matchId, "h2h");
  //           if (!matches) return;
  //           const h2hHome = matches
  //             .filter(
  //               (match) =>
  //                 match.home_team_id === home_t.id ||
  //                 match.away_team_id === home_t.id
  //             )
  //             .map((match) => ({
  //               ...match,
  //               result: determineResult(match, home_t.id),
  //             }))
  //             .sort((a, b) => b.match_time - a.match_time)
  //             .slice(0, 5);

  //           const home_m = matches
  //             .filter((match) => match.home_team_id === home_t.id)
  //             .map((match) => ({
  //               ...match,
  //               result: determineResult(match, home_t.id),
  //             }))
  //             .sort((a, b) => b.match_time - a.match_time)
  //             .slice(0, 5);

  //           const h2hAway = matches
  //             .filter(
  //               (match) =>
  //                 match.home_team_id === away_t.id ||
  //                 match.away_team_id === away_t.id
  //             )
  //             .map((match) => ({
  //               ...match,
  //               result: determineResult(match, away_t.id),
  //             }))
  //             .sort((a, b) => b.match_time - a.match_time)
  //             .slice(0, 5);
  //           const away_m = matches
  //             .filter((match) => match.away_team_id === away_t.id)
  //             .map((match) => ({
  //               ...match,
  //               result: determineResult(match, away_t.id),
  //             }))
  //             .sort((a, b) => b.match_time - a.match_time)
  //             .slice(0, 5);
  //           const h2h = matches
  //             .filter(
  //               (match) =>
  //                 (match.home_team_id === home_t.id &&
  //                   match.away_team_id === away_t.id) ||
  //                 (match.home_team_id === away_t.id &&
  //                   match.away_team_id === home_t.id)
  //             )
  //             .map((match) => ({
  //               ...match,
  //               result: determineResult(match, home_t.id),
  //             }))
  //             .sort((a, b) => b.match_time - a.match_time)
  //             .slice(0, 5);

  //           match__h2h.innerHTML = h2hTemplate({
  //             h2hHome,
  //             h2hAway,
  //             h2h: h2h.length > 0 ? h2h : null,
  //             away_t,
  //             home_t,
  //             home_m,
  //             away_m,
  //           });
  //           break;

  //         default:
  //           break;
  //       }
  //     }
  //   });

  //   const observer = new MutationObserver((mutationsList) => {
  //     mutationsList.forEach((mutation) => {
  //       if (
  //         mutation.type === "attributes" &&
  //         mutation.attributeName === "class"
  //       ) {
  //         const target = mutation.target;
  //         if (target.classList.contains("active")) {
  //           const status = target.getAttribute("data-status");

  //           if (!tabs.includes(status)) {
  //             (async () => {
  //               try {
  //                 tabs.push(status);
  //                 switch (status) {
  //                   case "squad":
  //                     const squad = playerStore.getData();
  //                     if (!squad) return;
  //                     const homeTeam =
  //                       squad?.filter(
  //                         (player) => player.pos == 1 && player.first
  //                       ) || [];
  //                     const awayTeam =
  //                       squad?.filter(
  //                         (player) => player.pos == 2 && player.first
  //                       ) || [];
  //                     const althome =
  //                       squad?.filter(
  //                         (player) => player.pos == 1 && !player.first
  //                       ) || [];
  //                     const altaway =
  //                       squad?.filter(
  //                         (player) => player.pos == 2 && !player.first
  //                       ) || [];

  //                     const homeSub = incidents
  //                       .getData()
  //                       .reduce((acc, incident) => {
  //                         if (incident.position === 1 && incident.type === 9) {
  //                           const data = {
  //                             in:
  //                               squad.find(
  //                                 (player) =>
  //                                   player.id === incident.in_player_id
  //                               ) || null,
  //                             out:
  //                               squad.find(
  //                                 (player) =>
  //                                   player.id === incident.out_player_id
  //                               ) || null,
  //                             time: incident.time,
  //                           };
  //                           acc.push(data);
  //                         }
  //                         return acc;
  //                       }, []);
  //                     const awaySub = incidents
  //                       .getData()
  //                       .reduce((acc, incident) => {
  //                         if (incident.position === 2 && incident.type === 9) {
  //                           const data = {
  //                             in:
  //                               squad.find(
  //                                 (player) =>
  //                                   player.id === incident.in_player_id
  //                               ) || null,
  //                             out:
  //                               squad.find(
  //                                 (player) =>
  //                                   player.id === incident.out_player_id
  //                               ) || null,
  //                             time: incident.time,
  //                           };
  //                           acc.push(data);
  //                         }
  //                         return acc;
  //                       }, []);

  //                     if (squadTemp)
  //                       composition.innerHTML = squadTemplate({
  //                         homeTeam,
  //                         awayTeam,
  //                         althome,
  //                         altaway,
  //                         away_t,
  //                         home_t,
  //                         homeSub,
  //                         awaySub,
  //                         coachesData,
  //                       });
  //                     return;
  //                   case "statistics":
  //                     const { home_team, away_team } = await fetchData(
  //                       matchId,
  //                       "statistics"
  //                     );
  //                     statistics.innerHTML = statTemplate({
  //                       home_team,
  //                       away_team,
  //                     });
  //                     initCheckbox();
  //                     return;
  //                   case "standings":
  //                     const stdata = await fetchStandings(compId);
  //                     if (!stdata && !standings) return;
  //                     standings.innerHTML = standTemplate({
  //                       standings: stdata.standings,
  //                       brackets: stdata.brackets,
  //                     });

  //                     return;
  //                   case "h2h":
  //                     const { matches } = await fetchData(matchId, "h2h");
  //                     if (!matches) return;
  //                     const h2hHome = matches
  //                       .filter(
  //                         (match) =>
  //                           match.home_team_id === home_t.id ||
  //                           match.away_team_id === home_t.id
  //                       )
  //                       .map((match) => ({
  //                         ...match,
  //                         result: determineResult(match, home_t.id),
  //                       }))
  //                       .sort((a, b) => b.match_time - a.match_time)
  //                       .slice(0, 5);

  //                     const home_m = matches
  //                       .filter((match) => match.home_team_id === home_t.id)
  //                       .map((match) => ({
  //                         ...match,
  //                         result: determineResult(match, home_t.id),
  //                       }))
  //                       .sort((a, b) => b.match_time - a.match_time)
  //                       .slice(0, 5);

  //                     const h2hAway = matches
  //                       .filter(
  //                         (match) =>
  //                           match.home_team_id === away_t.id ||
  //                           match.away_team_id === away_t.id
  //                       )
  //                       .map((match) => ({
  //                         ...match,
  //                         result: determineResult(match, away_t.id),
  //                       }))
  //                       .sort((a, b) => b.match_time - a.match_time)
  //                       .slice(0, 5);
  //                     const away_m = matches
  //                       .filter((match) => match.away_team_id === away_t.id)
  //                       .map((match) => ({
  //                         ...match,
  //                         result: determineResult(match, away_t.id),
  //                       }))
  //                       .sort((a, b) => b.match_time - a.match_time)
  //                       .slice(0, 5);
  //                     const h2h = matches
  //                       .filter(
  //                         (match) =>
  //                           (match.home_team_id === home_t.id &&
  //                             match.away_team_id === away_t.id) ||
  //                           (match.home_team_id === away_t.id &&
  //                             match.away_team_id === home_t.id)
  //                       )
  //                       .map((match) => ({
  //                         ...match,
  //                         result: determineResult(match, home_t.id),
  //                       }))
  //                       .sort((a, b) => b.match_time - a.match_time)
  //                       .slice(0, 5);

  //                     match__h2h.innerHTML = h2hTemplate({
  //                       h2hHome,
  //                       h2hAway,
  //                       h2h: h2h.length > 0 ? h2h : null,
  //                       away_t,
  //                       home_t,
  //                       home_m,
  //                       away_m,
  //                     });
  //                     return;
  //                   case "odds":
  //                     const { odds } = await fetchData(matchId, "odds");
  //                     if (!odds || odds.length == 0) return;
  //                     const row = odds.find(
  //                       (odd) =>
  //                         odd.company_id == "22" ||
  //                         odd.company_id == "2" ||
  //                         odd.company_id == "9"
  //                     );

  //                     if (!row) return;
  //                     const rows = [row];
  //                     matchOdds.innerHTML = oddsTemplate({ rows });

  //                     if (!tabs.includes(activeTab)) tabs.push(activeTab);
  //                     return;
  //                   default:
  //                     return;
  //                 }
  //               } catch (error) {
  //                 console.error("Ошибка при получении данных:", error);
  //               }
  //             })();
  //           } else {
  //           }
  //         }
  //       }
  //     });
  //   });

  //   const config = { attributes: true, attributeFilter: ["class"] };

  //   tabsItems.forEach((item) => {
  //     observer.observe(item, config);
  //   });
  // } else {
  // }

  function getPinnedItems() {
    return JSON.parse(localStorage.getItem("pinned")) || [];
  }

  if (tabsItems.length > 0) {
    tabsItems.forEach(async (item) => {
      if (item.classList.contains("active")) {
        const activeTab = item.getAttribute("data-status");

        match = await fetchData(matchId, "review");

        const {
          incidents: incidentsData,
          home_team,
          away_team,
          competition_id,
        } = match;
        away_t = away_team;
        home_t = home_team;
        compId = competition_id;

        tabHandlers = {
          review: async () => handleReviewTab(incidentsData),
          odds: async () => handleOddsTab(),
          h2h: async () => handleH2HTab(),
          statistics: async () => handleStatisticsTab(),
          standings: async () => handleStandingsTab(),
          squad: async () => handleSquadTab(),
        };

        if (tabHandlers[activeTab]) {
          await tabHandlers[activeTab]();
        }
      }
    });

    const handleReviewTab = async (incidentsData) => {
      incidents.setData(incidentsData);
      const { squad, coaches } = await fetchData(matchId, "squad");
      if (coaches) coachesData = coaches;
      playerStore.setData(squad);
      if (!tabs.includes("review")) tabs.push("review");

      const newIncident = { type: 33 };
      const fitstHalves = { type: 999 };
      if (!incidentsData) return;
      incidentsData.unshift(fitstHalves);
      const inc = processIncidents(incidentsData);
      const html = inc
        .map((incident) => {
          if (!p_series && (incident.type === 29 || incident.type === 30)) {
            p_series = true;
            return (
              incidentTemplate({ incident: newIncident }) +
              incidentTemplate({ incident })
            );
          }
          return incidentTemplate({ incident });
        })
        .join("");
      review.innerHTML = html;
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

    const handleH2HTab = async () => {
      const h2hresp = await fetchData(matchId, "h2h");
      if (h2hresp.status && h2hresp.status !== 200)
        return (match__h2h.innerHTML = mEerortemplate());
      const { matches } = h2hresp;
      if (!matches) return (match__h2h.innerHTML = mEerortemplate());

      const filterAndPrepareMatches = (team_id) =>
        matches
          .filter(
            (match) =>
              match.home_team_id === team_id || match.away_team_id === team_id
          )
          .map((match) => ({
            ...match,
            result: determineResult(match, team_id),
          }))
          .sort((a, b) => b.match_time - a.match_time)
          .slice(0, 5);

      const h2hHome = filterAndPrepareMatches(home_t.id);
      const home_m = filterAndPrepareMatches(home_t.id);
      const h2hAway = filterAndPrepareMatches(away_t.id);
      const away_m = filterAndPrepareMatches(away_t.id);

      const h2h = matches
        .filter(
          (match) =>
            (match.home_team_id === home_t.id &&
              match.away_team_id === away_t.id) ||
            (match.home_team_id === away_t.id &&
              match.away_team_id === home_t.id)
        )
        .map((match) => ({
          ...match,
          result: determineResult(match, home_t.id),
        }))
        .sort((a, b) => b.match_time - a.match_time)
        .slice(0, 5);

      match__h2h.innerHTML = h2hTemplate({
        h2hHome,
        h2hAway,
        h2h: h2h.length > 0 ? h2h : null,
        away_t,
        home_t,
        home_m,
        away_m,
      });
    };

    const handleStatisticsTab = async () => {
      const { statistics: stat } = await fetchData(matchId, "statistics");
      if (!stat) return (statistics.innerHTML = mEerortemplate());
      statistics.innerHTML = statTemplate({ stat });
      initCheckbox();
    };

    const handleStandingsTab = async () => {
      const stdata = await fetchStandings(compId);
      if (!stdata || !standings)
        return (standings.innerHTML = mEerortemplate());
      if (
        !stdata.standings &&
        (!stdata.brackets || stdata.brackets.length === 0)
      )
        return (standings.innerHTML = mEerortemplate());

      standings.innerHTML = standTemplate({
        standings: stdata.standings,
        brackets: stdata.brackets,
      });
    };

    const handleSquadTab = async () => {
      const squad = playerStore.getData();

      if (!squad) return;

      const filterPlayers = (pos, isFirst) =>
        squad.filter(
          (player) => player.pos === pos && player.first === isFirst
        );

      const homeTeam = filterPlayers(1, 1);
      const awayTeam = filterPlayers(2, 1);
      const althome = filterPlayers(1, 0);
      const altaway = filterPlayers(2, 0);

      const filterSubstitutes = (position) =>
        incidents.getData().reduce((acc, incident) => {
          if (incident.position === position && incident.type === 9) {
            const data = {
              in:
                squad.find((player) => player.id === incident.in_player_id) ||
                null,
              out:
                squad.find((player) => player.id === incident.out_player_id) ||
                null,
              time: incident.time,
            };
            acc.push(data);
          }
          return acc;
        }, []);

      const homeSub = filterSubstitutes(1);
      const awaySub = filterSubstitutes(2);

      if (squadTemp)
        composition.innerHTML = squadTemplate({
          homeTeam,
          awayTeam,
          althome,
          altaway,
          away_t,
          home_t,
          homeSub,
          awaySub,
          coachesData,
        });
    };

    const observer = new MutationObserver((mutationsList) => {
      mutationsList.forEach((mutation) => {
        if (
          mutation.type === "attributes" &&
          mutation.attributeName === "class"
        ) {
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

  function updateLiveMatchesTime() {
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

  const socket = new WebSocket("wss://sync-stage.sport-pulse.kz");

  socket.onopen = () => {};

  setInterval(() => {
    if (socket.readyState === WebSocket.OPEN) {
      socket.send(".");
    }
  }, 30000);

  socket.onmessage = (event) => {
    const data = JSON.parse(event.data);

    data.forEach((matchUpdate) => {
      if (matchUpdate.id == matchId && matchUpdate.score) {
        const score = matchUpdate.score;

        if (timeElement) {
          const spanElement = timeElement.querySelector("span");
          spanElement.textContent = getMatchTimeOrBreak(
            matchUpdate.id,
            score[1],
            score[4]
          );
        }
        if (scoreboard__goals) {
          const spanElement = scoreboard__goals.querySelector("span");
          const count = `${score[2][0]}:${score[3][0]}`;
          spanElement.textContent = count;
        }
      }
      if (matchUpdate.id == matchId && matchUpdate.incidents) {
        const incident = incidents.addIncidents(matchUpdate);
        // updateIncident(matchUpdate);

        newIncident(incident, review);
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
      const spanElement = timeElement.querySelector("span");
      spanElement.textContent = getMatchTimeOrBreak(
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
    const matchElement = document.querySelector(`[data-matchid="${id}"]`);

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
        matchElement.classList.add("ended");
        if (matchElement.classList.contains("live")) {
          matchElement.classList.remove("live");
        }
        stopSync();
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

async function fetchData(id, tab = "review") {
  try {
    const response = await fetch(
      `/wp-json/sports/v1/matche_data?match_id=${id}&tab=${tab}`
    );

    if (!response.ok) {
      throw new Error(`Ошибка запроса: ${response.statusText}`);
    }

    const data = await response.json();
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
      const firstTab = statistics.querySelector(
        `.custom-checkbox__tab[data-checkbox-tab="${firstTabName}"]`
      );
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

            const activeTab = statistics.querySelector(
              `.custom-checkbox__tab[data-checkbox-tab="${tabName}"]`
            );
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
    const response = await fetch(
      `/wp-json/sports/v1/competition_standings_data?competition_id=${id}`
    );

    if (!response.ok) {
      throw new Error(`Ошибка запроса: ${response.statusText}`);
    }

    let data = await response.json();

    let target = null;
    let tgindex = -1;

    let rounds = [];

    if (data.brackets.length != 0) {
      rounds = data.brackets;

      for (let i = rounds.length - 1; i >= 0; i--) {
        if (rounds[i]?.matchups && Object.keys(rounds[i].matchups).length > 0) {
          target = rounds[i];
          tgindex = i;
          break;
        }
      }

      target.newMatchups = rounds[tgindex].matchups;
      rounds[tgindex].newMatchups = rounds[tgindex].matchups;

      while (tgindex > 0) {
        if (target) {
          target.newMatchups.forEach((matchup, index) => {
            if (matchup?.children_ids && Array.isArray(matchup?.children_ids)) {
              if (matchup?.children_ids.length == 1) {
                matchup?.children_ids.forEach((childId) => {
                  const prevRound = rounds[tgindex - 1];
                  const childMatchup = prevRound?.matchups?.find(
                    (m) => m.id === childId
                  );

                  if (childMatchup) {
                    const missingTeamId = !prevRound.matchups.some(
                      (m) =>
                        m.home_team_id === matchup?.home_team_id ||
                        m.away_team_id === matchup?.home_team_id
                    )
                      ? matchup?.home_team_id
                      : !prevRound.matchups?.some(
                          (m) =>
                            m.home_team_id === matchup?.away_team_id ||
                            m.away_team_id === matchup?.away_team_id
                        )
                      ? matchup?.away_team_id
                      : null;

                    if (missingTeamId) {
                      const emptyMatchup = {
                        id: `empty-${childId}`,
                        home_team_id: missingTeamId,
                        away_team_id: null,
                        match_ids: [],
                        parent_ids: [childId],
                        children_ids: [],
                        home_team:
                          missingTeamId === matchup?.home_team_id
                            ? matchup?.home_team
                            : matchup?.away_team,
                        away_team: null,
                      };

                      if (!prevRound.newMatchups) {
                        prevRound.newMatchups = [];
                      }

                      prevRound.newMatchups.push(emptyMatchup);
                    }

                    if (!prevRound.newMatchups) {
                      prevRound.newMatchups = [];
                    }
                    prevRound.newMatchups.push(childMatchup);
                  } else {
                    console.log(
                      `Матчап с ID ${childId} не найден в предыдущем раунде.`
                    );
                  }
                });
              } else if (matchup.children_ids.length == 0) {
                const prevRound = rounds[tgindex - 1];

                const emptyMatchupWithTeam = {
                  id: `empty`,
                  home_team_id: matchup.home_team_id,
                  away_team_id: null,
                  match_ids: [],
                  parent_ids: [],
                  children_ids: [],
                  home_team: matchup.home_team,
                  away_team: null,
                };

                if (!prevRound.newMatchups) {
                  prevRound.newMatchups = [];
                }

                prevRound.newMatchups.push(emptyMatchupWithTeam);

                const emptyMatchupWithoutTeam = {
                  id: `empty`,
                  home_team_id: null,
                  away_team_id: null,
                  match_ids: [],
                  parent_ids: [],
                  children_ids: [],
                  home_team: null,
                  away_team: null,
                };

                prevRound.newMatchups.push(emptyMatchupWithoutTeam);
              } else {
                matchup.children_ids.forEach((childId) => {
                  const prevRound = rounds[tgindex - 1];
                  const childMatchup = prevRound?.matchups?.find(
                    (m) => m.id === childId
                  );

                  if (childMatchup) {
                    if (!prevRound.newMatchups) {
                      prevRound.newMatchups = [];
                    }
                    prevRound.newMatchups.push(childMatchup);
                  } else {
                    console.log(
                      `Матчап с ID ${childId} не найден в предыдущем раунде.`
                    );
                  }
                });
              }
            } else {
            }
          });

          target = rounds[tgindex - 1];
          tgindex -= 1;

          data.brackets = rounds;
        } else {
          break;
        }
      }

      for (let i = tgindex + 1; i < rounds.length; i++) {
        const currentRound = rounds[i];
        const nextRound = rounds[i + 1];

        if (nextRound && nextRound.matchups.length === 0) {
          const requiredMatchups = Math.ceil(
            currentRound.newMatchups.length / 2
          );
          nextRound.newMatchups = [];

          for (let j = 0; j < requiredMatchups; j++) {
            nextRound.newMatchups.push({
              id: `empty-next-${i}-${j}`,
              home_team_id: null,
              away_team_id: null,
              match_ids: [],
              parent_ids: [],
              children_ids: [],
              home_team: null,
              away_team: null,
            });
          }
        }
      }
    }

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
  const homeScore = match.home_scores ? match.home_scores[0] : 0;
  const awayScore = match.away_scores ? match.away_scores[0] : 0;

  if (match.home_team_id === teamId) {
    if (homeScore > awayScore) return "win";
    if (homeScore < awayScore) return "loss";
    return "draw";
  } else if (match.away_team_id === teamId) {
    if (awayScore > homeScore) return "win";
    if (awayScore < homeScore) return "loss";
    return "draw";
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
  return homeScores[0] > awayScores[0] ? "winner" : "";
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

function processIncidents(incidents) {
  let foundType11 = false;

  const processedIncidents = incidents.map((incident) => {
    if (incident.type === 11) {
      foundType11 = true;
    }

    if (!foundType11 && incident.time > 45) {
      incident.time = `45+${incident.time - 45}`;
    }

    if (foundType11 && incident.time > 90) {
      incident.time = `90+${incident.time - 90}`;
    }

    return incident;
  });

  return processedIncidents;
}

Handlebars.registerHelper("checkState", function (state_id) {
  return state_id === "0" || state_id === "1" ? false : true;
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
  let html = incidentTemplate({ incident: data.incident });
  if (!p_series && (data.incident.type === 29 || data.incident.type === 30)) {
    p_series = true;
    html =
      incidentTemplate({ incident: { type: 33 } }) +
      incidentTemplate({ incident: data.incident });
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
