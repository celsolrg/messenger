<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - WhatsApp System</title>

    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 text-slate-900" style="display:none;">

<div class="min-h-screen flex">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-slate-900 text-white hidden md:flex flex-col">
        <div class="p-6 border-b border-slate-700">
            <h1 class="text-xl font-bold">WhatsApp System</h1>
            <p class="text-xs text-slate-400 mt-1">Painel de campanhas</p>
        </div>

        <nav class="flex-1 p-4 space-y-2">
            <a href="#" class="block px-4 py-3 rounded bg-blue-600 font-semibold">Dashboard</a>
            <a href="#contatos" class="block px-4 py-3 rounded hover:bg-slate-800">Contatos</a>
            <a href="#campanhas" class="block px-4 py-3 rounded hover:bg-slate-800">Campanhas</a>
            <a href="#mensagens" class="block px-4 py-3 rounded hover:bg-slate-800">Fila de Envios</a>
            <a href="#whatsapp" class="block px-4 py-3 rounded hover:bg-slate-800">WhatsApp</a>
        </nav>

        <div class="p-4 border-t border-slate-700">
            <button onclick="logout()" class="w-full bg-red-600 hover:bg-red-700 px-4 py-2 rounded">
                Sair
            </button>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 p-6">

        <!-- HEADER -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold">Dashboard</h2>
                <p class="text-sm text-slate-500">Gerenciamento de contatos, campanhas e envios</p>
            </div>

            <button onclick="logout()" class="md:hidden text-red-600 font-semibold">
                Sair
            </button>
        </div>

        <!-- CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white p-5 rounded-xl shadow-sm border">
                <p class="text-sm text-slate-500">Contatos</p>
                <h3 id="cardContacts" class="text-3xl font-bold mt-2">-</h3>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border">
                <p class="text-sm text-slate-500">Campanhas</p>
                <h3 id="cardCampaigns" class="text-3xl font-bold mt-2">-</h3>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border">
                <p class="text-sm text-slate-500">Enviadas</p>
                <h3 id="cardSent" class="text-3xl font-bold mt-2 text-green-600">-</h3>
            </div>

            <div class="bg-white p-5 rounded-xl shadow-sm border">
                <p class="text-sm text-slate-500">Pendentes</p>
                <h3 id="cardPending" class="text-3xl font-bold mt-2 text-yellow-600">-</h3>
            </div>
        </div>

        <!-- CONTATOS -->
	<section id="contatos" class="bg-white p-6 rounded-xl shadow-sm border mb-6">
	    <div class="flex justify-between items-center mb-4">
	        <div>
	            <h2 class="text-lg font-bold">Contatos</h2>
	            <p class="text-sm text-slate-500">Importe contatos via texto ou planilha Excel</p>
	        </div>
	    </div>

	    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
	        <div class="border rounded-xl p-4 bg-slate-50">
	            <h3 class="font-semibold mb-2">Importar por texto</h3>

	            <textarea id="csvInput"
	                placeholder="55999999999,João"
	                class="w-full border border-slate-300 p-3 mb-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"></textarea>

	            <button id="btnImport"
	                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold">
	                Importar texto
	            </button>
	        </div>

	        <div class="border rounded-xl p-4 bg-slate-50">
	            <h3 class="font-semibold mb-2">Importar Excel</h3>

	            <input id="excelFile"
	                type="file"
	                accept=".xlsx,.xls,.csv"
	                class="w-full border border-slate-300 p-3 mb-3 rounded-lg bg-white">

	            <button id="btnImportExcel"
	                class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-lg font-semibold">
	                Importar planilha
	            </button>

		            <div id="excelImportResult" class="mt-3 text-sm text-slate-600"></div>
		        </div>
		    </div>

		    <div id="contactsList" class="mt-5 space-y-2"></div>
		</section>

        <!-- CAMPANHAS -->
        <section id="campanhas" class="bg-white p-6 rounded-xl shadow-sm border mb-6">
            <h2 class="text-lg font-bold mb-1">Campanhas</h2>
            <p class="text-sm text-slate-500 mb-4">Crie campanhas usando variáveis como @{{name}}</p>
            <input id="campaignName"
                placeholder="Nome da campanha"
                class="border border-slate-300 p-3 w-full mb-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none">

            <textarea id="campaignMessage"
                placeholder="Mensagem. Exemplo: Olá @{{name}}, tudo bem?"
                class="border border-slate-300 p-3 w-full mb-3 rounded-lg focus:ring-2 focus:ring-blue-500 outline-none"></textarea>

            <button onclick="createCampaign()"
                class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg font-semibold">
                Criar campanha
            </button>

            <div id="campaignsList" class="mt-5 space-y-2"></div>
        </section>

        <!-- FILA -->
        <section id="mensagens" class="bg-white p-6 rounded-xl shadow-sm border mb-6">
            <h2 class="text-lg font-bold mb-1">Fila de Envios</h2>
            <p class="text-sm text-slate-500 mb-4">Status das mensagens geradas pelas campanhas</p>

            <div id="messagesList" class="text-sm text-slate-500">
                A listagem da fila será conectada no próximo passo.
            </div>
        </section>

        <!-- WHATSAPP -->
        <section id="whatsapp" class="bg-white p-6 rounded-xl shadow-sm border">
            <h2 class="text-lg font-bold mb-1">WhatsApp / Evolution API</h2>
            <p class="text-sm text-slate-500 mb-4">Área reservada para conexão, QR Code e status da instância.</p>

            <div class="p-4 rounded-lg bg-slate-50 border text-sm">
                Status atual: <span class="font-semibold text-yellow-600">não configurado</span>
            </div>
        </section>

    </main>
</div>

<script src="/js/api.js"></script>
<script src="/js/contacts.js"></script>
<script src="/js/app.js"></script>
<script src="/js/campaigns.js"></script>

</body>
</html>
