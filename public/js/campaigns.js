const mediaLabels = {
    image: "Selecione uma imagem",
    video: "Selecione um vídeo",
    audio: "Selecione um áudio",
    document: "Selecione um documento",
};

const campaignTypeNames = {
    text: "Texto",
    image: "Imagem",
    video: "Vídeo",
    audio: "Áudio",
    document: "Documento",
};

function updateCampaignMediaInput() {
    const type = document.getElementById("campaignType").value;
    const mediaBox = document.getElementById("campaignMediaBox");
    const mediaLabel = document.getElementById("campaignMediaLabel");
    const mediaInput = document.getElementById("campaignMedia");

    if (type === "text") {
        mediaBox.classList.add("hidden");
        mediaInput.value = "";
        return;
    }

    mediaBox.classList.remove("hidden");
    mediaLabel.innerText = mediaLabels[type] || "Selecione um arquivo";
}

async function loadCampaigns() {
    const container = document.getElementById("campaignsList");

    if (!container) {
        return;
    }

    container.innerHTML = `
        <div class="text-sm text-slate-500 bg-slate-50 border rounded-lg p-3">
            Carregando campanhas...
        </div>
    `;

    try {
        const res = await fetch("/api/campaigns", {
            headers: {
                "Authorization": `Bearer ${localStorage.getItem("token")}`,
                "Accept": "application/json",
            },
        });

        const campaigns = await res.json();

        if (!res.ok) {
            container.innerHTML = `
                <div class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-3">
                    Erro ao carregar campanhas.
                </div>
            `;
            return;
        }

        renderCampaigns(campaigns);

    } catch (error) {
        container.innerHTML = `
            <div class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg p-3">
                Erro de conexão ao carregar campanhas.
            </div>
        `;
    }
}

function renderCampaigns(campaigns) {
    const container = document.getElementById("campaignsList");
    container.innerHTML = "";

    if (!campaigns.length) {
        container.innerHTML = `
            <div class="text-sm text-slate-500 bg-slate-50 border rounded-lg p-3">
                Nenhuma campanha criada ainda.
            </div>
        `;
        return;
    }

    campaigns.forEach(campaign => {
        const media = campaign.media && campaign.media.length
            ? campaign.media[0]
            : null;

        const mediaHtml = media
            ? `
                <div class="text-xs text-slate-500 mt-2">
                    Anexo:
                    <a href="/storage/${media.file_path}" target="_blank"
                       class="text-blue-600 hover:underline">
                        ${media.file_name}
                    </a>
                </div>
            `
            : "";

        const div = document.createElement("div");
        div.className = "border border-slate-200 rounded-lg p-4 bg-slate-50 flex justify-between gap-4 items-start";

        div.innerHTML = `
            <div>
                <div class="font-semibold text-slate-800">
                    ${campaign.name || "-"}
                </div>

                <div class="text-xs text-slate-500 mt-1">
                    Tipo: ${campaignTypeNames[campaign.type] || campaign.type || "-"}
                </div>

                <div class="text-sm text-slate-600 mt-2">
                    ${campaign.message || ""}
                </div>

                ${mediaHtml}
            </div>

            <button onclick="sendCampaign(${campaign.id})"
                class="bg-green-600 hover:bg-green-700 text-white text-sm px-3 py-2 rounded-lg font-semibold">
                Enviar
            </button>
        `;

        container.appendChild(div);
    });
}

async function createCampaign() {
    const formData = new FormData();

    const name = document.getElementById("campaignName").value.trim();
    const type = document.getElementById("campaignType").value;
    const message = document.getElementById("campaignMessage").value.trim();
    const mediaInput = document.getElementById("campaignMedia");

    if (!name) {
        alert("Informe o nome da campanha.");
        return;
    }

    if (!message && type === "text") {
        alert("Informe a mensagem da campanha.");
        return;
    }

    formData.append("name", name);
    formData.append("type", type);
    formData.append("message", message);

    if (type !== "text") {
        if (!mediaInput.files.length) {
            alert(mediaLabels[type] || "Selecione um arquivo.");
            return;
        }

        formData.append("media", mediaInput.files[0]);
    }

    const res = await fetch("/api/campaigns", {
        method: "POST",
        headers: {
            "Authorization": `Bearer ${localStorage.getItem("token")}`,
            "Accept": "application/json",
        },
        body: formData,
    });

    const data = await res.json();

    if (!res.ok) {
        alert(data.message || "Erro ao criar campanha.");
        return;
    }

    alert("Campanha criada com sucesso.");

    document.getElementById("campaignName").value = "";
    document.getElementById("campaignMessage").value = "";
    document.getElementById("campaignType").value = "text";
    document.getElementById("campaignMedia").value = "";

    updateCampaignMediaInput();
    loadCampaigns();
}

async function sendCampaign(id) {
    if (!confirm("Deseja enviar esta campanha para os contatos opt-in?")) {
        return;
    }

    const res = await fetch(`/api/campaigns/${id}/send`, {
        method: "POST",
        headers: {
            "Authorization": `Bearer ${localStorage.getItem("token")}`,
            "Accept": "application/json",
        },
    });

    const data = await res.json();

    if (!res.ok) {
        alert(data.message || "Erro ao enviar campanha.");
        return;
    }

    alert(data.message || "Campanha enviada para fila.");
}
