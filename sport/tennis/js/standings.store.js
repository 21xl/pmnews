class Store {
  constructor() {
    this.data = null;
    this.eventTarget = new EventTarget();
  }

  setData(newData) {
    if (!newData || typeof newData !== "object") {
      console.error("Некорректный формат данных для setData");
      return;
    }

    this.data = [...newData];

    const event = new CustomEvent("dataChanged", {
      detail: { data: this.data },
    });
    this.eventTarget.dispatchEvent(event);
  }

  getData() {
    return this.data;
  }

  getMatchById(matchId) {
    if (!this.data || !Array.isArray(this.data.matches)) {
      return null;
    }

    return this.data.matches.find((match) => match.id === matchId) || null;
  }

  updateMatchData(matchUpdate) {
    console.log("updateMatchData", matchUpdate);
    if (!this.data || !Array.isArray(this.data.matches)) {
      console.error("Данные не инициализированы или некорректны.");
      return;
    }

    const [
      matchId,
      newStatusId,
      newHomeScores,
      newAwayScores,
      kickoffTimestamp,
    ] = matchUpdate;

    let updatedMatch = null;

    this.data.matches = this.data.matches.map((match) => {
      if (match.id === matchId) {
        updatedMatch = {
          ...match,
          status_id: newStatusId,
          home_scores: newHomeScores,
          away_scores: newAwayScores,
          kickoff_timestamp: kickoffTimestamp,
        };
        return updatedMatch;
      }
      return match;
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

  filterMatchesByStatus(status) {
    if (!this.data || !Array.isArray(this.data.matches)) {
      return [];
    }

    return this.data.matches.filter(
      (match) => String(match.status_id) === status
    );
  }

  onDataChange(callback) {
    this.eventTarget.addEventListener("dataChanged", (event) => {
      callback(event.detail);
    });
  }
}

const competitionStore = new Store();
export default competitionStore;
