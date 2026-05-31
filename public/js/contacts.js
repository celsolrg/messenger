let contacts = [];

async function loadContacts() {
    contacts = await api("/contacts");
    renderContacts();
    updateDashboardCards();
}

function renderContacts() {
    const container = document.getElementById("contactsList");
    container.innerHTML = "";

    if (!contacts.length) {
        container.innerHTML = `
            <div class="text-sm text-slate-500 bg-slate-50 border rounded-lg p-4">
                Nenhum contato importado ainda.
            </div>
        `;
        return;
    }

    contacts.forEach(c => {
        const div = document.createElement("div");
        div.className = "flex justify-between items-center border border-slate-200 p-3 rounded-lg bg-slate-50";

        div.innerHTML = `
            <div>
                <strong>${c.name || "-"}</strong><br>
                <small class="text-slate-500">${c.phone}</small>
            </div>

            <button onclick="deleteContact(${c.id})"
                class="text-red-600 hover:text-red-800 font-bold">
                Remover
            </button>
        `;

        container.appendChild(div);
    });
}

async function importContacts(csv) {
    const lines = csv.trim().split("\n");

    for (const line of lines) {
        const [phone, name] = line.split(",");

        if (!phone) continue;

        await api("/contacts", {
            method: "POST",
            body: JSON.stringify({
                name: name?.trim() || null,
                phone: phone.trim(),
                opt_in: true
            })
        });
    }

    document.getElementById("csvInput").value = "";
    await loadContacts();
}

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

    result.innerHTML = "Importando planilha...";

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
        result.innerHTML = `<span class="text-red-600">Erro ao importar planilha.</span>`;
        throw new Error(data.message || "Erro ao importar Excel");
    }

    result.innerHTML = `
        <div class="text-emerald-700">
            ${data.message}<br>
            Criados: <strong>${data.created}</strong> |
            Atualizados: <strong>${data.updated}</strong> |
            Ignorados: <strong>${data.ignored}</strong>
        </div>
    `;

    input.value = "";

    await loadContacts();
}

async function deleteContact(id) {
    await api("/contacts/" + id, {
        method: "DELETE"
    });

    await loadContacts();
}

document.addEventListener("DOMContentLoaded", () => {
    const btn = document.getElementById("btnImport");
    const btnExcel = document.getElementById("btnImportExcel");

    if (btn) {
        btn.onclick = async () => {
            const csv = document.getElementById("csvInput").value;

            if (!csv.trim()) {
                alert("Cole os dados primeiro");
                return;
            }

            await importContacts(csv);
            alert("Importação concluída");
        };
    }

    if (btnExcel) {
        btnExcel.onclick = async () => {
            try {
                await importExcelContacts();
            } catch (e) {
                alert(e.message);
            }
        };
    }
});
