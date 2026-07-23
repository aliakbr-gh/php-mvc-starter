const hideLoader = () => {
    const loader = document.querySelector('.page-loader');
    if (!loader) return;
    loader.classList.add('is-hidden');
    window.setTimeout(() => loader.remove(), 200);
};

if (document.readyState === 'complete') {
    hideLoader();
} else {
    window.addEventListener('load', hideLoader, { once: true });
    // Never trap the user behind a loader if a third-party asset stalls.
    window.setTimeout(hideLoader, 8000);
}

const updateThemeControls = () => {
    const dark = document.documentElement.getAttribute('data-bs-theme') === 'dark';
    document.querySelectorAll('.theme-label').forEach((label) => { label.textContent = dark ? 'Light mode' : 'Dark mode'; });
    document.querySelectorAll('.theme-toggle').forEach((button) => {
        button.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
    });
};

document.querySelectorAll('.theme-toggle').forEach((button) => {
    button.addEventListener('click', () => {
        const next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-bs-theme', next);
        localStorage.setItem('theme', next);
        updateThemeControls();
    });
});
updateThemeControls();

const currentPath = window.location.pathname.replace(/\/+$/, '');
document.querySelectorAll('.side-nav a').forEach((link) => {
    const linkPath = new URL(link.href).pathname.replace(/\/+$/, '');
    if (linkPath === currentPath) link.classList.add('active');
});

if (window.DataTable) {
    document.querySelectorAll('.table-card table').forEach((table) => {
        new DataTable(table, {
            paging: false,
            searching: false,
            info: false,
            responsive: true,
            order: [],
            language: { emptyTable: 'No records found.' },
            columnDefs: [{ targets: -1, orderable: false }]
        });
    });
}

document.querySelectorAll('.toast-stack .toast').forEach((toast) => {
    const close = () => {
        toast.classList.add('toast-hide');
        window.setTimeout(() => toast.remove(), 220);
    };
    toast.querySelector('button')?.addEventListener('click', close);
    window.setTimeout(close, 4500);
});
