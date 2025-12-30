document.addEventListener("DOMContentLoaded", () => {
    // Show loader when any filter changes (since form auto-submits)
    const form = document.querySelector(".global-filter-grid");
    if (!form) return;

    const inputs = form.querySelectorAll("select, input");
    const loader = document.querySelector(".filter-loading");

    inputs.forEach((input) => {
        input.addEventListener("change", () => {
            if (loader) {
                loader.style.display = "flex";
            }
        });
    });
});
