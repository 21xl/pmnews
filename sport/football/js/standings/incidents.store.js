class IncidentStore {
  constructor() {
    this.data = [];
    this.eventTarget = new EventTarget();
  }

  setData(newData) {
    try {
      this.data = newData;
      const event = new CustomEvent("dataChanged", { detail: this.data });
      this.eventTarget.dispatchEvent(event);
    } catch (error) {
      console.error("Error in setData:", error);
    }
  }

  getData() {
    try {
      return this.data;
    } catch (error) {
      console.error("Error in getData:", error);
      return [];
    }
  }

  onDataChange(callback) {
    try {
      this.eventTarget.addEventListener("dataChanged", (event) => {
        try {
          callback(event.detail);
        } catch (callbackError) {
          console.error("Error in onDataChange callback:", callbackError);
        }
      });
    } catch (error) {
      console.error("Error in onDataChange:", error);
    }
  }

  /**
   * Добавляет новые инциденты в Store.
   * @param {Object} newIncidents - Объект с ID и массивом инцидентов.
   * @returns {Object|null} - Возвращает последний добавленный инцидент или null.
   */
  addIncidents(newIncidents) {
    try {
      const { id, incidents } = newIncidents;
      let addedIncident = null;

      const existingItem = this.data.find((item) => item.id === id);

      if (existingItem) {
        if (incidents.length > existingItem.incidents.length) {
          const newIncident = incidents.slice(existingItem.incidents.length);
          existingItem.incidents.push(...newIncident);
          addedIncident = {
            id,
            incident: newIncident[newIncident.length - 1],
          };
        }
      } else {
        this.data.push({ id, incidents });
        addedIncident = {
          id,
          incident: incidents[incidents.length - 1],
        };
      }

      this.setData(this.data);

      return addedIncident;
    } catch (error) {
      console.error("Error in addIncidents:", error);
      return null;
    }
  }
}

const incidentStore = new IncidentStore();
export default incidentStore;
