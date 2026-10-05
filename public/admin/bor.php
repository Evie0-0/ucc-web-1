<?php
declare(strict_types=1);

require_once __DIR__ . '/../../bootstrap.php';
require_once __DIR__ . '/../../app/Support/helper.php';

use Core\User;
use Core\Database;
use Core\Token;

$pageTitle = 'Board of Regents';

User::requireAuthentication();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <title>UCC Admin | <?= e($pageTitle) ?></title>
    <link rel="icon" type="image/png" href="/admin/assets/images/ucc-LOGO.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Libre+Baskerville:wght@700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link href="https://cdn.jsdelivr.net/npm/quill@2/dist/quill.snow.css" rel="stylesheet"> 
    <link rel="stylesheet" href="/admin/assets/css/style.css">
    <link rel="stylesheet" href="/admin/assets/css/bor.css">
</head>

<body>

<div class="dashboard-layout">
    <?php require_once __DIR__ . '/template/sidebar.php' ?>
    <main class="main-content">
        <?php require_once __DIR__ . '/template/header.php' ?>
        <section class="bor-page">
            <div class="page-toolbar">
                <div class="toolbar-copy">
                    <p>Manage the members and official information of the University of Caloocan City Board of Regents.</p>
                </div>
                <button class="primary-btn" id="addBorBtn" type="button">
                    <i data-lucide="plus"></i>
                    Add Regent
                </button>
            </div>

            <div class="bor-card">
                <div class="table-toolbar">
                    <div class="search-box">
                        <i data-lucide="search"></i>
                        <input id="borSearch" type="search" placeholder="Search regent..." autocomplete="off">
                    </div>
                    <div class="bor-filter">
                        <select id="borStatusFilter">
                            <option value="active">Active</option>
                            <option value="hidden">Hidden</option>
                        </select>
                    </div>
                    <div class="result-count" id="borCount">
                        17 members
                    </div>
                </div>
                <div class="table-wrap">
                    <table class="bor-table">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Position</th>
                                <th>Status</th>
                                <th class="actions-head">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="borTableBody"></tbody>
                    </table>
                </div>
                <div class="empty-state" id="borEmpty" hidden>
                    <i data-lucide="users-round"></i>
                    <h3>No regents found</h3>
                    <p>Try a different search term.</p>
                </div>
            </div>
        </section>

        <div class="modal-overlay" id="borModal" hidden>
            <div class="official-modal" role="dialog" aria-modal="true" aria-labelledby="borModalTitle">
                <div class="modal-header">
                    <div>
                        <span class="modal-eyebrow">Board of Regents</span>
                        <h2 id="borModalTitle">Add Regent</h2>
                    </div>
                    <button class="modal-close" id="closeBorModal" type="button" aria-label="Close">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <form id="borForm">
                    <div class="form-grid">
                        <label>
                            <span>Photo</span>
                            <input id="borPhoto" type="text" placeholder="Upload photo">
                        </label>
                        <label>
                            <span>Full Name</span>
                            <input id="borName" type="text" required>
                        </label>
                        <label class="full">
                            <span>Position</span>
                            <input id="borPosition" type="text" required>
                        </label>
                        <label class="full">
                            <span>Biography / Short Description</span>
                            <textarea id="borBio" rows="4" placeholder="Enter biography or additional information"></textarea>
                        </label>
                        <label>
                            <span>Status</span>
                            <select id="borStatus">
                                <option>Active</option>
                                <option>Hidden</option>
                            </select>
                        </label>
                    </div>
                    <div class="form-actions">
                        <button class="secondary-btn" id="cancelBorBtn" type="button">
                            Cancel
                        </button>
                        <button class="primary-btn" type="submit">
                            <i data-lucide="save"></i>
                            Save Regent
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-overlay" id="borViewModal" hidden>
            <div class="official-modal view-modal" role="dialog" aria-modal="true" aria-labelledby="borViewTitle">
                <div class="modal-header">
                    <div>
                        <span class="modal-eyebrow">Regent Details</span>
                        <h2 id="borViewTitle">Board Member</h2>
                    </div>
                    <button class="modal-close" id="closeBorView" type="button" aria-label="Close">
                        <i data-lucide="x"></i>
                    </button>
                </div>
                <div class="details-layout">
                    <div class="details-photo" id="borViewPhoto"></div>
                    <div class="details-copy">
                        <h3 id="borViewName"></h3>
                        <p class="details-position" id="borViewPosition"></p>
                        <span class="status-badge" id="borViewStatus">Active</span>
                        <div class="details-divider"></div>
                        <p class="details-label">Biography / Short Description</p>
                        <p class="details-bio" id="borViewBio"></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
