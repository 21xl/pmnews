class Store {
  constructor() {
    this.data = [];
    this.eventTarget = new EventTarget();
  }

  setData(newData) {
    const pinnedItems = newData.filter((item) => item.pinned);
    const unpinnedItems = newData.filter((item) => !item.pinned);

    const sortItems = (a, b) => {
      if (a.country?.name && b.country?.name) {
        return a.country.name.localeCompare(b.country.name);
      }
      if (a.category?.name && b.category?.name) {
        return a.category.name.localeCompare(b.category.name);
      }
      return 0;
    };

    pinnedItems.sort(sortItems);
    unpinnedItems.sort(sortItems);

    this.data = [...pinnedItems, ...unpinnedItems];
  }

  updatePinned(id, pinned) {
    this.data = this.data.map((item) => {
      if (item.tournamentId === id) {
        return { ...item, pinned };
      }
      return item;
    });

    this.setData(this.data);
  }

  updateMatchData(matchUpdate) {
    const [matchId, newStatusId, serving_side, scores] = matchUpdate;

    let updatedMatch = null;

    this.data = this.data.map((competition) => {
      const updatedMatches = competition.matches.map((match) => {
        if (match.id === matchId) {
          // Создаем обновленный объект матча
          updatedMatch = {
            ...match,
            status_id: newStatusId,
            serving_side: serving_side,
            scores: scores,
          };

          // Проверяем наличие scores.ft и его значения
          if (scores && scores.ft && Array.isArray(scores.ft)) {
            const [homeFt, awayFt] = scores.ft;

            // Проверяем, изменился ли счет ft по сравнению с предыдущим значением
            const previousFt =
              match.scores && match.scores.ft ? match.scores.ft : [0, 0];
            const ftChanged =
              previousFt[0] !== homeFt || previousFt[1] !== awayFt;

            if (ftChanged && homeFt === 2 && awayFt === 0) {
              // Если счет ft изменился и стал [2, 0], определяем победителя
              updatedMatch.set_win = 1; // Home выиграл сет
            } else if (ftChanged && homeFt === 0 && awayFt === 2) {
              // Если счет ft изменился и стал [0, 2]
              updatedMatch.set_win = 2; // Away выиграл сет
            } else if (!ftChanged && "set_win" in updatedMatch) {
              // Если счет ft не изменился и set_win есть, удаляем его
              delete updatedMatch.set_win;
            }
          } else if ("set_win" in updatedMatch) {
            // Если scores.ft отсутствует и set_win есть, удаляем его
            delete updatedMatch.set_win;
          }

          return updatedMatch;
        }
        return match;
      });

      return {
        ...competition,
        matches: updatedMatches,
      };
    });

    if (!updatedMatch) {
      return;
    }

    const event = new CustomEvent("dataChanged", {
      detail: {
        updatedMatch,
      },
    });
    this.eventTarget.dispatchEvent(event);
  }

  updateMatchGame(matchUpdate) {
    const { id, timeline } = matchUpdate;

    let updatedMatch = null;

    this.data = this.data.map((competition) => {
      const updatedMatches = competition.matches.map((match) => {
        if (match.id === id) {
          // Находим последний сет и последний раунд
          const lastSet = timeline[timeline.length - 1];
          const lastRound = lastSet?.rounds[lastSet.rounds.length - 1];

          // Копируем текущий матч
          updatedMatch = { ...match };

          if (lastRound && lastRound.score) {
            const { home, away } = lastRound.score;

            // Определяем победителя раунда по счету
            if (home > away) {
              updatedMatch.round_win = 1; // Home выиграл
            } else if (away > home) {
              updatedMatch.round_win = 2; // Away выиграл
            } else {
              // Если счет равный (например, 40-40), удаляем round_win
              delete updatedMatch.round_win;
            }
          } else {
            // Если score отсутствует в последнем раунде, удаляем round_win
            if ("round_win" in updatedMatch) {
              delete updatedMatch.round_win;
            }
          }

          return updatedMatch;
        }
        return match;
      });

      return {
        ...competition,
        matches: updatedMatches,
      };
    });

    if (!updatedMatch) {
      console.log(`Матч с id ${id} не найден`);
      return;
    }

    // Отправляем событие с обновленным матчем
    const event = new CustomEvent("gameChanged", {
      detail: {
        updatedMatch,
      },
    });
    this.eventTarget.dispatchEvent(event);
  }

  getMatchById(matchId) {
    for (const competition of this.data) {
      const match = competition.matches.find((match) => match.id === matchId);
      if (match) {
        return match;
      }
    }

    return null;
  }

  getData() {
    return this.data;
  }

  filterDataByStatus(status) {
    const filterMatches = (data, statusFilter) =>
      data
        .map((competition) => ({
          ...competition,
          matches: competition.matches
            .filter(statusFilter)
            .sort((a, b) => a.match_time - b.match_time), // Сортировка по match_time
        }))
        .filter((competition) => competition.matches.length > 0)
        .sort((a, b) => (b.pinned ? 1 : 0) - (a.pinned ? 1 : 0));

    let filteredData;

    if (status === "live") {
      filteredData = filterMatches(this.data, (match) =>
        ["3", "51", "52", "53", "54", "55"].includes(String(match.status_id))
      );
    } else if (status === "ended") {
      filteredData = filterMatches(
        this.data,
        (match) => String(match.status_id) === "100"
      );
    } else if (status === "scheduled") {
      filteredData = filterMatches(
        this.data,
        (match) => String(match.status_id) === "1"
      );
    } else {
      filteredData = filterMatches(
        this.data,
        (match) =>
          String(match.status_id) !== "9" && String(match.status_id) !== "13"
      );
    }

    return filteredData && filteredData.length > 0 ? filteredData : [];
  }

  onDataChange(callback) {
    this.eventTarget.addEventListener("dataChanged", (event) => {
      callback(event.detail);
    });
  }

  gameChanged(callback) {
    this.eventTarget.addEventListener("gameChanged", (event) => {
      callback(event.detail);
    });
  }
}

const competitionStore = new Store();
export default competitionStore;
