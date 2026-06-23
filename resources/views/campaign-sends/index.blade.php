<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Envios</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100 min-h-screen">

<div class="max-w-7xl mx-auto p-6">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Envios</h1>
            <p class="text-sm text-slate-500">Iniciar e acompanhar envios de campanhas</p>
        </div>

        <div class="flex gap-2">
            <a href="/" class="bg-slate-700 text-white px-4 py-2 rounded-lg hover:bg-slate-800">Dashboard</a>
            <a href="/campanhas" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Campanhas</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <div class="bg-white rounded-xl shadow p-5 lg:col-span-1">
            <h2 class="text-lg font-bold mb-4">Novo envio</h2>

            <form id="sendForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold mb-1">Campanha</label>
                    <select id="campaign_id" class="w-full border rounded-lg px-3 py-2" required>
                        <option value="">Selecione...</option>
                        @foreach($campaigns as $campaign)
                            <option value="{{ $campaign->id }}">{{ $campaign->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="border rounded-xl p-4 bg-slate-50">
                    <h3 class="text-sm font-bold text-slate-700 mb-3">Público</h3>

                    <div>
                        <label class="block text-sm font-semibold mb-1">Enviar para</label>
                        <select id="target_type" class="w-full border rounded-lg px-3 py-2" onchange="toggleTargetValue()">
                            <option value="all">Todos os contatos ativos</option>
                            <option value="tag">TAG</option>
                            <option value="state">Estado</option>
                            <option value="city">Cidade</option>
                            <option value="ddd">DDD</option>
                        </select>
                    </div>

                    <div id="targetValueBox" class="mt-3 hidden">
                        <label id="targetValueLabel" class="block text-sm font-semibold mb-1">Valor</label>
                        <input id="target_value" type="text" class="w-full border rounded-lg px-3 py-2" placeholder="">
                        <p id="targetValueHelp" class="text-xs text-slate-500 mt-1"></p>
                    </div>
                </div>

                <div class="border rounded-xl p-4 bg-slate-50">
                    <h3 class="text-sm font-bold text-slate-700 mb-3">Controle de velocidade</h3>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Mínimo seg.</label>
                            <input id="min_delay_seconds" type="number" value="20" min="5" class="w-full border rounded-lg px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1">Máximo seg.</label>
                            <input id="max_delay_seconds" type="number" value="60" min="5" class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3 mt-3">
                        <div>
                            <label class="block text-sm font-semibold mb-1">Pausar a cada</label>
                            <input id="pause_every" type="number" value="20" min="1" class="w-full border rounded-lg px-3 py-2">
                        </div>

                        <div>
                            <label class="block text-sm font-semibold mb-1">Pausa seg.</label>
                            <input id="pause_seconds" type="number" value="300" min="0" class="w-full border rounded-lg px-3 py-2">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full bg-green-600 text-white py-3 rounded-lg font-bold hover:bg-green-700">
                    Iniciar envio
                </button>

                <div id="formMsg" class="text-sm"></div>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow p-5 lg:col-span-2">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-lg font-bold">Histórico</h2>
                <button onclick="loadSends()" class="bg-slate-200 px-3 py-2 rounded-lg text-sm hover:bg-slate-300">
                    Atualizar
                </button>
            </div>

            <div id="sendsList" class="space-y-3">
                <p class="text-slate-500 text-sm">Carregando...</p>
            </div>
        </div>

    </div>
</div>

<script>
const token = localStorage.getItem('token');

if (!token) {
    window.location.href = '/login';
}

function toggleTargetValue() {
    const type = document.getElementById('target_type').value;
    const box = document.getElementById('targetValueBox');
    const label = document.getElementById('targetValueLabel');
    const input = document.getElementById('target_value');
    const help = document.getElementById('targetValueHelp');

    input.value = '';

    if (type === 'all') {
        box.classList.add('hidden');
        input.required = false;
        return;
    }

    box.classList.remove('hidden');
    input.required = true;

    if (type === 'tag') {
        label.innerText = 'TAG';
        input.placeholder = 'Ex: clientes, vip, importacao-junho';
        help.innerText = 'Envia apenas para contatos com essa TAG.';
    }

    if (type === 'state') {
        label.innerText = 'Estado / UF';
        input.placeholder = 'Ex: MT, RO, SP';
        help.innerText = 'Use a sigla do estado cadastrada no contato.';
    }

    if (type === 'city') {
        label.innerText = 'Cidade';
        input.placeholder = 'Ex: Cuiabá';
        help.innerText = 'Digite a cidade exatamente como está nos contatos.';
    }

    if (type === 'ddd') {
        label.innerText = 'DDD';
        input.placeholder = 'Ex: 65';
        help.innerText = 'Digite apenas o DDD, sem parênteses.';
    }
}

document.getElementById('sendForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const msg = document.getElementById('formMsg');
    msg.innerHTML = 'Criando envio...';
    msg.className = 'text-sm text-slate-600';

    const targetType = document.getElementById('target_type').value;
    const targetValue = document.getElementById('target_value').value.trim();

    const payload = {
        campaign_id: document.getElementById('campaign_id').value,
        target_type: targetType,
        target_value: targetType === 'all' ? null : targetValue,
        min_delay_seconds: Number(document.getElementById('min_delay_seconds').value || 20),
        max_delay_seconds: Number(document.getElementById('max_delay_seconds').value || 60),
        pause_every: Number(document.getElementById('pause_every').value || 20),
        pause_seconds: Number(document.getElementById('pause_seconds').value || 300),
    };

    try {
        const res = await fetch('/api/campaign-sends', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(payload)
        });

        const data = await res.json();

        if (!res.ok) {
            throw new Error(data.message || 'Erro ao criar envio');
        }

        msg.innerHTML = `Envio #${data.send_id} criado com ${data.contacts} contatos.`;
        msg.className = 'text-sm text-green-700 font-semibold';

        await loadSends();

    } catch (err) {
        msg.innerHTML = err.message;
        msg.className = 'text-sm text-red-600 font-semibold';
    }
});