<script>
lucide.createIcons();

document.querySelectorAll(".menu-title").forEach(button => {
    button.addEventListener("click", () => {
        button.parentElement.classList.toggle("open");
    });
});
/*
const borMembers = [
    {
        photo: "BOR-MALAPITAN.jpg",
        name: 'Hon. Dale Gonzalo "ALONG" R. MALAPITAN',
        position: "Chairperson, Board of Regents",
        bio: "Mayor Dale Gonzalo “Along” Malapitan is the 25th local chief executive of the historic City of Caloocan and the Chairman of the Board of Regents of the University of Caloocan City (UCC).",
        status: "Active"
    },
    {
        photo: "BOR-NAS.png",
        name: "Atty. Jessamine Jared S. Nas",
        position: "Vice Chairman",
        bio: "Board Member",
        status: "Active"
    },
    {
        photo: "BOR-CIEGO.png",
        name: "EnP. Aurora C. Ciego, DPA",
        position: "City Administrator",
        bio: "Board Member",
        status: "Active"
    },
    {
        photo: "BOR-CAMINA.png",
        name: "Atty. Michael Arthur Camina",
        position: "Member",
        bio: "Board Member",
        status: "Active"
    },
    {
        photo: "BOR-CUNANAN.png",
        name: "Hon. Carolyn C. Cunanan",
        position: "Member",
        bio: "Board Member",
        status: "Active"
    },
    {
        photo: "BOR-PRADO.png",
        name: "Hon. Atty. Patrick L. Prado",
        position: "Member",
        bio: "Majority Floor Leader / Caloocan City Council",
        status: "Active"
    },
    {
        photo: "BOR-LOPEZ.png",
        name: "Engr. Wenald H. Lopez, PhD",
        position: "Member",
        bio: "Board Member",
        status: "Active"
    },
    {
        photo: "BOR-JUNIO.png",
        name: "Mr. John Nicklaus S. Junio",
        position: "Member",
        bio: "Board Member",
        status: "Active"
    },
    {
        photo: "BOR-DANTAY.png",
        name: "Rodrigo M. Dantay Jr., DPA, EdD",
        position: "Member",
        bio: "DPA, EdD — Board Member",
        status: "Active"
    },
    {
        photo: "BOR-CARANDANG.png",
        name: "Cecille G. Carandang, CESO VI",
        position: "Member",
        bio: "Board Member",
        status: "Active"
    },
    {
        photo: "BOR-REYES.png",
        name: "Dionisio S. Reyes, DPA, LPT",
        position: "Member",
        bio: "Board Member",
        status: "Active"
    },
    {
        photo: "BOR-MACKAY.png",
        name: "Dr. Eloisa P. Mackay",
        position: "Member",
        bio: "Board Member",
        status: "Active"
    },
    {
        photo: "BOR-RABANAL.png",
        name: "Mr. Paul Daniel C. Rabanal",
        position: "Member",
        bio: "Board Member",
        status: "Active"
    },
    {
        photo: "BOR-GARCIA.png",
        name: "Ms. Princess Garcia",
        position: "Member",
        bio: "Board Member",
        status: "Active"
    },
    {
        photo: "BOR-YAKIT.png",
        name: "Ms. Leslie Anne C. Yakit",
        position: "Member",
        bio: "Board Member",
        status: "Active"
    },
    {
        photo: "BOR-GONZALES.png",
        name: "Ms. Violeta Y. Gonzales",
        position: "Ex-Officio Member",
        bio: "Board Member",
        status: "Active"
    },
    {
        photo: "BOR-YEE.png",
        name: "Catlleya C. Yee, PhD-ELL, LPT",
        position: "Secretary",
        bio: "Board Member",
        status: "Active"
    }
];

const tableBody = document.getElementById("borTableBody");
const searchInput = document.getElementById("borSearch");
const statusFilter = document.getElementById("borStatusFilter");
const resultCount = document.getElementById("borCount");
const emptyState = document.getElementById("borEmpty");

const modal = document.getElementById("borModal");
const viewModal = document.getElementById("borViewModal");

let editingIndex = null;

function escapeHTML(value) {
    return String(value ?? "").replace(/[&<>'"]/g, character => {
        return {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            "'": "&#39;",
            '"': "&quot;"
        }[character];
    });
}

function initials(name) {
    return name
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map(word => word[0])
        .join("")
        .toUpperCase();
}

function photoMarkup(member) {
    if (!member.photo) {
        return `
            <div class="table-photo">
                <span style="display:grid">${escapeHTML(initials(member.name))}</span>
            </div>
        `;
    }

    return `
        <div class="table-photo">
            <img
                src="images/${escapeHTML(member.photo)}"
                alt="${escapeHTML(member.name)}"
                onerror="this.style.display='none';this.nextElementSibling.style.display='grid'"
            >
            <span>${escapeHTML(initials(member.name))}</span>
        </div>
    `;
}

function renderBoard() {
    const term = searchInput.value.trim().toLowerCase();
    const selectedStatus = statusFilter.value;

    const filtered = borMembers
        .map((member, index) => ({
            ...member,
            index
        }))
        .filter(member => {

            const searchMatch =
                !term ||
                `${member.name} ${member.position} ${member.bio}`
                    .toLowerCase()
                    .includes(term);

            const statusMatch =
                selectedStatus === "all" ||
                member.status.toLowerCase() === selectedStatus;

            return searchMatch && statusMatch;
        });

    tableBody.innerHTML = filtered.map(member => `
        <tr>

            <td>
                ${photoMarkup(member)}
            </td>

            <td>
                <div class="name-cell">
                    <strong>${escapeHTML(member.name)}</strong>
                    <small>${escapeHTML(member.bio || "—")}</small>
                </div>
            </td>

            <td>
                ${escapeHTML(member.position)}
            </td>

            <td>
                <span class="status-badge ${
                    member.status === "Active"
                        ? "active"
                        : "hidden-status"
                }">
                    ${escapeHTML(member.status)}
                </span>
            </td>

            <td class="actions-cell">

                <button
                    class="icon-btn view"
                    type="button"
                    title="View"
                    aria-label="View ${escapeHTML(member.name)}"
                    data-action="view"
                    data-index="${member.index}"
                >
                    <i data-lucide="eye"></i>
                </button>

                <button
                    class="icon-btn edit"
                    type="button"
                    title="Edit"
                    aria-label="Edit ${escapeHTML(member.name)}"
                    data-action="edit"
                    data-index="${member.index}"
                >
                    <i data-lucide="pencil"></i>
                </button>

                <button
                    class="icon-btn delete"
                    type="button"
                    title="Delete"
                    aria-label="Delete ${escapeHTML(member.name)}"
                    data-action="delete"
                    data-index="${member.index}"
                >
                    <i data-lucide="trash-2"></i>
                </button>

            </td>

        </tr>
    `).join("");

    resultCount.textContent =
        `${filtered.length} ${
            filtered.length === 1 ? "member" : "members"
        }`;

    emptyState.hidden = filtered.length !== 0;

    tableBody.parentElement.parentElement.style.display =
        filtered.length ? "" : "none";

    lucide.createIcons();
}

function openBorModal(index = null) {

    editingIndex = index;

    document.getElementById("borModalTitle").textContent =
        index === null ? "Add Regent" : "Edit Regent";

    const member = index === null
        ? {
            photo: "",
            name: "",
            position: "",
            bio: "",
            status: "Active"
        }
        : borMembers[index];

    document.getElementById("borPhoto").value = member.photo || "";
    document.getElementById("borName").value = member.name || "";
    document.getElementById("borPosition").value = member.position || "";
    document.getElementById("borBio").value = member.bio || "";
    document.getElementById("borStatus").value = member.status || "Active";

    modal.hidden = false;
    document.body.classList.add("modal-open");

    setTimeout(() => {
        document.getElementById("borName").focus();
    }, 50);
}

function closeBorModal() {
    modal.hidden = true;

    if (viewModal.hidden) {
        document.body.classList.remove("modal-open");
    }
}

function openBorView(index) {

    const member = borMembers[index];

    if (!member) {
        return;
    }

    document.getElementById("borViewTitle").textContent =
        member.name;

    document.getElementById("borViewName").textContent =
        member.name;

    document.getElementById("borViewPosition").textContent =
        member.position;

    document.getElementById("borViewBio").textContent =
        member.bio || "No additional description provided.";

    const status = document.getElementById("borViewStatus");

    status.textContent = member.status;

    status.className =
        `status-badge ${
            member.status === "Active"
                ? "active"
                : "hidden-status"
        }`;

    const photo = document.getElementById("borViewPhoto");

    photo.innerHTML = `
        <img
            src="images/${escapeHTML(member.photo)}"
            alt="${escapeHTML(member.name)}"
            onerror="this.style.display='none';this.nextElementSibling.style.display='grid'"
        >
        <span>${escapeHTML(initials(member.name))}</span>
    `;

    viewModal.hidden = false;
    document.body.classList.add("modal-open");

    lucide.createIcons();
}

function closeBorView() {

    viewModal.hidden = true;

    if (modal.hidden) {
        document.body.classList.remove("modal-open");
    }
}

document
    .getElementById("addBorBtn")
    .addEventListener("click", () => openBorModal());

document
    .getElementById("closeBorModal")
    .addEventListener("click", closeBorModal);

document
    .getElementById("cancelBorBtn")
    .addEventListener("click", closeBorModal);

document
    .getElementById("closeBorView")
    .addEventListener("click", closeBorView);

searchInput.addEventListener("input", renderBoard);
statusFilter.addEventListener("change", renderBoard);

tableBody.addEventListener("click", event => {

    const button = event.target.closest("[data-action]");

    if (!button) {
        return;
    }

    const index = Number(button.dataset.index);
    const action = button.dataset.action;

    if (action === "view") {
        openBorView(index);
    }

    if (action === "edit") {
        openBorModal(index);
    }

    if (action === "delete") {

        const member = borMembers[index];

        if (
            member &&
            confirm(`Delete ${member.name}?`)
        ) {
            borMembers.splice(index, 1);
            renderBoard();
        }
    }
});

document
    .getElementById("borForm")
    .addEventListener("submit", event => {

        event.preventDefault();

        const member = {
            photo: document
                .getElementById("borPhoto")
                .value
                .trim(),

            name: document
                .getElementById("borName")
                .value
                .trim(),

            position: document
                .getElementById("borPosition")
                .value
                .trim(),

            bio: document
                .getElementById("borBio")
                .value
                .trim() || "No additional description provided.",

            status: document
                .getElementById("borStatus")
                .value
        };

        if (!member.name || !member.position) {
            return;
        }

        if (editingIndex === null) {
            borMembers.push(member);
        } else {
            borMembers[editingIndex] = member;
        }

        closeBorModal();
        renderBoard();
    });

modal.addEventListener("click", event => {

    if (event.target === modal) {
        closeBorModal();
    }

});

viewModal.addEventListener("click", event => {

    if (event.target === viewModal) {
        closeBorView();
    }

});

document.addEventListener("keydown", event => {

    if (event.key !== "Escape") {
        return;
    }

    if (!modal.hidden) {
        closeBorModal();
    }

    if (!viewModal.hidden) {
        closeBorView();
    }

});

renderBoard();
 */
</script>

</body>
</html>
