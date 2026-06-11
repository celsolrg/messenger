<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Novo Contato</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 p-6">

<div class="max-w-3xl mx-auto bg-white rounded-xl shadow p-6">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Novo Contato</h1>
            <p class="text-sm text-slate-500">Cadastro manual via API</p>
        </div>

        <a href="/contatos" class="bg-slate-700 text-white px-4 py-2 rounded-lg">
            Voltar
        </a>
    </div>

    <div id="result" class="hidden mb-4 p-3 rounded"></div>

    <form id="contactForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <input id="name" placeholder="Nome" class="border p-2 rounded md:col-span-2" required>
        <input id="phone" placeholder="Telefone" class="border p-2 rounded" required>
        <input id="email" placeholder="E-mail" class="border p-2 rounded">
        <input id="cpf" placeholder="CPF" class="border p-2 rounded">
        <input id="cidade" placeholder="Cidade" class="border p-2 rounded">
        <input id="estado" placeholder="UF" maxlength="2" class="border p-2 rounded">
        <input id="tag" placeholder="Tag" class="border p-2 rounded">

        <div class="md:col-span-2 flex gap-2">
            <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded">
                Salvar
            </button>

            <a href="/contatos" class="bg-gray-500 text-white px-4 py-2 rounded">
                Cancelar
            </a>
        </div>
    </form>

</div>

<script>
document.getElementById("contactForm").addEventListener("submit", async function(e) {
    e.preventDefault();

    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "/login";
        return;
    }

    const payload = {
        name: document.getElementById("name").value.trim(),
        phone: document.getElementById("phone").value.trim(),
        email: document.getElementById("email").value.trim(),
        cpf: document.getElementById("cpf").value.trim(),
        cidade: document.getElementById("cidade").value.trim(),
        estado: document.getElementById("estado").value.trim(),
        tag: document.getElementById("tag").value.trim(),
        opt_in: true,
        ativo: true
    };

    const result = document.getElementById("result");

    try {
        const res = await fetch("/api/contacts", {
            method: "POST",
            headers: {
                "Authorization": "Bearer " + token,
                "Accept": "application/json",
                "Content-Type": "application/json"
            },
            body: JSON.stringify(payload)
        });

        const text = await res.text();

        if (res.status === 401) {
            localStorage.removeItem("token");
            window.location.href = "/login";
            return;
        }

        if (!res.ok) {
            result.className = "mb-4 p-3 rounded bg-red-100 text-red-700";
            result.innerHTML = "Erro ao salvar contato:<br>" + text;
            return;
        }

        result.className = "mb-4 p-3 rounded bg-green-100 text-green-700";
        result.innerHTML = "Contato salvo com sucesso.";

        setTimeout(() => {
            window.location.href = "/contatos";
        }, 800);

    } catch (error) {
        result.className = "mb-4 p-3 rounded bg-red-100 text-red-700";
        result.innerHTML = "Erro de conexão.";
    }
});
</script>

</body>
</html>
