let contacts = [];
let contactsCurrentPage = 1;
let contactsLastPage = 1;

// =========================
// HELPERS
// =========================

function getValue(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : "";
}

function setValue(id, value) {
    const el = document.getElementById(id);
    if (el) el.value = value ?? "";
}

function clearFields(ids) {
    ids.forEach((id) => {
        const el = document.getElementById(id);
        if (el) el.value = "";
    });
}

function getContactName(c) {
    return c.nome || c.name || "-";
}

function getPrimaryPhone(c) {
    if (c.phones && c.phones.length) {
        const principal = c.phones.find((p) => p.principal);
        return principal || c.phones[0];
    }

    return null;
}

function getContactPhone(c) {
    const p = getPrimaryPhone(c);

    if (p) {
        return `${p.ddd || ""}${p.telefone || ""}`;
    }

    return c.telefone || c.phone || "-";
}

function getContactDdd(c) {
    const p = getPrimaryPhone(c);
    return p?.ddd || c.ddd || "";
}

function getContactPhoneOnly(c) {
    const p = getPrimaryPhone(c);
    return p?.telefone || c.phone || c.telefone || "";
}

function getContactUf(c) {
    return c.uf || c.estado || "";
}

function getContactFilters() {
    const params = new URLSearchParams();

    params.append("page", contactsCurrentPage);
    params.append("per_page", getValue("filterPerPage") || "50");

    const filters = {
        search: getValue("filterSearch"),
        cidade: getValue("filterCidade"),
        estado: getValue("filterEstado"),
        tag: getValue("filterTag"),
        opt_in: getValue("filterOptIn"),
        ativo: getValue("filterAtivo"),
        has_phone: getValue("filterHasPhone"),
    };

    Object.entries(filters).forEach(([key, value]) => {
        if (value !== "") {
            params.append(key, value);
        }
    });

    return params.toString();
}

// =========================
// LOAD / RENDER
// =========================

async function loadContacts(page = 1) {
    contactsCurrentPage = page;

    const response = await api("/contacts?" + getContactFilters());

    contacts = response.data || [];
    contactsCurrentPage = response.current_page || 1;
    contactsLastPage = response.last_page || 1;

    renderContacts();
    renderContactsPagination(response);

    if (typeof updateDashboardCards === "function") {
        updateDashboardCards();
    }
}

function renderContacts() {
    const container = document.getElementById("contactsList");

    if (!container) return;

    container.innerHTML = "";

    if (!contacts.length) {
        container.innerHTML = `
            <div class="text-sm text-slate-500 bg-slate-50 border rounded-lg p-4">
                Nenhum contato encontrado.
            </div>
        `;
        return;
    }

    contacts.forEach((c) => {
        const div = document.createElement("div");

        div.className =
            "flex justify-between items-center border border-slate-200 p-3 rounded-lg bg-slate-50";

        div.innerHTML = `
            <div>
                <strong>${getContactName(c)}</strong><br>

                <small class="text-slate-500">
                    CPF: ${c.cpf || "-"} | Tel: ${getContactPhone(c)}
                </small><br>

                <small class="text-slate-400">
                    ${c.cidade || ""} ${getContactUf(c) || ""}
                    ${c.tag ? " | Tag: " + c.tag : ""}
                </small>
            </div>

            <div class="flex gap-2">
                <button onclick="openEditContact(${c.id})"
                    class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 text-sm">
                    Editar
                </button>

                <button onclick="deleteContact(${c.id})"
                    class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 text-sm">
                    Remover
                </button>
            </div>
        `;

        container.appendChild(div);
    });
}

