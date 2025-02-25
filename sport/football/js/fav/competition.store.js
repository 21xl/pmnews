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
      if (item.competition.id === id) {
        return { ...item, pinned };
      }
      return item;
    });

    this.setData(this.data);
  }

  updateMatchData(matchUpdate) {
    const [
      matchId,
      newStatusId,
      newHomeScores,
      newAwayScores,
      kickoff_timestamp,
    ] = matchUpdate;

    let updatedMatch = null;

    this.data = this.data.map((competition) => {
      const updatedMatches = competition.matches.map((match) => {
        if (match.id === matchId) {
          updatedMatch = {
            ...match,
            status_id: newStatusId,
            home_scores: newHomeScores,
            away_scores: newAwayScores,
            kickoff_timestamp: kickoff_timestamp,
          };
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
          matches: competition.matches.filter(statusFilter),
        }))
        .filter((competition) => competition.matches.length > 0)
        .sort((a, b) => (b.pinned ? 1 : 0) - (a.pinned ? 1 : 0));

    let filteredData;

    if (status === "live") {
      filteredData = filterMatches(this.data, (match) =>
        ["2", "3", "4", "5", "7"].includes(String(match.status_id))
      );
    } else if (status === "ended") {
      filteredData = filterMatches(
        this.data,
        (match) => String(match.status_id) === "8"
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

  deleteMatchById(matchId) {
    let deletedMatch = null;

    this.data = this.data
      .map((competition) => {
        const filteredMatches = competition.matches.filter(
          (match) => match.id !== matchId
        );

        // Если матч был удалён, сохраним его для события
        if (filteredMatches.length < competition.matches.length) {
          deletedMatch = competition.matches.find(
            (match) => match.id === matchId
          );
        }

        return {
          ...competition,
          matches: filteredMatches,
        };
      })
      .filter((competition) => competition.matches.length > 0); // Удаляем соревнования без матчей

    if (deletedMatch) {
      const event = new CustomEvent("dataChanged", {
        detail: {
          deletedMatch,
        },
      });
      this.eventTarget.dispatchEvent(event);
    }
  }
}

const competitionStore = new Store();
export default competitionStore;
