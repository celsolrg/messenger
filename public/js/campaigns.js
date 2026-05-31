let campaigns = [];

async function loadCampaigns() {
    campaigns = await api("/campaigns");
    renderCampaigns();
    updateDashboardCards();
}

function renderCampaigns() {
    const container = document.getElementById("campaignsList");
    container.innerHTML = "";

    if (!campaigns.length) {
        container.innerHTML = `
            <div class="text-sm text-slate-500 bg-slate-50 border rounded-lg p-4">
                Nenhuma campanha criada ainda.
            </div>
        `;
        return;
    }

    campaigns.forEach(c => {
        const div = document.createElement("div");
        div.className = "border border-slate-200 p-4 rounded-lg flex flex-col md:flex-row md:justify-between md:items-center gap-3 bg-slate-50";

        div.innerHTML = `
            <div>
                <strong class="text-slate-900">${c.name}</strong><br>
                <small class="text-slate-500">${c.message || ''}</small>
            </div>

            <button onclick="sendCampaign(${c.id})"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                Disparar
            </button>
        `;

        container.appendChild(div);
    });
}

async function createCampaign() {
    const nameInput = document.getElementById("campaignName");
    const messageInput = document.getElementById("campaignMessage");

    const name = nameInput.value.trim();
    const message = messageInput.value.trim();

    if (!name) {
        alert("Nome obrigatório");
        return;
    }

    await api("/campaigns", {
        method: "POST",
        body: JSON.stringify({
            name,
            message,
            type: "text"
        })
    });

    nameInput.value = "";
    messageInput.value = "";

    await loadCampaigns();
}

async function sendCampaign(id) {
    await api(`/campaigns/${id}/send`, {
        method: "POST"
    });

    alert("Campanha enviada para fila");

    await loadCampaigns();
}