function renderContactsPagination(response) {
    const info = document.getElementById("contactsPaginationInfo");
    const controls = document.getElementById("contactsPaginationControls");

    if (info) {
        info.innerHTML = `
            Mostrando <strong>${response.from || 0}</strong> até
            <strong>${response.to || 0}</strong> de
            <strong>${response.total || 0}</strong> contatos
        `;
    }

    if (!controls) return;

    controls.innerHTML = `
        <div class="flex flex-wrap items-center gap-2 mt-4">
            <button onclick="loadContacts(1)"
                ${contactsCurrentPage === 1 ? "disabled" : ""}
                class="px-3 py-2 bg-slate-200 rounded disabled:opacity-50">
                Primeira
            </button>

            <button onclick="loadContacts(${contactsCurrentPage - 1})"
                ${contactsCurrentPage === 1 ? "disabled" : ""}
                class="px-3 py-2 bg-slate-200 rounded disabled:opacity-50">
                Anterior
            </button>

            <span class="text-sm text-slate-600">
                Página <strong>${contactsCurrentPage}</strong> de <strong>${contactsLastPage}</strong>
            </span>

            <button onclick="loadContacts(${contactsCurrentPage + 1})"
                ${contactsCurrentPage === contactsLastPage ? "disabled" : ""}
                class="px-3 py-2 bg-slate-200 rounded disabled:opacity-50">
                Próxima
            </button>

            <button onclick="loadContacts(${contactsLastPage})"
                ${contactsCurrentPage === contactsLastPage ? "disabled" : ""}
                class="px-3 py-2 bg-slate-200 rounded disabled:opacity-50">
                Última
            </button>

            <input id="goToPageInput"
                type="number"
                min="1"
                max="${contactsLastPage}"
                placeholder="Página"
                class="border rounded px-3 py-2 w-24">

            <button onclick="goToContactPage()"
                class="px-3 py-2 bg-slate-700 text-white rounded">
                Ir
            </button>
        </div>
    `;
}

// =========================
// FILTERS
// =========================

function applyContactFilters() {
    loadContacts(1);
}

function clearContactFilters() {
    [
        "filterSearch",
        "filterCidade",
        "filterEstado",
        "filterTag",
        "filterOptIn",
        "filterAtivo",
        "filterHasPhone",
    ].forEach((id) => setValue(id, ""));

    setValue("filterPerPage", "50");

    loadContacts(1);
}

function goToContactPage() {
    const page = parseInt(getValue("goToPageInput"));

    if (!page || page < 1) {
        loadContacts(1);
        return;
    }

    if (page > contactsLastPage) {
        loadContacts(contactsLastPage);
        return;
    }

    loadContacts(page);
}

// =========================
// CREATE MANUAL
// =========================

async function createContact() {
    const payload = {
        name: getValue("contactNome"),
        phone: getValue("contactTelefone"),
        email: getValue("contactEmail"),
        cpf: getValue("contactCpf"),
        cidade: getValue("contactCidade"),
        uf: getValue("contactEstado"),
        estado: getValue("contactEstado"),
        tag: getValue("contactTag"),
        opt_in: true,
        ativo: true,
    };

    if (!payload.name || !payload.phone) {
        alert("Informe pelo menos nome e telefone.");
        return;
    }

    await api("/contacts", {
        method: "POST",
        body: JSON.stringify(payload),
    });

    clearFields([
        "contactNome",
        "contactTelefone",
        "contactEmail",
        "contactCpf",
        "contactCidade",
        "contactEstado",
        "contactTag",
    ]);

    await loadContacts(contactsCurrentPage);

    alert("Contato cadastrado com sucesso!");
}

// =========================
// EDIT
// =========================

function openEditContact(id) {
    const contact = contacts.find((c) => c.id === id);

    if (!contact) {
        alert("Contato não encontrado.");
        return;
    }

    setValue("editContactId", contact.id);
    setValue("editContactNome", getContactName(contact));
    setValue("editContactCpf", contact.cpf || "");
    setValue("editContactDdd", getContactDdd(contact));
    setValue("editContactTelefone", getContactPhoneOnly(contact));
    setValue("editContactEmail", contact.email || "");
    setValue("editContactCidade", contact.cidade || "");
    setValue("editContactEstado", getContactUf(contact));
    setValue("editContactBairro", contact.bairro || "");
    setValue("editContactAddress", contact.address || "");
    setValue("editContactCep", contact.cep || "");

    const modal = document.getElementById("editContactModal");
    if (modal) modal.classList.remove("hidden");
}

function closeEditContact() {
    const modal = document.getElementById("editContactModal");
    if (modal) modal.classList.add("hidden");
}

