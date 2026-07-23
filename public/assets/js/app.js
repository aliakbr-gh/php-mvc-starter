const hideLoader = () => {
  const loader = document.querySelector(".page-loader");
  if (!loader) return;
  loader.classList.add("is-hidden");
  window.setTimeout(() => loader.remove(), 200);
};

if (document.readyState === "complete") {
  hideLoader();
} else {
  window.addEventListener("load", hideLoader, { once: true });
  // Never trap the user behind a loader if a third-party asset stalls.
  window.setTimeout(hideLoader, 8000);
}

const updateThemeControls = () => {
  const dark =
    document.documentElement.getAttribute("data-bs-theme") === "dark";
  document.querySelectorAll(".theme-label").forEach((label) => {
    label.textContent = dark ? "Light mode" : "Dark mode";
  });
  document.querySelectorAll(".theme-toggle").forEach((button) => {
    button.setAttribute(
      "aria-label",
      dark ? "Switch to light mode" : "Switch to dark mode"
    );
  });
};

document.querySelectorAll(".theme-toggle").forEach((button) => {
  button.addEventListener("click", () => {
    const next =
      document.documentElement.getAttribute("data-bs-theme") === "dark"
        ? "light"
        : "dark";
    document.documentElement.setAttribute("data-bs-theme", next);
    localStorage.setItem("theme", next);
    updateThemeControls();
  });
});
updateThemeControls();

const normalizePath = (path) => path.replace(/\/+$/, "") || "/";
const currentPath = normalizePath(window.location.pathname);
const navLinks = [...document.querySelectorAll(".side-nav a[href]")];
const matchingLinks = navLinks
  .map((link) => ({ link, path: normalizePath(new URL(link.href).pathname) }))
  .filter(
    ({ path }) =>
      currentPath === path ||
      (path !== "/" && currentPath.startsWith(`${path}/`))
  )
  .sort((a, b) => b.path.length - a.path.length);

if (matchingLinks.length) {
  const activeLink = matchingLinks[0].link;
  activeLink.classList.add("active");
  activeLink.setAttribute("aria-current", "page");

  const submenu = activeLink.closest(".nav-submenu");
  if (submenu) {
    submenu.classList.add("show");
    const toggle = document.querySelector(`[data-bs-target="#${submenu.id}"]`);
    if (toggle) {
      toggle.classList.add("active");
      toggle.classList.remove("collapsed");
      toggle.setAttribute("aria-expanded", "true");
    }
  }
}

if (window.DataTable) {
  document.querySelectorAll(".table-card table").forEach((table) => {
    new DataTable(table, {
      paging: false,
      searching: false,
      info: false,
      responsive: true,
      order: [],
      language: { emptyTable: "No records found." },
      columnDefs: [{ targets: -1, orderable: false }],
    });
  });
}

document.querySelectorAll(".toast-container .toast").forEach((element) => {
  bootstrap.Toast.getOrCreateInstance(element).show();
});
