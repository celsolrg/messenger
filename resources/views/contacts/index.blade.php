<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contatos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100">

<div class="max-w-7xl mx-auto p-6">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Contatos</h1>
            <p class="text-sm text-slate-500">Gerenciamento e filtros de contatos</p>
        </div>

        <div class="flex gap-2">
            <a href="/"
               class="bg-slate-700 text-white px-4 py-2 rounded-lg hover:bg-slate-800">
                Dashboard
            </a>

            <a href="{{ route('contacts.create') }}"
               class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                Novo
            </a>

            <a href="{{ route('contacts.import.form') }}"
               class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Importar
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">

            <input id="filterSearch"
                   placeholder="Nome, CPF, telefone ou e-mail"
                   class="border rounded-lg px-3 py-2">

            <input id="filterCity"
                   placeholder="Cidade"
                   class="border rounded-lg px-3 py-2">

            <input id="filterState"
                   placeholder="Estado"
                   class="border rounded-lg px-3 py-2">

            <input id="filterTag"
                   placeholder="Tag"
                   class="border rounded-lg px-3 py-2">

            <select id="filterStatus" class="border rounded-lg px-3 py-2">
                <option value="">Todos</option>
                <option value="1">Ativos</option>
                <option value="0">Inativos</option>
            </select>

            <button onclick="loadContactsPage()"
                    class="bg-slate-800 text-white rounded-lg px-4 py-2 hover:bg-slate-900">
                Filtrar
            </button>

        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-800 text-white">
            <tr>
                <th class="p-3 text-left">Nome</th>
                <th class="p-3 text-left">CPF</th>
                <th class="p-3 text-left">Telefone</th>
                <th class="p-3 text-left">Cidade/UF</th>
                <th class="p-3 text-left">Tag</th>
                <th class="p-3 text-center">Ativo</th>
            </tr>
            </thead>

            <tbody id="contactsTable">
            <tr>
                <td colspan="6" class="p-4 text-center text-slate-500">
                    Carregando contatos...
                </td>
            </tr>
            </tbody>
        </table>
    </div>

</div>

<script>
async function loadContactsPage() {
    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "/login";
        return;
    }

    const params = new URLSearchParams();

    const search = document.getElementById("filterSearch").value;
    const cidade = document.getElementById("filterCity").value;
    const estado = document.getElementById("filterState").value;
    const tag = document.getElementById("filterTag").value;
    const ativo = document.getElementById("filterStatus").value;

    if (search) params.append("search", search);
    if (cidade) params.append("cidade", cidade);
    if (estado) params.append("estado", estado);
    if (tag) params.append("tag", tag);
    if (ativo !== "") params.append("ativo", ativo);

    const tbody = document.getElementById("contactsTable");

    try {
        const res = await fetch("/api/contacts?" + params.toString(), {
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
                    <td colspan="6" class="p-4 text-center text-red-600">
                        Erro ao carregar contatos.
                    </td>
                </tr>
            `;
            return;
        }

        const contacts = await res.json();

        if (!contacts.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="p-4 text-center text-slate-500">
                        Nenhum contato encontrado.
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = "";

        contacts.forEach(c => {
            tbody.innerHTML += `
                <tr class="border-b hover:bg-slate-50">
                    <td class="p-3">${c.nome || c.name || "-"}</td>
                    <td class="p-3">${c.cpf || "-"}</td>
                    <td class="p-3">${formatPhone(c)}</td>
                    <td class="p-3">${formatCityState(c)}</td>
                    <td class="p-3">${c.tag || "-"}</td>
                    <td class="p-3 text-center">
                        ${c.ativo == 1
                            ? '<span class="text-green-700 font-semibold">Sim</span>'
                            : '<span class="text-red-700 font-semibold">Não</span>'}
                    </td>
                </tr>
            `;
        });

    } catch (error) {
        tbody.innerHTML = `
            <tr>
                <td colspan="6" class="p-4 text-center text-red-600">
                    Erro de conexão ao carregar contatos.
                </td>
            </tr>
        `;
    }
}

function formatPhone(c) {
    if (c.telefone) {
        return `(${c.ddd || ""}) ${c.telefone}`.trim();
    }

    if (c.phone) {
        return c.phone;
    }

    return "-";
}

function formatCityState(c) {
    const cidade = c.cidade || "-";
    const estado = c.estado || c.uf || "";

    return estado ? `${cidade}/${estado}` : cidade;
}

document.addEventListener("DOMContentLoaded", loadContactsPage);

document.addEventListener("keydown", function(e) {
    if (e.key === "Enter") {
        loadContactsPage();
    }
});
</script>

</body>
</html>
