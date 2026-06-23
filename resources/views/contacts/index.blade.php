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
            <a href="/" class="bg-slate-700 text-white px-4 py-2 rounded-lg hover:bg-slate-800">
                Dashboard
            </a>

            <a href="{{ route('contacts.create') }}" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                Novo
            </a>

            <a href="{{ route('contacts.import.form') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Importar
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3">
            <input id="filterSearch" placeholder="Nome, CPF, telefone ou e-mail" class="border rounded-lg px-3 py-2">
            <input id="filterCity" placeholder="Cidade" class="border rounded-lg px-3 py-2">
            <input id="filterState" placeholder="Estado" class="border rounded-lg px-3 py-2">
            <input id="filterTag" placeholder="Tag" class="border rounded-lg px-3 py-2">

            <select id="filterStatus" class="border rounded-lg px-3 py-2">
                <option value="">Todos</option>
                <option value="1">Ativos</option>
                <option value="0">Inativos</option>
            </select>

            <button onclick="loadContactsPage()" class="bg-slate-800 text-white rounded-lg px-4 py-2 hover:bg-slate-900">
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
                <th class="p-3 text-center">Ações</th>
            </tr>
            </thead>
             <tbody id="contactsTable">
            <tr>
                <td colspan="7" class="p-4 text-center text-slate-500">
                    Carregando contatos...
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<div id="editContactModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl">
        <div class="flex justify-between items-center border-b p-5">
            <h2 class="text-xl font-bold text-slate-800">Editar contato</h2>

            <button onclick="closeEditContactModal()" class="text-slate-500 hover:text-slate-800 text-2xl">
                &times;
            </button>
        </div>

        <div class="p-5">
            <input type="hidden" id="editContactId">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-slate-600">Nome</label>
                    <input id="editContactName" class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="text-sm text-slate-600">CPF</label>
                    <input id="editContactCpf" class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="text-sm text-slate-600">DDD</label>
                    <input id="editContactDdd" class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="text-sm text-slate-600">Telefone</label>
                    <input id="editContactPhone" class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="text-sm text-slate-600">E-mail</label>
                    <input id="editContactEmail" class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="text-sm text-slate-600">Cidade</label>
                    <input id="editContactCidade" class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="text-sm text-slate-600">UF</label>
                    <input id="editContactUf" maxlength="2" class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="text-sm text-slate-600">Bairro</label>
                    <input id="editContactBairro" class="w-full border rounded-lg px-3 py-2">
                </div>

                <div class="md:col-span-2">
                    <label class="text-sm text-slate-600">Endereço</label>
                    <input id="editContactAddress" class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="text-sm text-slate-600">CEP</label>
                    <input id="editContactCep" class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="text-sm text-slate-600">Tag</label>
                    <input id="editContactTag" class="w-full border rounded-lg px-3 py-2">
                </div>

                <div>
                    <label class="text-sm text-slate-600">Ativo</label>
                    <select id="editContactAtivo" class="w-full border rounded-lg px-3 py-2">
                        <option value="1">Sim</option>
                        <option value="0">Não</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button onclick="closeEditContactModal()" class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300">
                    Cancelar
                </button>

                <button onclick="saveContactEdit()" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                    Salvar alterações
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let contactsPageData = [];

function getToken() {
    return localStorage.getItem("token");
}

function getPrimaryPhone(c) {
    if (c.phones && c.phones.length) {
        const principal = c.phones.find(p => Number(p.principal) === 1);
        return principal || c.phones[0];
    }

    return null;
}

function value(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : "";
}

function setValue(id, val) {
    const el = document.getElementById(id);
    if (el) el.value = val ?? "";
}

