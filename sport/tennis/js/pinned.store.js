const apiUrl = process.env.API_URL || "http://localhost:3277";

class Store {
  constructor() {
    this.data = [];
    this.eventTarget = new EventTarget();
  }

  setData(newData) {
    this.data = newData;
    const event = new CustomEvent("dataChanged", { detail: this.data });
    this.eventTarget.dispatchEvent(event);
  }

  getData() {
    return this.data;
  }

  onDataChange(callback) {
    this.eventTarget.addEventListener("dataChanged", (event) => {
      callback(event.detail);
    });
  }

  async updatePinned(id) {
    const exists = this.data.some((pinnedItem) => pinnedItem.id === id);

    if (exists) {
      this.data = this.data.filter((pinnedItem) => pinnedItem.id !== id);
    } else {
      try {
        const response = await fetch(`${apiUrl}/api/tennis/tournaments`, {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify({ tournamentIds: [id] }),
        });

        if (!response.ok) {
          throw new Error(
            `Server error: ${response.status} - ${response.statusText}`
          );
        }

        const { data } = await response.json();

        if (data && data.length > 0) {
          this.data.push(data[0]);
        } else {
        }
      } catch (error) {
        console.error("Error fetching matches:", error);
        return;
      }
    }

    this.setData(this.data);
  }

  delPinned(id) {
    const exists = this.data.some((pinnedItem) => pinnedItem.id === id);

    if (exists) {
      this.data = this.data.filter((pinnedItem) => pinnedItem.id !== id);
    }

    this.setData(this.data);
  }
}

const store = new Store();
export default store;
