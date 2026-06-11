<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Conexão WhatsApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
</head>

<body class="bg-slate-100 min-h-screen">

<div class="max-w-4xl mx-auto p-6">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Conexão WhatsApp</h1>
            <p class="text-sm text-slate-500">
                Instância: <strong>{{ $instance }}</strong>
            </p>
        </div>

        <a href="/" class="bg-slate-700 text-white px-4 py-2 rounded-lg hover:bg-slate-800">
            Dashboard
        </a>
    </div>

    <div class="bg-white rounded-xl shadow p-6 mb-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">Status da conexão</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-slate-50 border rounded-lg p-4">
                <p class="text-sm text-slate-500">Status</p>
                <p id="statusText" class="text-xl font-bold text-slate-800">Carregando...</p>
            </div>

            <div class="bg-slate-50 border rounded-lg p-4">
                <p class="text-sm text-slate-500">Número conectado</p>
                <p id="numberText" class="text-xl font-bold text-slate-800">-</p>
            </div>

            <div class="bg-slate-50 border rounded-lg p-4">
                <p class="text-sm text-slate-500">Instância</p>
                <p class="text-xl font-bold text-slate-800">{{ $instance }}</p>
            </div>
        </div>

        <div class="flex gap-3 flex-wrap">
            <button onclick="createInstance()"
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                Criar instância
            </button>

            <button onclick="generateQrCode()"
                    class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                Gerar QR Code
            </button>

            <button onclick="loadStatus()"
                    class="bg-slate-600 text-white px-4 py-2 rounded-lg hover:bg-slate-700">
                Atualizar status
            </button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">QR Code</h2>

        <div id="qrBox" class="flex items-center justify-center min-h-[320px] bg-slate-50 border rounded-lg">
            <p class="text-slate-500">Clique em “Gerar QR Code”.</p>
        </div>
    </div>

</div>

<script>
    async function loadStatus() {
        try {
            const res = await fetch('/whatsapp/status');
            const json = await res.json();

            const statusText = document.getElementById('statusText');
            const numberText = document.getElementById('numberText');

            if (!json.success) {
                statusText.innerText = 'erro';
                numberText.innerText = '-';
                return;
            }

            statusText.innerText = json.status || '-';

            if (json.number) {
                numberText.innerText = json.number
                    .replace('@s.whatsapp.net', '')
                    .replace('@c.us', '');
            } else {
                numberText.innerText = '-';
            }
        } catch (error) {
            document.getElementById('statusText').innerText = 'erro';
            document.getElementById('numberText').innerText = '-';
            console.error(error);
        }
    }

    async function createInstance() {
        try {
            const res = await fetch('/whatsapp/create', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            });

            const json = await res.json();

            if (!json.success) {
                alert('Erro ao criar instância');
                console.log(json);
                return;
            }

            alert('Instância criada/verificada com sucesso.');
            loadStatus();
        } catch (error) {
            alert('Erro ao criar instância');
            console.error(error);
        }
    }

    async function generateQrCode() {
        const box = document.getElementById('qrBox');

        box.innerHTML = `
            <p class="text-slate-500">Gerando QR Code...</p>
        `;

        try {
            const res = await fetch('/whatsapp/qrcode');
            const json = await res.json();

            console.log(json);

            if (!json.success) {
                box.innerHTML = `
                    <div class="text-center text-red-600">
                        <p class="font-bold">Erro ao gerar QR Code</p>
                        <p class="text-sm">Verifique se a Evolution API está ativa.</p>
                    </div>
                `;
                return;
            }

            let qrBase64 =
                json.data?.base64 ||
                json.data?.qrcode?.base64 ||
                json.data?.qrcode ||
                null;

            let qrCodeText =
                json.data?.code ||
                json.data?.qrcode?.code ||
                json.data?.pairingCode ||
                null;

            if (qrBase64 && typeof qrBase64 === 'string' && qrBase64.length > 100) {
                if (!qrBase64.startsWith('data:image')) {
                    qrBase64 = 'data:image/png;base64,' + qrBase64;
                }

                box.innerHTML = `
                    <div class="text-center p-4">
                        <img src="${qrBase64}" class="w-72 h-72 mx-auto border rounded-lg bg-white p-3">
                        <p class="text-sm text-slate-500 mt-3">
                            Abra o WhatsApp no celular e escaneie o QR Code.
                        </p>
                    </div>
                `;
                return;
            }

            if (qrCodeText) {
                box.innerHTML = `
                    <div class="text-center p-4">
                        <canvas id="qrCanvas" class="mx-auto bg-white border rounded-lg p-3"></canvas>
                        <p class="text-sm text-slate-500 mt-3">
                            Abra o WhatsApp no celular e escaneie o QR Code.
                        </p>
                    </div>
                `;

                await QRCode.toCanvas(document.getElementById('qrCanvas'), qrCodeText, {
                    width: 280,
                    margin: 2
                });

                return;
            }

            box.innerHTML = `
                <div class="text-center text-red-600 p-4">
                    <p class="font-bold">QR Code não encontrado na resposta.</p>
                    <pre class="text-xs text-left mt-3 bg-slate-100 p-3 rounded overflow-auto max-h-72">${JSON.stringify(json.data, null, 2)}</pre>
                </div>
            `;
        } catch (error) {
            box.innerHTML = `
                <div class="text-center text-red-600">
                    <p class="font-bold">Erro ao gerar QR Code</p>
                    <p class="text-sm">Veja o console do navegador.</p>
                </div>
            `;
            console.error(error);
        }
    }

    loadStatus();
    setInterval(loadStatus, 5000);
</script>

</body>
</html>