function targetLabel(send) {
    const type = send.target_type || 'all';
    const value = send.target_value || '';

    if (type === 'all') return 'Todos os contatos ativos';
    if (type === 'tag') return `TAG: ${value}`;
    if (type === 'state') return `Estado: ${value}`;
    if (type === 'city') return `Cidade: ${value}`;
    if (type === 'ddd') return `DDD: ${value}`;

    return type;
}

async function loadSends() {
    const box = document.getElementById('sendsList');
    box.innerHTML = '<p class="text-slate-500 text-sm">Carregando...</p>';

    try {
        const res = await fetch('/api/campaign-sends', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Accept': 'application/json',
            }
        });

        const sends = await res.json();

        if (!Array.isArray(sends) || sends.length === 0) {
            box.innerHTML = '<p class="text-slate-500 text-sm">Nenhum envio encontrado.</p>';
            return;
        }

        box.innerHTML = sends.map(send => {
            const total = send.total_contacts || 0;
            const sent = send.total_sent || 0;
            const failed = send.total_failed || 0;
            const pending = send.total_pending || 0;
            const done = sent + failed;
            const percent = total > 0 ? Math.round((done / total) * 100) : 0;

            return `
                <div class="border rounded-xl p-4">
                    <div class="flex justify-between items-start mb-2 gap-4">
                        <div>
                            <div class="font-bold text-slate-800">
                                #${send.id} - ${send.campaign?.name ?? 'Campanha removida'}
                            </div>
                            <div class="text-xs text-slate-500">
                                Público: <strong>${targetLabel(send)}</strong>
                            </div>
                            <div class="text-xs text-slate-500">
                                Status: <strong>${send.status}</strong>
                            </div>
                        </div>

                        <div class="text-right text-xs text-slate-500">
                            ${send.created_at ?? ''}
                        </div>
                    </div>

                    <div class="w-full bg-slate-200 rounded-full h-3 mb-3">
                        <div class="bg-blue-600 h-3 rounded-full" style="width:${percent}%"></div>
                    </div>

                    <div class="grid grid-cols-4 gap-2 text-center text-sm">
                        <div class="bg-slate-100 rounded-lg p-2">
                            <div class="font-bold">${total}</div>
                            <div class="text-xs text-slate-500">Total</div>
                        </div>
                        <div class="bg-yellow-50 rounded-lg p-2">
                            <div class="font-bold">${pending}</div>
                            <div class="text-xs text-slate-500">Pendentes</div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-2">
                            <div class="font-bold">${sent}</div>
                            <div class="text-xs text-slate-500">Enviadas</div>
                        </div>
                        <div class="bg-red-50 rounded-lg p-2">
                            <div class="font-bold">${failed}</div>
                            <div class="text-xs text-slate-500">Falhas</div>
                        </div>
                    </div>

                    <div class="text-xs text-slate-500 mt-3">
                        Intervalo: ${send.min_delay_seconds ?? 20}s a ${send.max_delay_seconds ?? 60}s |
                        Pausa a cada ${send.pause_every ?? 20} mensagens por ${send.pause_seconds ?? 300}s
                    </div>
                </div>
            `;
        }).join('');

    } catch (err) {
        box.innerHTML = `<p class="text-red-600 text-sm">${err.message}</p>`;
    }
}

toggleTargetValue();
loadSends();
setInterval(loadSends, 10000);
</script>

</body>
</html>