<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Novo Contato</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-3xl mx-auto bg-white rounded shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Novo Contato</h1>

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('contacts.store') }}" class="grid grid-cols-2 gap-4">
        @csrf
        <input name="nome" placeholder="Nome" class="border p-2 rounded col-span-2" required>
        <input name="cpf" placeholder="CPF" class="border p-2 rounded">
        <input name="ddd" placeholder="DDD" class="border p-2 rounded">
        <input name="telefone" placeholder="Telefone" class="border p-2 rounded">
        <input name="cidade" placeholder="Cidade" class="border p-2 rounded">
        <input name="uf" placeholder="UF" maxlength="2" class="border p-2 rounded">
        <input name="bairro" placeholder="Bairro" class="border p-2 rounded">
        <input name="cep" placeholder="CEP" class="border p-2 rounded">
        <div class="col-span-2 flex gap-2">
            <button class="bg-green-600 text-white px-4 py-2 rounded">Salvar</button>
            <a href="{{ route('contacts.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Voltar</a>
        </div>
    </form>
</div>
</body>
</html>
