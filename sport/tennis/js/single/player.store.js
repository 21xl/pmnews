class PlayerStore {
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
}

const playerStore = new PlayerStore();
export default playerStore;
