<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Campanhas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100">

<div class="max-w-7xl mx-auto p-6">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Campanhas</h1>
            <p class="text-sm text-slate-500">Criar, visualizar e excluir campanhas</p>
        </div>

        <a href="/" class="bg-slate-700 text-white px-4 py-2 rounded-lg hover:bg-slate-800">
            Dashboard
        </a>
    </div>

    <div class="bg-white rounded-xl shadow p-5 mb-6">
        <h2 class="text-lg font-bold mb-4">Nova campanha</h2>

        <input id="campaignName"
               placeholder="Nome da campanha"
               class="border rounded-lg p-3 w-full mb-3">

        <select id="campaignType"
                onchange="toggleMediaInput()"
                class="border rounded-lg p-3 w-full mb-3">
            <option value="text">Somente texto</option>
            <option value="image">Texto + imagem</option>
            <option value="video">Texto + vídeo</option>
            <option value="audio">Texto + áudio</option>
            <option value="document">Texto + documento</option>
        </select>

        <div id="mediaBox" class="hidden mb-3">
            <label class="block text-sm text-slate-600 mb-1">
                Arquivo da campanha
            </label>

            <input id="campaignMedia"
                   type="file"
                   class="border rounded-lg p-3 w-full bg-white">
        </div>

        <textarea id="campaignMessage"
                  placeholder="Mensagem. Exemplo: Olá @{{name}}, tudo bem?"
                  class="border rounded-lg p-3 w-full mb-3"></textarea>

        <button onclick="createCampaignPage()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold">
            Criar campanha
        </button>

        <div id="campaignResult" class="mt-3 text-sm"></div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-800 text-white">
            <tr>
                <th class="p-3 text-left">Nome</th>
                <th class="p-3 text-left">Tipo</th>
                <th class="p-3 text-left">Mensagem</th>
                <th class="p-3 text-left">Arquivo</th>
                <th class="p-3 text-center">Ações</th>
            </tr>
            </thead>

            <tbody id="campaignsTable">
            <tr>
                <td colspan="5" class="p-4 text-center text-slate-500">
                    Carregando campanhas...
                </td>
            </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
function toggleMediaInput() {
    const type = document.getElementById("campaignType").value;
    const mediaBox = document.getElementById("mediaBox");
    const mediaInput = document.getElementById("campaignMedia");

    if (type === "text") {
        mediaBox.classList.add("hidden");
        mediaInput.value = "";
        mediaInput.removeAttribute("accept");
        return;
    }

    mediaBox.classList.remove("hidden");

    if (type === "image") {
        mediaInput.setAttribute("accept", "image/*");
    } else if (type === "video") {
        mediaInput.setAttribute("accept", "video/*");
    } else if (type === "audio") {
        mediaInput.setAttribute("accept", "audio/*");
    } else if (type === "document") {
        mediaInput.setAttribute("accept", ".pdf,.doc,.docx,.xls,.xlsx,.txt,.csv");
    }
}

async function loadCampaignsPage() {
    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "/login";
        return;
    }

    const tbody = document.getElementById("campaignsTable");

    const res = await fetch("/api/campaigns", {
        headers: {
            "Authorization": "Bearer " + token,
            "Accept": "application/json"
        }
    });

    if (res.status === 401) {
        localStorage.removeItem("token");
        window.location.href = "/login";
        return;
    }

    if (!res.ok) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="p-4 text-center text-red-600">
                    Erro ao carregar campanhas.
                </td>
            </tr>
        `;
        return;
    }

    const campaigns = await res.json();

    if (!campaigns.length) {
        tbody.innerHTML = `
            <tr>
                <td colspan="5" class="p-4 text-center text-slate-500">
                    Nenhuma campanha encontrada.
                </td>
            </tr>
        `;
        return;
    }

    tbody.innerHTML = "";

    campaigns.forEach(c => {
        const media = c.media && c.media.length > 0 ? c.media[0] : null;

        tbody.innerHTML += `
            <tr class="border-b hover:bg-slate-50">
                <td class="p-3 font-semibold">${c.name || "-"}</td>
                <td class="p-3">${formatCampaignType(c.type)}</td>
                <td class="p-3">${c.message || "-"}</td>
                <td class="p-3">${media ? media.file_name : "-"}</td>
                <td class="p-3 text-center">



		<div class="flex gap-2 justify-center">
		    <button onclick='editCampaignPage(${JSON.stringify(c)})'
		            class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded">
		        Editar
		    </button>

		    <button onclick="copyCampaignPage(${c.id})"
		            class="bg-slate-600 hover:bg-slate-700 text-white px-3 py-1 rounded">
		        Copiar
		    </button>

		    <button onclick="deleteCampaignPage(${c.id})"
		            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded">
		        Excluir
		    </button>
		</div>
                </td>
            </tr>
        `;
    });
}

async function createCampaignPage() {
    const token = localStorage.getItem("token");

    const name = document.getElementById("campaignName").value;
    const type = document.getElementById("campaignType").value;
    const message = document.getElementById("campaignMessage").value;
    const media = document.getElementById("campaignMedia").files[0];
    const result = document.getElementById("campaignResult");

    if (!name) {
        result.innerHTML = `<span class="text-red-600">Informe o nome da campanha.</span>`;
        return;
    }

    if (type !== "text" && !media) {
        result.innerHTML = `<span class="text-red-600">Selecione o arquivo da campanha.</span>`;
        return;
    }

    const formData = new FormData();
    formData.append("name", name);
    formData.append("type", type);
    formData.append("message", message);

    if (media) {
        formData.append("media", media);
    }

    const res = await fetch("/api/campaigns", {
        method: "POST",
        headers: {
            "Authorization": "Bearer " + token,
            "Accept": "application/json"
        },
        body: formData
    });

     if (!res.ok) {
        let errorMessage = "Erro ao criar campanha.";

        try {
            const errorJson = await res.json();

            if (errorJson.message) {
                 errorMessage = errorJson.message;
            }

            if (errorJson.errors) {
                const firstError = Object.values(errorJson.errors)[0];
                if (firstError && firstError[0]) {
                    errorMessage = firstError[0];
                }
            }
        } catch (e) {
            errorMessage = await res.text();
       }

        result.innerHTML = `<span class="text-red-600">${errorMessage}</span>`;
        return;
    }

    document.getElementById("campaignName").value = "";
    document.getElementById("campaignMessage").value = "";
    document.getElementById("campaignType").value = "text";
    document.getElementById("campaignMedia").value = "";
    toggleMediaInput();

    result.innerHTML = `<span class="text-green-700">Campanha criada com sucesso.</span>`;

    loadCampaignsPage();
}

async function deleteCampaignPage(id) {
    if (!confirm("Deseja excluir esta campanha?")) {
        return;
    }

    const token = localStorage.getItem("token");

    const res = await fetch(`/api/campaigns/${id}`, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + token,
            "Accept": "application/json"
        }
    });

    if (!res.ok) {
        alert("Erro ao excluir campanha.");
        return;
    }

    loadCampaignsPage();
}

function formatCampaignType(type) {
    const types = {
        text: "Texto",
        image: "Texto + imagem",
        video: "Texto + vídeo",
        audio: "Texto + áudio",
        document: "Texto + documento"
    };

    return types[type] || type || "-";
}

document.addEventListener("DOMContentLoaded", function () {
    toggleMediaInput();
    loadCampaignsPage();
});
</script>

</body>
</html>
