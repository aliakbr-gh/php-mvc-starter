(() => {
  const reduceMotion = window.matchMedia("(prefers-reduced-motion: reduce)").matches;

  document.querySelectorAll("[data-toast]").forEach((toast) => {
    const duration = Number(toast.dataset.duration) || 2000;
    let timeoutId;
    let remaining = duration;
    let startedAt;
    let isRunning = false;

    const dismiss = () => {
      window.clearTimeout(timeoutId);
      toast.classList.add("is-leaving");
      toast.addEventListener("animationend", () => toast.remove(), { once: true });

      if (reduceMotion) {
        toast.remove();
      }
    };

    const startTimer = () => {
      if (isRunning || remaining <= 0) return;
      isRunning = true;
      startedAt = Date.now();
      timeoutId = window.setTimeout(dismiss, remaining);
    };

    const pauseTimer = () => {
      if (!isRunning) return;
      window.clearTimeout(timeoutId);
      remaining -= Date.now() - startedAt;
      isRunning = false;
    };

    toast.addEventListener("mouseenter", pauseTimer);
    toast.addEventListener("mouseleave", startTimer);
    toast.addEventListener("focusin", pauseTimer);
    toast.addEventListener("focusout", (event) => {
      if (!toast.contains(event.relatedTarget)) startTimer();
    });

    startTimer();
  });
})();
