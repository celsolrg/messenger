<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Importar Contatos</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-3xl mx-auto bg-white rounded shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Importar Contatos</h1>

    @if(session('success'))
        <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('contacts.import') }}" enctype="multipart/form-data">
        @csrf
        <input type="file" name="arquivo" accept=".xlsx,.xls,.csv,.txt" class="border p-2 rounded w-full mb-4" required>
        <div class="flex gap-2">
            <button class="bg-blue-600 text-white px-4 py-2 rounded">Importar</button>
            <a href="{{ route('contacts.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded">Voltar</a>
        </div>
    </form>
</div>
</body>
</html>
