(() => {
  const root = document.documentElement;
  const appSlug = document.documentElement.dataset.appSlug || "php-mvc-starter";
  const themeStorageKey = `${appSlug}-theme`;
  const savedTheme = localStorage.getItem(themeStorageKey);
  const preferredTheme = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
  root.dataset.theme = savedTheme || preferredTheme;

  document.addEventListener("DOMContentLoaded", () => {
    const overlay = document.querySelector("[data-sidebar-overlay]");
    const sidebar = document.querySelector("[data-sidebar]");
    const menuToggle = document.querySelector("[data-menu-toggle]");
    const sidebarClose = document.querySelector("[data-sidebar-close]");
    const userMenuToggle = document.querySelector("[data-user-menu-toggle]");
    const userMenu = document.querySelector("[data-user-menu]");

    const closeSidebar = () => {
      overlay?.classList.remove("is-open");
      sidebar?.classList.remove("is-open");
      overlay?.setAttribute("aria-hidden", "true");
      document.body.classList.remove("drawer-open");
    };

    const openSidebar = () => {
      overlay?.classList.add("is-open");
      sidebar?.classList.add("is-open");
      overlay?.setAttribute("aria-hidden", "false");
      document.body.classList.add("drawer-open");
    };

    document.querySelectorAll("[data-theme-toggle]").forEach((button) => {
      button.addEventListener("click", () => {
        const nextTheme = root.dataset.theme === "dark" ? "light" : "dark";
        root.dataset.theme = nextTheme;
        localStorage.setItem(themeStorageKey, nextTheme);
      });
    });

    menuToggle?.addEventListener("click", openSidebar);
    sidebarClose?.addEventListener("click", closeSidebar);
    overlay?.addEventListener("click", (event) => {
      if (event.target === overlay) closeSidebar();
    });
    sidebar?.querySelectorAll("a").forEach((link) => link.addEventListener("click", closeSidebar));

    sidebar?.querySelectorAll("[data-nav-group-toggle]").forEach((toggle) => {
      toggle.addEventListener("click", () => {
        const items = toggle.nextElementSibling;
        const open = items?.classList.toggle("is-open") ?? false;
        toggle.setAttribute("aria-expanded", String(open));
      });
    });

    userMenuToggle?.addEventListener("click", () => {
      const open = userMenu?.classList.toggle("is-open") ?? false;
      userMenuToggle.setAttribute("aria-expanded", String(open));
    });

    document.addEventListener("click", (event) => {
      if (!userMenu?.classList.contains("is-open")) return;
      if (!userMenu.contains(event.target) && !userMenuToggle?.contains(event.target)) {
        userMenu.classList.remove("is-open");
        userMenuToggle?.setAttribute("aria-expanded", "false");
      }
    });

    document.addEventListener("keydown", (event) => {
      if (event.key !== "Escape") return;
      closeSidebar();
      userMenu?.classList.remove("is-open");
      userMenuToggle?.setAttribute("aria-expanded", "false");
    });
  });
})();
