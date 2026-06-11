<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Importar Contatos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 p-6">

<div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-6">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Importar Contatos</h1>
            <p class="text-sm text-slate-500">Importação via API</p>
        </div>

        <a href="/contatos" class="bg-slate-700 text-white px-4 py-2 rounded-lg">
            Voltar
        </a>
    </div>

    <div id="result" class="hidden mb-4 p-3 rounded"></div>

    <input id="excelFile"
           type="file"
           accept=".xlsx,.xls,.csv"
           class="border p-2 rounded w-full mb-4">

    <button id="btnImport"
            class="bg-blue-600 text-white px-4 py-2 rounded">
        Importar
    </button>

</div>

<script>
document.getElementById("btnImport").addEventListener("click", async function() {
    const token = localStorage.getItem("token");
    const input = document.getElementById("excelFile");
    const result = document.getElementById("result");

    if (!token) {
        window.location.href = "/login";
        return;
    }

    if (!input.files.length) {
        alert("Selecione uma planilha.");
        return;
    }

    const formData = new FormData();
    formData.append("file", input.files[0]);

    result.className = "mb-4 p-3 rounded bg-blue-100 text-blue-700";
    result.innerHTML = "Importando...";

    try {
        const res = await fetch("/api/contacts/import-excel", {
            method: "POST",
            headers: {
                "Authorization": "Bearer " + token,
                "Accept": "application/json"
            },
            body: formData
        });

        const text = await res.text();

        if (res.status === 401) {
            localStorage.removeItem("token");
            window.location.href = "/login";
            return;
        }

        if (!res.ok) {
            result.className = "mb-4 p-3 rounded bg-red-100 text-red-700";
            result.innerHTML = "Erro ao importar:<br>" + text;
            return;
        }

        const data = JSON.parse(text);

        result.className = "mb-4 p-3 rounded bg-green-100 text-green-700";
        result.innerHTML = `
            ${data.message || "Importação concluída."}<br>
            Criados: <strong>${data.created || 0}</strong> |
            Atualizados: <strong>${data.updated || 0}</strong> |
            Ignorados: <strong>${data.ignored || 0}</strong>
        `;

        setTimeout(() => {
            window.location.href = "/contatos";
        }, 1200);

    } catch (error) {
        result.className = "mb-4 p-3 rounded bg-red-100 text-red-700";
        result.innerHTML = "Erro de conexão.";
    }
});
</script>

</body>
</html>
