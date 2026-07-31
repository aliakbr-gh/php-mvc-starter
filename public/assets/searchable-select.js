(() => {
  class SearchableSelect {
    constructor(select) {
      this.select = select;
      this.options = Array.from(select.options);
      this.placeholder = this.options.find((option) => option.value === "")?.textContent.trim()
        || "Search and select";

      this.wrapper = document.createElement("div");
      this.wrapper.className = "searchable-select";

      this.input = document.createElement("input");
      this.input.type = "search";
      this.input.className = "searchable-select__input";
      this.input.placeholder = this.placeholder;
      this.input.autocomplete = "off";
      this.input.setAttribute("role", "combobox");
      this.input.setAttribute("aria-expanded", "false");
      this.input.required = select.required;

      this.chevron = document.createElement("span");
      this.chevron.className = "searchable-select__chevron";
      this.chevron.setAttribute("aria-hidden", "true");
      this.chevron.innerHTML = `
        <svg viewBox="0 0 20 20" width="18" height="18" fill="none">
          <path d="m5 7.5 5 5 5-5" stroke="currentColor" stroke-width="1.8"
            stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      `;

      this.dropdown = document.createElement("div");
      this.dropdown.className = "searchable-select__dropdown";
      this.dropdown.setAttribute("role", "listbox");

      this.select.required = false;
      this.select.tabIndex = -1;
      this.select.setAttribute("aria-hidden", "true");
      this.select.classList.add("searchable-select__native");
      this.select.parentNode.insertBefore(this.wrapper, this.select);
      this.wrapper.append(this.select, this.input, this.chevron, this.dropdown);

      this.render();
      this.syncInput();
      this.bindEvents();
    }

    bindEvents() {
      this.input.addEventListener("focus", () => this.open());
      this.input.addEventListener("click", () => this.open());
      this.input.addEventListener("input", () => {
        this.render(this.input.value);
        this.open();
      });
      this.input.addEventListener("keydown", (event) => {
        if (event.key === "Escape") {
          this.close();
        }
        if (event.key === "Enter" && this.isOpen()) {
          const firstOption = this.dropdown.querySelector("button:not([hidden])");
          if (firstOption) {
            event.preventDefault();
            firstOption.click();
          }
        }
      });
      this.select.addEventListener("change", () => this.syncInput());
      document.addEventListener("click", (event) => {
        if (!this.wrapper.contains(event.target)) {
          this.close();
        }
      });
    }

    render(search = "") {
      const query = search.trim().toLowerCase();
      const matching = this.options.filter((option) => {
        if (option.value === "") return query === "";
        return option.textContent.toLowerCase().includes(query);
      });

      this.dropdown.replaceChildren();

      if (matching.length === 0) {
        const empty = document.createElement("p");
        empty.className = "searchable-select__empty";
        empty.textContent = "No options found";
        this.dropdown.append(empty);
        return;
      }

      matching.forEach((option) => {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "searchable-select__option";
        button.textContent = option.textContent.trim();
        button.setAttribute("role", "option");
        button.setAttribute("aria-selected", String(option.selected));
        button.addEventListener("click", () => this.choose(option));
        this.dropdown.append(button);
      });
    }

    choose(option) {
      this.select.value = option.value;
      this.select.dispatchEvent(new Event("change", { bubbles: true }));
      this.input.value = option.value === "" ? "" : option.textContent.trim();
      this.input.setCustomValidity("");
      this.close();

      if (this.select.hasAttribute("data-auto-submit")) {
        this.select.form?.requestSubmit();
      }
    }

    syncInput() {
      const selected = this.select.options[this.select.selectedIndex];
      this.input.value = selected && selected.value !== "" ? selected.textContent.trim() : "";
      this.render();
    }

    open() {
      this.wrapper.classList.add("is-open");
      this.input.setAttribute("aria-expanded", "true");
    }

    close() {
      this.wrapper.classList.remove("is-open");
      this.input.setAttribute("aria-expanded", "false");
      this.syncInput();
    }

    isOpen() {
      return this.wrapper.classList.contains("is-open");
    }
  }

  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("select:not([data-native-select])").forEach((select) => {
      new SearchableSelect(select);
    });
  });
})();
