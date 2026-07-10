document.addEventListener("DOMContentLoaded", () => {
    const toggle = document.querySelector("[data-kmb-menu-toggle]");
    const menu = document.getElementById("kmbMenu");

    toggle?.addEventListener("click", () => {
        menu?.classList.toggle("is-open");
    });
});
