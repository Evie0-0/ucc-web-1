function initializeComponents() {
    if (window.lucide) {
        lucide.createIcons();
    }

    document.querySelectorAll(".menu-title").forEach(button => {
        button.addEventListener("click", () => {
            const menuGroup = button.closest(".menu-group");

            if (menuGroup) {
                menuGroup.classList.toggle("open");
            }
        });
    });

    setActivePage();
}

function setActivePage() {
    const currentPath = window.location.pathname.toLowerCase();

    document.querySelectorAll(".sidebar-nav a").forEach(link => {
        const linkPath = new URL(link.href).pathname.toLowerCase();

        if (linkPath === currentPath) {
            link.classList.add("active");

            const menuGroup = link.closest(".menu-group");

            if (menuGroup) {
                menuGroup.classList.add("open");
                menuGroup.querySelector(".menu-title")?.classList.add("active");
            }
        }
    });
}
