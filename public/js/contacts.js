let contacts = [];

// =========================
// HELPERS
// =========================

function getValue(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : "";
}

function clearFields(ids) {
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = "";
    });
}

function getContactName(c) {
    return c.nome || c.name || "-";
}

function getContactPhone(c) {
    return c.telefone || c.phone || "-";
}

// =========================
// LOAD / RENDER
// =========================

async function loadContacts() {
    contacts = await api("/contacts");

    renderContacts();

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
                Nenhum contato cadastrado ainda.
            </div>
        `;
        return;
    }

    contacts.forEach(c => {
        const div = document.createElement("div");

        div.className = "flex justify-between items-center border border-slate-200 p-3 rounded-lg bg-slate-50";

        div.innerHTML = `
            <div>
                <strong>${getContactName(c)}</strong><br>
                <small class="text-slate-500">${getContactPhone(c)}</small>
            </div>

            <button onclick="deleteContact(${c.id})"
                class="text-red-600 hover:text-red-800 font-bold">
                Remover
            </button>
        `;

        container.appendChild(div);
    });
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
        estado: getValue("contactEstado"),
        tag: getValue("contactTag"),
        opt_in: true,
        ativo: true
    };

    if (!payload.name || !payload.phone) {
        alert("Informe pelo menos nome e telefone.");
        return;
    }

    await api("/contacts", {
        method: "POST",
        body: JSON.stringify(payload)
    });

    clearFields([
        "contactNome",
        "contactTelefone",
        "contactEmail",
        "contactCpf",
        "contactCidade",
        "contactEstado",
        "contactTag"
    ]);

    await loadContacts();

    alert("Contato cadastrado com sucesso!");
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
                ativo: true
            })
        });
    }

    clearFields(["csvInput"]);

    await loadContacts();
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
            "Authorization": "Bearer " + token,
            "Accept": "application/json"
        },
        body: formData
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
                ${data.message}<br>
                Criados: <strong>${data.created}</strong> |
                Atualizados: <strong>${data.updated}</strong> |
                Ignorados: <strong>${data.ignored}</strong>
            </div>
        `;
    }

    input.value = "";

    await loadContacts();
}

// =========================
// DELETE
// =========================

async function deleteContact(id) {
    if (!confirm("Deseja remover este contato?")) return;

    await api("/contacts/" + id, {
        method: "DELETE"
    });

    await loadContacts();
}

// =========================
// EVENTS
// =========================

document.addEventListener("DOMContentLoaded", () => {
    const btnImport = document.getElementById("btnImport");
    const btnExcel = document.getElementById("btnImportExcel");
    const btnCreate = document.getElementById("btnCreateContact");

    if (btnCreate) {
        btnCreate.onclick = async () => {
            try {
                await createContact();
            } catch (e) {
                console.error(e);
                alert("Erro ao cadastrar contato.");
            }
        };
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
});