async function updateContact() {
    const id = getValue("editContactId");

    if (!id) {
        alert("Contato inválido.");
        return;
    }

    const payload = {
        name: getValue("editContactNome"),
        cpf: getValue("editContactCpf"),
        ddd: getValue("editContactDdd"),
        phone: getValue("editContactTelefone"),
        email: getValue("editContactEmail"),
        cidade: getValue("editContactCidade"),
        uf: getValue("editContactEstado"),
        estado: getValue("editContactEstado"),
        bairro: getValue("editContactBairro"),
        address: getValue("editContactAddress"),
        cep: getValue("editContactCep"),
    };

    if (!payload.name || !payload.phone) {
        alert("Informe pelo menos nome e telefone.");
        return;
    }

    await api("/contacts/" + id, {
        method: "PUT",
        body: JSON.stringify(payload),
    });

    closeEditContact();
    await loadContacts(contactsCurrentPage);

    alert("Contato atualizado com sucesso!");
}

// =========================
// IMPORT CSV
// =========================

async function importContacts(csv) {
    const lines = csv.trim().split("\n");

    for (const line of lines) {
        const [telefone, nome] = line.split(",");

        if (!telefone) continue;

        await api("/contacts", {
            method: "POST",
            body: JSON.stringify({
                name: nome?.trim() || null,
                phone: telefone.trim(),
                opt_in: true,
                ativo: true,
            }),
        });
    }

    clearFields(["csvInput"]);

    await loadContacts(1);
}

// =========================
// IMPORT EXCEL
// =========================

async function importExcelContacts() {
    const input = document.getElementById("excelFile");
    const result = document.getElementById("excelImportResult");

    if (!input || !input.files.length) {
        alert("Selecione uma planilha primeiro");
        return;
    }

    const token = localStorage.getItem("token");
    const formData = new FormData();

    formData.append("file", input.files[0]);

    if (result) {
        result.innerHTML = "Importando planilha...";
    }

    const res = await fetch("/api/contacts/import-excel", {
        method: "POST",
        headers: {
            Authorization: "Bearer " + token,
            Accept: "application/json",
        },
        body: formData,
    });

    const data = await res.json();

    if (!res.ok) {
        if (result) {
            result.innerHTML = `<span class="text-red-600">Erro ao importar planilha.</span>`;
        }

        throw new Error(data.message || "Erro ao importar Excel");
    }

    if (result) {
        result.innerHTML = `
            <div class="text-emerald-700">
                ${data.message || "Importação concluída."}
            </div>
        `;
    }

    input.value = "";

    await loadContacts(1);
}

// =========================
// DELETE
// =========================

async function deleteContact(id) {
    if (!confirm("Deseja remover este contato?")) return;

    await api("/contacts/" + id, {
        method: "DELETE",
    });

    await loadContacts(contactsCurrentPage);
}

// =========================
// EVENTS
// =========================

document.addEventListener("DOMContentLoaded", () => {
    loadContacts();

    const btnImport = document.getElementById("btnImport");
    const btnExcel = document.getElementById("btnImportExcel");
    const btnCreate = document.getElementById("btnCreateContact");
    const btnSaveEdit = document.getElementById("btnSaveEditContact");
    const btnCancelEdit = document.getElementById("btnCancelEditContact");

    if (btnCreate) {
        btnCreate.onclick = async () => {
            try {
                await createContact();
            } catch (e) {
                console.error(e);
                alert(e.message || "Erro ao cadastrar contato.");
            }
        };
    }

    if (btnSaveEdit) {
        btnSaveEdit.onclick = async () => {
            try {
                await updateContact();
            } catch (e) {
                console.error(e);
                alert(e.message || "Erro ao atualizar contato.");
            }
        };
    }

    if (btnCancelEdit) {
        btnCancelEdit.onclick = closeEditContact;
    }

    if (btnImport) {
        btnImport.onclick = async () => {
            const csv = getValue("csvInput");

            if (!csv) {
                alert("Cole os dados primeiro.");
                return;
            }

            try {
                await importContacts(csv);
                alert("Importação concluída.");
            } catch (e) {
                console.error(e);
                alert("Erro ao importar contatos.");
            }
        };
    }

    if (btnExcel) {
        btnExcel.onclick = async () => {
            try {
                await importExcelContacts();
            } catch (e) {
                console.error(e);
                alert(e.message);
            }
        };
    }

    const filterSearch = document.getElementById("filterSearch");

    if (filterSearch) {
        filterSearch.addEventListener("keyup", (event) => {
            if (event.key === "Enter") {
                applyContactFilters();
            }
        });
    }
});