async function loadContactsPage() {
    const token = getToken();

    if (!token) {
        window.location.href = "/login";
        return;
    }

    const params = new URLSearchParams();

    const search = value("filterSearch");
    const cidade = value("filterCity");
    const estado = value("filterState");
    const tag = value("filterTag");
    const ativo = value("filterStatus");

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
            tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-center text-red-600">Erro ao carregar contatos.</td></tr>`;
            return;
        }

        contactsPageData = await res.json();

        if (!contactsPageData.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-center text-slate-500">Nenhum contato encontrado.</td></tr>`;
            return;
        }

        tbody.innerHTML = "";

        contactsPageData.forEach(c => {
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

                    <td class="p-3 text-center">
                        <div class="flex justify-center gap-2">
                            <button onclick="openEditContactModal(${c.id})"
                                class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700">
                                Editar
                            </button>

                            <button onclick="deleteContact(${c.id})"
                                class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">
                                Excluir
                            </button>
                        </div>
                    </td>
                </tr>
            `;
        });

    } catch (error) {
        tbody.innerHTML = `<tr><td colspan="7" class="p-4 text-center text-red-600">Erro de conexão ao carregar contatos.</td></tr>`;
    }
}

function formatPhone(c) {
    const p = getPrimaryPhone(c);

    if (p) {
        return `(${p.ddd || ""}) ${p.telefone || ""}`.trim();
    }

    if (c.phone) return c.phone;
    if (c.telefone) return c.telefone;

    return "-";
}

function formatCityState(c) {
    const cidade = c.cidade || "-";
    const estado = c.estado || c.uf || "";

    return estado ? `${cidade}/${estado}` : cidade;
}

function openEditContactModal(id) {
    const c = contactsPageData.find(item => item.id === id);

    if (!c) {
        alert("Contato não encontrado.");
        return;
    }

    const p = getPrimaryPhone(c);

    setValue("editContactId", c.id);
    setValue("editContactName", c.nome || c.name || "");
    setValue("editContactCpf", c.cpf || "");
    setValue("editContactDdd", p?.ddd || c.ddd || "");
    setValue("editContactPhone", p?.telefone || c.phone || c.telefone || "");
    setValue("editContactEmail", c.email || "");
    setValue("editContactCidade", c.cidade || "");
    setValue("editContactUf", c.uf || c.estado || "");
    setValue("editContactBairro", c.bairro || "");
    setValue("editContactAddress", c.address || "");
    setValue("editContactCep", c.cep || "");
    setValue("editContactTag", c.tag || "");
    setValue("editContactAtivo", c.ativo == 1 ? "1" : "0");

    document.getElementById("editContactModal").classList.remove("hidden");
}

function closeEditContactModal() {
    document.getElementById("editContactModal").classList.add("hidden");
}

async function saveContactEdit() {
    const token = getToken();
    const id = value("editContactId");

    const payload = {
    name: value("editContactName"),
    cpf: value("editContactCpf"),
    ddd: value("editContactDdd"),
    phone: value("editContactPhone"),
    email: value("editContactEmail"),
    cidade: value("editContactCidade"),
    uf: value("editContactUf"),
    bairro: value("editContactBairro"),
    address: value("editContactAddress"),
    cep: value("editContactCep"),
    tag: value("editContactTag"),
    ativo: value("editContactAtivo") === "1" ? true : false
};

    const res = await fetch("/api/contacts/" + id, {
        method: "PUT",
        headers: {
            "Authorization": "Bearer " + token,
            "Accept": "application/json",
            "Content-Type": "application/json"
        },
        body: JSON.stringify(payload)
    });

    const data = await res.json();

    if (!res.ok) {
        alert(data.message || "Erro ao atualizar contato.");
        return;
    }

    closeEditContactModal();
    await loadContactsPage();

    alert("Contato atualizado com sucesso.");
}

    async function deleteContact(id) {
        if (!confirm("Deseja realmente excluir este contato?")) {
            return;
        }

        const token = getToken();

        const res = await fetch("/api/contacts/" + id, {
            method: "DELETE",
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
            alert("Erro ao excluir contato.");
            return;
        }

        await loadContactsPage();

        alert("Contato excluído com sucesso.");
    }

document.addEventListener("DOMContentLoaded", loadContactsPage);

document.addEventListener("keydown", function(e) {
    if (e.key === "Enter") {
        loadContactsPage();
    }

    if (e.key === "Escape") {
        closeEditContactModal();
    }
});
</script>

</body>
</html>
