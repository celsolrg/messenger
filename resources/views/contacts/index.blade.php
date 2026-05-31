<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Contatos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-7xl mx-auto bg-white rounded shadow p-6">
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Contatos</h1>
        <div class="flex gap-2">
            <a href="{{ route('contacts.create') }}" class="bg-green-600 text-white px-4 py-2 rounded">Novo</a>
            <a href="{{ route('contacts.import.form') }}" class="bg-blue-600 text-white px-4 py-2 rounded">Importar</a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    <form method="GET" class="mb-4 flex gap-2">
        <input name="search" value="{{ request('search') }}" placeholder="Buscar por nome, CPF, telefone ou cidade" class="border p-2 rounded w-full">
        <button class="bg-gray-800 text-white px-4 py-2 rounded">Buscar</button>
    </form>

    <div class="overflow-x-auto">
        <table class="w-full text-sm border">
            <thead class="bg-gray-200">
            <tr>
                <th class="p-2 border text-left">Nome</th>
                <th class="p-2 border">CPF</th>
                <th class="p-2 border">Telefone</th>
                <th class="p-2 border">Cidade/UF</th>
                <th class="p-2 border">Ativo</th>
            </tr>
            </thead>
            <tbody>
            @forelse($contacts as $contact)
                <tr>
                    <td class="p-2 border">{{ $contact->nome }}</td>
                    <td class="p-2 border">{{ $contact->cpf }}</td>
                    <td class="p-2 border">({{ $contact->ddd }}) {{ $contact->telefone }}</td>
                    <td class="p-2 border">{{ $contact->cidade }}/{{ $contact->uf }}</td>
                    <td class="p-2 border text-center">{{ $contact->ativo ? 'Sim' : 'Não' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="p-4 text-center">Nenhum contato encontrado.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $contacts->links() }}</div>
</div>
</body>
</html>
