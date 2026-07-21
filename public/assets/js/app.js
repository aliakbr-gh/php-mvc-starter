const revealPage = () => {
    document.body.classList.remove('page-loading');
    document.body.classList.add('page-ready');
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => requestAnimationFrame(revealPage), { once: true });
} else {
    requestAnimationFrame(revealPage);
}

document.querySelectorAll('.toast').forEach((toast) => {
    const close = () => {
        toast.classList.add('toast-hide');
        window.setTimeout(() => toast.remove(), 220);
    };
    toast.querySelector('button')?.addEventListener('click', close);
    window.setTimeout(close, 4500);
});
