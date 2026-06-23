<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Contatos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 min-h-screen">

<div class="max-w-7xl mx-auto p-6">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Contatos</h1>
            <p class="text-sm text-slate-500">Gerenciamento, filtros e paginação de contatos</p>
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

    <div class="bg-white rounded-xl shadow p-4 mb-4">
        <h2 class="text-lg font-bold text-slate-700 mb-4">Filtros</h2>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <input id="filterSearch" class="border rounded px-3 py-2" placeholder="Nome, CPF, telefone ou e-mail">
            <input id="filterCidade" class="border rounded px-3 py-2" placeholder="Cidade">
            <input id="filterEstado" class="border rounded px-3 py-2 uppercase" maxlength="2" placeholder="UF">
            <input id="filterTag" class="border rounded px-3 py-2" placeholder="Tag">

            <select id="filterOptIn" class="border rounded px-3 py-2">
                <option value="">Opt-in: Todos</option>
                <option value="1">Com opt-in</option>
                <option value="0">Sem opt-in</option>
            </select>

            <select id="filterAtivo" class="border rounded px-3 py-2">
                <option value="">Status: Todos</option>
                <option value="1">Ativos</option>
                <option value="0">Inativos</option>
            </select>

            <select id="filterHasPhone" class="border rounded px-3 py-2">
                <option value="">Telefone: Todos</option>
                <option value="1">Com telefone</option>
                <option value="0">Sem telefone</option>
            </select>

            <select id="filterPerPage" class="border rounded px-3 py-2">
                <option value="25">25 por página</option>
                <option value="50" selected>50 por página</option>
                <option value="100">100 por página</option>
                <option value="200">200 por página</option>
            </select>
        </div>

        <div class="flex gap-2 mt-4">
            <button onclick="applyContactFilters()" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                Buscar
            </button>

            <button onclick="clearContactFilters()" class="bg-slate-200 text-slate-700 px-4 py-2 rounded hover:bg-slate-300">
                Limpar
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-4">
        <div class="flex justify-between items-center mb-3">
            <h2 class="text-lg font-bold text-slate-700">Lista de contatos</h2>
            <div id="contactsPaginationInfo" class="text-sm text-slate-500"></div>
        </div>

        <div id="contactsList" class="space-y-3">
            <div class="text-sm text-slate-500 bg-slate-50 border rounded-lg p-4">
                Carregando contatos...
            </div>
        </div>

        <div id="contactsPaginationControls" class="mt-4"></div>
    </div>

</div>

<div id="editContactModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-3xl">
        <div class="flex justify-between items-center border-b p-5">
            <h2 class="text-xl font-bold text-slate-800">Editar contato</h2>

            <button onclick="closeEditContact()" class="text-slate-500 hover:text-slate-800 text-2xl">
                &times;
            </button>
        </div>

        <div class="p-5">
            <input type="hidden" id="editContactId">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm text-slate-600">Nome</label>
                    <input id="editContactNome" class="w-full border rounded-lg px-3 py-2">
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
                    <input id="editContactTelefone" class="w-full border rounded-lg px-3 py-2">
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
                    <input id="editContactEstado" maxlength="2" class="w-full border rounded-lg px-3 py-2 uppercase">
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
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button onclick="closeEditContact()" class="px-4 py-2 rounded-lg bg-slate-200 hover:bg-slate-300">
                    Cancelar
                </button>

                <button onclick="updateContact()" class="px-4 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700">
                    Salvar alterações
                </button>
            </div>
        </div>
    </div>
</div>

<script>
async function api(path, options = {}) {
    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "/login";
        return;
    }

    const headers = {
        "Authorization": "Bearer " + token,
        "Accept": "application/json",
        ...(options.body instanceof FormData ? {} : {"Content-Type": "application/json"}),
        ...(options.headers || {})
    };

    const response = await fetch("/api" + path, {
        ...options,
        headers
    });

    if (response.status === 401) {
        localStorage.removeItem("token");
        window.location.href = "/login";
        return;
    }

    const data = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(data.message || "Erro na requisição.");
    }

    return data;
}
</script>

<script src="/js/contacts.js"></script>

</body>
</html>