<!doctype html>
<html lang="pt-BR">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Simulador de Disparos WhatsApp</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
	document.addEventListener("DOMContentLoaded", () => {
	    const token = localStorage.getItem("token");

	    if (!token) {
	        window.location.href = "/login";
	    }
	});
    </script>
    <style>
      :root {
        --brand: #25d366;
        --brand-dark: #128c7e;
      }
      .btn {
        @apply inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-medium transition;
      }
      .scrollbar::-webkit-scrollbar {
        width: 8px;
        height: 8px;
      }
      .scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
      }
      @keyframes pulse-ring {
        0% {
          transform: scale(0.9);
          opacity: 0.6;
        }
        100% {
          transform: scale(1.6);
          opacity: 0;
        }
      }
      .pulse-dot::before {
        content: "";
        position: absolute;
        inset: 0;
        border-radius: 9999px;
        background: currentColor;
        animation: pulse-ring 1.5s ease-out infinite;
      }
      .qr {
        background-image:
          linear-gradient(
            45deg,
            #000 25%,
            transparent 25%,
            transparent 75%,
            #000 75%
          ),
          linear-gradient(
            45deg,
            #000 25%,
            transparent 25%,
            transparent 75%,
            #000 75%
          );
        background-size: 14px 14px;
        background-position:
          0 0,
          7px 7px;
      }
    </style>
  </head>
  <body class="bg-slate-50 text-slate-900">
    <div class="max-w-7xl mx-auto p-4">
      <!-- Header -->
      <header class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
          <div
            class="w-10 h-10 rounded-xl flex items-center justify-center"
            style="background: var(--brand)"
          >
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="white"
              class="w-6 h-6"
            >
              <path
                d="M20.5 3.5A11.9 11.9 0 0 0 12 0C5.4 0 0 5.4 0 12c0 2.1.6 4.2 1.6 6L0 24l6.1-1.6c1.8 1 3.8 1.5 5.9 1.5 6.6 0 12-5.4 12-12 0-3.2-1.2-6.2-3.5-8.4zM12 21.8c-1.9 0-3.7-.5-5.3-1.4l-.4-.2-3.6 1 1-3.5-.3-.4A9.8 9.8 0 0 1 2.2 12C2.2 6.6 6.6 2.2 12 2.2c2.6 0 5.1 1 7 2.9a9.75 9.75 0 0 1 2.9 7c0 5.4-4.5 9.7-9.9 9.7zm5.4-7.3c-.3-.1-1.7-.9-2-1s-.5-.2-.7.2c-.2.3-.8 1-1 1.2-.2.2-.4.2-.7.1s-1.3-.5-2.4-1.5c-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.7l.5-.6.3-.5.1-.4-.1-.4c-.1-.2-.7-1.6-.9-2.2-.3-.6-.5-.5-.7-.5h-.6c-.2 0-.6.1-.9.4-.3.3-1.1 1.1-1.1 2.7s1.2 3.1 1.3 3.3c.2.2 2.3 3.5 5.5 4.9.8.3 1.4.5 1.8.7.8.2 1.5.2 2 .1.6-.1 1.7-.7 2-1.4.3-.7.3-1.2.2-1.4-.1-.1-.3-.2-.6-.3z"
              />
            </svg>
          </div>
          <div>
            <h1 class="text-lg font-semibold leading-tight">
              Disparos WhatsApp
            </h1>
            <p class="text-xs text-slate-500">Ambiente de mensagem</p>
          </div>
        </div>
        <div class="flex items-center gap-2">
          <span
            class="text-xs px-2 py-1 rounded-full bg-amber-100 text-amber-800"
            >Modo de envio</span
          >
          <button
            id="btnReset"
            class="text-sm text-slate-500 hover:text-red-600"
          >
            Resetar tudo
          </button>
	 <button onclick="logout()" class="text-red-500">Sair</button>
        </div>
      </header>

      <div class="grid lg:grid-cols-[280px_1fr] gap-4">
        <!-- Sidebar: números conectados -->
        <aside class="bg-white rounded-xl border border-slate-200 p-4 h-max">
          <div class="flex items-center justify-between mb-3">
            <h2 class="font-medium text-sm">Números conectados</h2>
            <button
              id="btnAddNumber"
              class="text-xs text-[var(--brand-dark)] hover:underline"
            >
              + Conectar
            </button>
          </div>
          <ul id="numbersList" class="space-y-2"></ul>
          <p class="text-[11px] text-slate-400 mt-3">Números, gerados.</p>
        </aside>

        <!-- Main -->
        <main
          class="bg-white rounded-xl border border-slate-200 overflow-hidden"
        >
          <nav class="flex border-b border-slate-200 px-2">
            <button
              data-tab="contacts"
              class="tab-btn px-4 py-3 text-sm font-medium border-b-2 border-transparent data-[active=true]:border-[var(--brand)] data-[active=true]:text-[var(--brand-dark)]"
              data-active="true"
            >
              Contatos
            </button>
            <button
              data-tab="campaign"
              class="tab-btn px-4 py-3 text-sm font-medium border-b-2 border-transparent data-[active=true]:border-[var(--brand)] data-[active=true]:text-[var(--brand-dark)]"
            >
              Campanha
            </button>
            <button
              data-tab="report"
              class="tab-btn px-4 py-3 text-sm font-medium border-b-2 border-transparent data-[active=true]:border-[var(--brand)] data-[active=true]:text-[var(--brand-dark)]"
            >
              Relatório
            </button>
            <button
              data-tab="inbox"
              class="tab-btn px-4 py-3 text-sm font-medium border-b-2 border-transparent data-[active=true]:border-[var(--brand)] data-[active=true]:text-[var(--brand-dark)]"
            >
              Inbox
            </button>
          </nav>

          <!-- TAB: CONTATOS -->
          <section data-panel="contacts" class="panel p-6">
            <div class="grid md:grid-cols-2 gap-6">
              <div>
                <h3 class="font-medium mb-2">Importar contatos</h3>
                <p class="text-xs text-slate-500 mb-3">
                  Cole uma coluna com telefones (um por linha). Com ou sem DDI.
                  Nome opcional após vírgula.
                </p>
                <textarea
                  id="csvInput"
                  rows="8"
                  class="w-full rounded-md border border-slate-300 p-2 text-sm font-mono"
                  placeholder="+5511999990001, João&#10;11988887777, Maria&#10;(11) 97777-6666"
                ></textarea>
                <div class="flex items-center justify-between mt-3 gap-2">
                  <label class="flex items-center gap-2 text-sm">
                    <input id="markOptIn" type="checkbox" checked /> marcar como
                    opt-in
                  </label>
                  <button
                    id="btnImport"
                    class="btn bg-[var(--brand)] hover:bg-[var(--brand-dark)] text-white"
                  >
                    Importar
                  </button>
                </div>
                <div class="mt-3 flex gap-2">
                  <button
                    id="btnSeed"
                    class="text-xs text-slate-500 hover:text-slate-900 underline"
                  >
                    gerar
                  </button>
                  <button
                    id="btnClearContacts"
                    class="text-xs text-slate-500 hover:text-red-600 underline"
                  >
                    limpar
                  </button>
                </div>
              </div>

              <div>
                <div class="flex items-center justify-between mb-2">
                  <h3 class="font-medium">Base atual</h3>
                  <span class="text-xs text-slate-500"
                    ><span id="contactCount">0</span> contatos</span
                  >
                </div>
                <div
                  class="border border-slate-200 rounded-md max-h-80 overflow-auto scrollbar"
                >
                  <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                      <tr>
                        <th class="text-left px-3 py-2">Nome</th>
                        <th class="text-left px-3 py-2">Telefone</th>
                        <th class="text-left px-3 py-2">Opt-in</th>
                      </tr>
                    </thead>
                    <tbody id="contactsTable"></tbody>
                  </table>
                  <div
                    id="contactsEmpty"
                    class="p-6 text-center text-sm text-slate-400"
                  >
                    Nenhum contato ainda.
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- TAB: CAMPANHA -->
          <section data-panel="campaign" class="panel p-6 hidden">
            <div class="grid md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <h3 class="font-medium">Nova campanha</h3>

                <div>
                  <label
                    class="block text-xs uppercase tracking-wide text-slate-500 mb-1"
                    >Número que envia</label
                  >
                  <select
                    id="campaignNumber"
                    class="w-full rounded-md border border-slate-300 p-2 text-sm"
                  ></select>
                </div>

                <div>
                  <label
                    class="block text-xs uppercase tracking-wide text-slate-500 mb-1"
                    >Tipo</label
                  >
                  <div class="flex gap-2 text-sm flex-wrap" id="typeTabs">
                    <button
                      data-type="text"
                      class="type-btn px-3 py-1 rounded border border-slate-300 data-[active=true]:bg-[var(--brand)] data-[active=true]:text-white data-[active=true]:border-[var(--brand)]"
                      data-active="true"
                    >
                      Texto
                    </button>
                    <button
                      data-type="image"
                      class="type-btn px-3 py-1 rounded border border-slate-300 data-[active=true]:bg-[var(--brand)] data-[active=true]:text-white data-[active=true]:border-[var(--brand)]"
                    >
                      Imagem
                    </button>
                    <button
                      data-type="video"
                      class="type-btn px-3 py-1 rounded border border-slate-300 data-[active=true]:bg-[var(--brand)] data-[active=true]:text-white data-[active=true]:border-[var(--brand)]"
                    >
                      Vídeo
                    </button>
                    <button
                      data-type="audio"
                      class="type-btn px-3 py-1 rounded border border-slate-300 data-[active=true]:bg-[var(--brand)] data-[active=true]:text-white data-[active=true]:border-[var(--brand)]"
                    >
                      Áudio
                    </button>
                    <button
                      data-type="document"
                      class="type-btn px-3 py-1 rounded border border-slate-300 data-[active=true]:bg-[var(--brand)] data-[active=true]:text-white data-[active=true]:border-[var(--brand)]"
                    >
                      Documento
                    </button>
                  </div>
                </div>

                <!-- Arquivo: aparece quando tipo != text -->
                <div id="fileImportBlock" class="hidden">
                  <label
                    class="block text-xs uppercase tracking-wide text-slate-500 mb-1"
                    >Arquivo</label
                  >
                  <input
                    id="campaignFile"
                    type="file"
                    class="block w-full text-xs file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200"
                  />
                  <p id="fileMeta" class="text-[11px] text-slate-400 mt-1">
                    Nenhum arquivo selecionado.
                  </p>
                  <button
                    id="btnClearFile"
                    class="hidden mt-1 text-[11px] text-slate-500 hover:text-red-600 underline"
                  >
                    remover arquivo
                  </button>
                </div>

                <div>
                  <label
                    class="block text-xs uppercase tracking-wide text-slate-500 mb-1"
                    >Mensagem
                    <span class="text-slate-400 normal-case"
                      >(opcional p/ mídia — vira legenda)</span
                    ></label
                  >
                  <textarea
                    id="campaignBody"
                    rows="5"
                    class="w-full rounded-md border border-slate-300 p-2 text-sm"
                    placeholder="Olá @{{name}}, tudo bem? ..."
                  ></textarea>
                  <p class="text-[11px] text-slate-400 mt-1">
                    Use <code>@{{name}}</code> para personalizar com o nome do
                    contato.
                  </p>
                </div>

                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label
                      class="block text-xs uppercase tracking-wide text-slate-500 mb-1"
                      >Mensagens por segundo</label
                    >
                    <input
                      id="campaignRate"
                      type="number"
                      min="1"
                      max="20"
                      value="4"
                      class="w-full rounded-md border border-slate-300 p-2 text-sm"
                    />
                  </div>
                  <div>
                    <label
                      class="block text-xs uppercase tracking-wide text-slate-500 mb-1"
                      >Taxa de erro (%)</label
                    >
                    <input
                      id="campaignErrorRate"
                      type="number"
                      min="0"
                      max="50"
                      value="4"
                      class="w-full rounded-md border border-slate-300 p-2 text-sm"
                    />
                  </div>
                </div>

                <label class="flex items-center gap-2 text-sm">
                  <input id="onlyOptIn" type="checkbox" checked /> somente
                  contatos com opt-in
                </label>

                <div class="flex gap-2 pt-2">
                  <button
                    id="btnStart"
                    class="btn bg-[var(--brand)] hover:bg-[var(--brand-dark)] text-white"
                  >
                    Disparar
                  </button>
                  <button
                    id="btnStop"
                    class="btn bg-white border border-slate-300 hover:bg-slate-50"
                    disabled
                  >
                    Parar
                  </button>
                </div>
              </div>

              <!-- Preview + progresso -->
              <div>
                <h3 class="font-medium mb-2">Pré-visualização</h3>
                <div
                  class="rounded-lg border border-slate-200 p-3"
                  style="background: #ece5dd"
                >
                  <div class="flex justify-end">
                    <div
                      class="rounded-lg shadow-sm p-2 text-sm max-w-[85%]"
                      style="background: #dcf8c6"
                    >
                      <div id="previewAttachment" class="hidden mb-2"></div>
                      <div id="previewBody" class="whitespace-pre-wrap">
                        Digite uma mensagem...
                      </div>
                      <div class="text-[10px] text-slate-500 text-right mt-1">
                        agora
                      </div>
                    </div>
                  </div>
                </div>

                <div class="mt-4">
                  <div class="flex justify-between text-xs text-slate-500 mb-1">
                    <span id="progressLabel">0 de 0</span>
                    <span id="progressPct">0%</span>
                  </div>
                  <div class="h-2 rounded-full bg-slate-200 overflow-hidden">
                    <div
                      id="progressBar"
                      class="h-full bg-[var(--brand)] transition-all"
                      style="width: 0%"
                    ></div>
                  </div>
                </div>

                <!-- Live counters -->
                <div class="grid grid-cols-3 gap-2 mt-4">
                  <div class="bg-slate-50 rounded p-3 text-center">
                    <div class="text-[11px] uppercase text-slate-500">
                      Enviadas
                    </div>
                    <div class="text-xl font-semibold" id="cntSent">0</div>
                  </div>
                  <div class="bg-sky-50 rounded p-3 text-center">
                    <div class="text-[11px] uppercase text-sky-700">
                      Entregues
                    </div>
                    <div
                      class="text-xl font-semibold text-sky-700"
                      id="cntDelivered"
                    >
                      0
                    </div>
                  </div>
                  <div class="bg-emerald-50 rounded p-3 text-center">
                    <div class="text-[11px] uppercase text-emerald-700">
                      Lidas
                    </div>
                    <div
                      class="text-xl font-semibold text-emerald-700"
                      id="cntRead"
                    >
                      0
                    </div>
                  </div>
                  <div class="bg-green-50 rounded p-3 text-center">
                    <div class="text-[11px] uppercase text-green-700">
                      Respostas
                    </div>
                    <div
                      class="text-xl font-semibold text-green-700"
                      id="cntReplied"
                    >
                      0
                    </div>
                  </div>
                  <div class="bg-red-50 rounded p-3 text-center">
                    <div class="text-[11px] uppercase text-red-700">Erros</div>
                    <div
                      class="text-xl font-semibold text-red-700"
                      id="cntFailed"
                    >
                      0
                    </div>
                  </div>
                  <div class="bg-slate-100 rounded p-3 text-center">
                    <div class="text-[11px] uppercase text-slate-500">Alvo</div>
                    <div class="text-xl font-semibold" id="cntTotal">0</div>
                  </div>
                </div>
              </div>
            </div>
          </section>

          <!-- TAB: RELATÓRIO -->
          <section data-panel="report" class="panel p-6 hidden">
            <div class="flex items-center justify-between mb-3">
              <h3 class="font-medium">Relatório de mensagens</h3>
              <button
                id="btnExport"
                class="btn bg-white border border-slate-300 hover:bg-slate-50 text-sm"
              >
                Exportar CSV
              </button>
            </div>
            <div
              class="border border-slate-200 rounded-md max-h-[70vh] overflow-auto scrollbar"
            >
              <table class="w-full text-sm">
                <thead
                  class="bg-slate-50 text-xs uppercase text-slate-500 sticky top-0"
                >
                  <tr>
                    <th class="text-left px-3 py-2">Telefone</th>
                    <th class="text-left px-3 py-2">Nome</th>
                    <th class="text-left px-3 py-2">Anexo</th>
                    <th class="text-left px-3 py-2">Status</th>
                    <th class="text-left px-3 py-2">Enviada</th>
                    <th class="text-left px-3 py-2">Entregue</th>
                    <th class="text-left px-3 py-2">Lida</th>
                    <th class="text-left px-3 py-2">Resposta</th>
                    <th class="text-left px-3 py-2">Erro</th>
                  </tr>
                </thead>
                <tbody id="reportTable"></tbody>
              </table>
              <div
                id="reportEmpty"
                class="p-8 text-center text-sm text-slate-400"
              >
                Ainda não há mensagens. Dispare uma campanha na aba Campanha.
              </div>
            </div>
          </section>

          <!-- TAB: INBOX -->
          <section data-panel="inbox" class="panel p-6 hidden">
            <div class="grid md:grid-cols-[260px_1fr] gap-4 h-[70vh]">
              <aside
                class="border border-slate-200 rounded-md overflow-auto scrollbar"
              >
                <ul id="inboxList" class="divide-y divide-slate-100"></ul>
                <div
                  id="inboxEmpty"
                  class="p-6 text-center text-xs text-slate-400"
                >
                  Sem conversas ainda. As respostas simuladas aparecem aqui.
                </div>
              </aside>
              <section
                class="border border-slate-200 rounded-md flex flex-col overflow-hidden"
              >
                <header
                  id="inboxHeader"
                  class="px-4 py-3 border-b border-slate-200 text-sm font-medium bg-slate-50"
                >
                  Selecione uma conversa
                </header>
                <div
                  id="inboxThread"
                  class="flex-1 overflow-auto scrollbar p-4 space-y-2"
                  style="background: #ece5dd"
                ></div>
                <div
                  id="inboxReplyFileMeta"
                  class="hidden px-4 py-2 text-[11px] text-slate-500 bg-slate-50 border-t border-slate-200"
                ></div>
                <form
                  id="inboxReplyForm"
                  class="border-t border-slate-200 p-2 flex gap-2 items-center"
                >
                  <label
                    class="cursor-pointer text-slate-500 hover:text-slate-800 px-2 text-lg"
                    title="Anexar arquivo"
                  >
                    <input id="inboxReplyFile" type="file" class="hidden" />+
                  </label>
                  <input
                    id="inboxReplyInput"
                    type="text"
                    placeholder="Digite uma resposta..."
                    class="flex-1 rounded-md border border-slate-300 p-2 text-sm"
                  />
                  <button
                    class="btn bg-[var(--brand)] hover:bg-[var(--brand-dark)] text-white text-sm"
                  >
                    Enviar
                  </button>
                </form>
              </section>
            </div>
          </section>
        </main>
      </div>

      <footer class="mt-6 text-center text-xs text-slate-400">
        Todos os números, entregas e respostas
      </footer>
    </div>

    <!-- Modal: conectar número -->
    <div
      id="modalQR"
      class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50"
    >
      <div
        class="bg-white rounded-xl p-6 max-w-sm w-full text-center space-y-3"
      >
        <h3 class="font-semibold">Escaneie o QR Code</h3>
        <p class="text-xs text-slate-500">
          Simulação — conecta automaticamente em
          <span id="qrCountdown">3</span>s.
        </p>
        <div
          class="qr w-48 h-48 mx-auto border-4 border-white rounded-md"
          style="box-shadow: 0 0 0 2px var(--brand-dark)"
        ></div>
        <button
          id="btnCancelQR"
          class="text-sm text-slate-500 hover:text-red-600"
        >
          Cancelar
        </button>
      </div>
    </div>

    <script>
const API = "/api";

function getToken() {
  return localStorage.getItem("token");
}

async function api(url, options = {}) {
  const res = await fetch(API + url, {
    ...options,
    headers: {
      "Content-Type": "application/json",
      Authorization: "Bearer " + getToken(),
      ...(options.headers || {})
    }
  });

  return res.json();
}

async function loginAuto() {
  if (getToken()) return;

  const res = await fetch(API + "/login", {
    method: "POST",
    headers: {"Content-Type": "application/json"},
    body: JSON.stringify({
      email: "admin@email.com",
      password: "123456"
    })
  });

  const data = await res.json();
  if (data.token) localStorage.setItem("token", data.token);
}

      // ======================================================================
      // Estado global
      // ======================================================================
      const state = {
        numbers: [],
        contacts: [],
        messages: [],
        campaign: { running: false, type: "text", file: null }, // file: {name, mime, size, kind, dataUrl}
        inbox: {},
        activeInboxPhone: null,
        inboxReplyFile: null,
      };

      // Limite seguro para não estourar localStorage
      const MAX_FILE_BYTES = 8 * 1024 * 1024; // 8 MB por arquivo

      // ======================================================================
      // Utilitários
      // ======================================================================
      const $ = (q, ctx = document) => ctx.querySelector(q);
      const $$ = (q, ctx = document) => [...ctx.querySelectorAll(q)];
      const uid = () => Math.random().toString(36).slice(2, 10);
      const now = () => new Date().toISOString();
      const fmt = (iso) =>
        iso
          ? new Date(iso).toLocaleTimeString("pt-BR", {
              hour: "2-digit",
              minute: "2-digit",
              second: "2-digit",
            })
          : "—";
      const sleep = (ms) => new Promise((r) => setTimeout(r, ms));
      const rand = (min, max) => Math.random() * (max - min) + min;
      const pick = (arr) => arr[Math.floor(Math.random() * arr.length)];
      const escapeHtml = (s) =>
        String(s ?? "").replace(
          /[&<>"']/g,
          (c) =>
            ({
              "&": "&amp;",
              "<": "&lt;",
              ">": "&gt;",
              '"': "&quot;",
              "'": "&#39;",
            })[c],
        );
      const formatSize = (b) =>
        b < 1024
          ? b + " B"
          : b < 1024 * 1024
            ? (b / 1024).toFixed(1) + " KB"
            : (b / 1024 / 1024).toFixed(1) + " MB";

      function normalizePhone(raw) {
        const d = String(raw).replace(/\D+/g, "");
        if (!d) return null;
        if (d.length === 13 && d.startsWith("55")) return "+" + d;
        if (d.length === 12 && d.startsWith("55")) return "+" + d;
        if (d.length === 11 || d.length === 10) return "+55" + d;
        if (d.length >= 11 && d.length <= 15) return "+" + d;
        return null;
      }

      function readFileAsDataURL(file) {
        return new Promise((resolve, reject) => {
          const r = new FileReader();
          r.onload = () => resolve(r.result);
          r.onerror = reject;
          r.readAsDataURL(file);
        });

async function checkAuth() {
    const token = localStorage.getItem("token");

    if (!token) {
        window.location.href = "/login";
        return;
    }

    try {
        const res = await fetch('/api/me', {
            headers: {
                Authorization: 'Bearer ' + token
            }
        });

        if (!res.ok) {
            throw new Error();
        }

    } catch (e) {
        localStorage.removeItem("token");
        window.location.href = "/login";
    }
}

document.addEventListener("DOMContentLoaded", checkAuth);
      }

	<script>
	function logout() {
	    localStorage.removeItem("token");
	    window.location.href = "/login";
	}
	</script>

      // Accept por tipo
      const ACCEPT_BY_TYPE = {
        image: "image/*",
        video: "video/*",
        audio: "audio/*",
        document:
          ".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip,.rar,application/*",
      };

      function inferKind(mime) {
        if (!mime) return "document";
        if (mime.startsWith("image/")) return "image";
        if (mime.startsWith("video/")) return "video";
        if (mime.startsWith("audio/")) return "audio";
        return "document";
      }

      // Renderiza um anexo (preview/thread/inbox). Se dataUrl ausente -> placeholder.
      function renderAttachmentHTML(file, { maxW = "220px" } = {}) {
        if (!file) return "";
        const kind = file.kind || inferKind(file.mime);
        const name = escapeHtml(file.name || "arquivo");
        const size = file.size ? escapeHtml(formatSize(file.size)) : "";
        const url = file.dataUrl;
        if (!url) {
          const icon =
            kind === "image"
              ? "IMG"
              : kind === "video"
                ? "VID"
                : kind === "audio"
                  ? "MIC"
                  : "DOC";
          return `<div class="p-2 bg-white/70 rounded text-xs flex items-center gap-2"><span class="font-semibold text-slate-600">${icon}</span><span class="truncate">${name}</span><span class="text-slate-400">${size}</span></div>`;
        }
        if (kind === "image") {
          return `<a href="${url}" target="_blank"><img src="${url}" alt="${name}" class="rounded max-w-full" style="max-width:${maxW};max-height:220px;object-fit:cover" /></a>`;
        }
        if (kind === "video") {
          return `<video src="${url}" controls class="rounded" style="max-width:${maxW};max-height:220px"></video>`;
        }
        if (kind === "audio") {
          return `<audio src="${url}" controls style="width:${maxW};max-width:100%"></audio>`;
        }
        return `<a href="${url}" download="${name}" class="flex items-center gap-2 p-2 bg-white/70 rounded text-xs hover:bg-white"><span class="font-semibold text-slate-600">DOC</span><span class="truncate">${name}</span><span class="text-slate-400">${size}</span></a>`;
      }

      // ======================================================================
      // Persistência (não salva dataUrl em messages/inbox p/ não estourar localStorage)
      // ======================================================================
      function persist() {
        try {
          const safeMsgs = state.messages.map((m) => {
            if (!m.file) return m;
            const { dataUrl, ...rest } = m.file;
            return { ...m, file: rest };
          });
          const safeInbox = {};
          for (const [phone, arr] of Object.entries(state.inbox)) {
            safeInbox[phone] = arr.map((item) => {
              if (!item.file) return item;
              const { dataUrl, ...rest } = item.file;
              return { ...item, file: rest };
            });
          }
          localStorage.setItem(
            "wasim_state",
            JSON.stringify({
              numbers: state.numbers,
              contacts: state.contacts,
              messages: safeMsgs,
              inbox: safeInbox,
            }),
          );
        } catch (e) {
          console.warn("persist falhou", e);
        }
      }
      function restore() {
        try {
          const raw = localStorage.getItem("wasim_state");
          if (!raw) return;
          const s = JSON.parse(raw);
          Object.assign(state, s);
        } catch {}
      }

      // ======================================================================
      // Tabs
      // ======================================================================
      $$(".tab-btn").forEach((b) =>
        b.addEventListener("click", () => {
          $$(".tab-btn").forEach((x) => (x.dataset.active = "false"));
          b.dataset.active = "true";
          $$(".panel").forEach((p) => p.classList.add("hidden"));
          $(`[data-panel="${b.dataset.tab}"]`).classList.remove("hidden");
        }),
      );

      // ======================================================================
      // Numbers
      // ======================================================================
      function renderNumbers() {
        const ul = $("#numbersList");
        if (state.numbers.length === 0) {
          ul.innerHTML = `<li class="text-xs text-slate-400 px-2 py-3 text-center border border-dashed border-slate-200 rounded">Nenhum número conectado.</li>`;
        } else {
          ul.innerHTML = state.numbers
            .map(
              (n) => `
      <li class="flex items-center justify-between p-2 rounded-md border border-slate-200">
        <div>
          <div class="text-sm font-medium">${escapeHtml(n.label)}</div>
          <div class="text-xs text-slate-500 font-mono">${escapeHtml(n.phone)}</div>
        </div>
        <div class="relative text-emerald-500" title="online">
          <span class="block w-2.5 h-2.5 rounded-full bg-emerald-500 pulse-dot"></span>
        </div>
      </li>
    `,
            )
            .join("");
        }
        $("#campaignNumber").innerHTML = state.numbers.length
          ? state.numbers
              .map(
                (n) =>
                  `<option value="${n.id}">${escapeHtml(n.label)} (${escapeHtml(n.phone)})</option>`,
              )
              .join("")
          : `<option value="">Conecte um número primeiro</option>`;
      }

      function randomPhone() {
        const ddd = pick([
          "11",
          "21",
          "31",
          "41",
          "51",
          "61",
          "71",
          "81",
          "85",
        ]);
        const rest = Math.floor(Math.random() * 1e8)
          .toString()
          .padStart(8, "0");
        return `+559${ddd}${rest}`.slice(0, 14);
      }

      $("#btnAddNumber").onclick = () => {
        $("#modalQR").classList.remove("hidden");
        $("#modalQR").classList.add("flex");
        let remaining = 3;
        $("#qrCountdown").textContent = remaining;
        const t = setInterval(() => {
          remaining -= 1;
          $("#qrCountdown").textContent = remaining;
          if (remaining <= 0) {
            clearInterval(t);
            $("#modalQR").classList.add("hidden");
            $("#modalQR").classList.remove("flex");
            const n = {
              id: uid(),
              label: `Atendimento ${state.numbers.length + 1}`,
              phone: randomPhone(),
            };
            state.numbers.push(n);
            persist();
            renderNumbers();
          }
        }, 1000);
      };
      $("#btnCancelQR").onclick = () => {
        $("#modalQR").classList.add("hidden");
        $("#modalQR").classList.remove("flex");
      };

      // ======================================================================
      // Contatos
      // ======================================================================
      function renderContacts() {
        const body = $("#contactsTable");
        if (state.contacts.length === 0) {
          body.innerHTML = "";
          $("#contactsEmpty").classList.remove("hidden");
        } else {
          $("#contactsEmpty").classList.add("hidden");
          body.innerHTML = state.contacts
            .map(
              (c) => `
      <tr class="border-t border-slate-100">
        <td class="px-3 py-2">${escapeHtml(c.name) || "—"}</td>
        <td class="px-3 py-2 font-mono text-xs">${escapeHtml(c.phone)}</td>
        <td class="px-3 py-2">${c.optedIn ? '<span class="text-xs text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded">opt-in</span>' : '<span class="text-xs text-slate-600 bg-slate-100 px-2 py-0.5 rounded">sem</span>'}</td>
      </tr>
    `,
            )
            .join("");
        }
        $("#contactCount").textContent = state.contacts.length;
      }

      $("#btnImport").onclick = () => {
        const raw = $("#csvInput").value.trim();
        if (!raw) return;
        const markOpt = $("#markOptIn").checked;
        const lines = raw
          .split(/\r?\n/)
          .map((l) => l.trim())
          .filter(Boolean);
        let added = 0;
        for (const line of lines) {
          const [p, n] = line.split(/[,;\t]/).map((s) => s?.trim());
          const phone = normalizePhone(p);
          if (!phone) continue;
          if (state.contacts.some((c) => c.phone === phone)) continue;
          state.contacts.push({
            id: uid(),
            phone,
            name: n || "",
            optedIn: markOpt,
          });
          added++;
        }
        $("#csvInput").value = "";
        persist();
        renderContacts();
        if (added === 0)
          alert("Nenhum telefone válido encontrado (ou todos já existiam).");
      };

      $("#btnClearContacts").onclick = () => {
        if (!confirm("Apagar todos os contatos?")) return;
        state.contacts = [];
        persist();
        renderContacts();
      };

      $("#btnSeed").onclick = () => {
        const names = [
          "Ana",
          "Bruno",
          "Carla",
          "Diego",
          "Elisa",
          "Felipe",
          "Gabi",
          "Hugo",
          "Iara",
          "João",
          "Karen",
          "Léo",
          "Marina",
          "Nico",
          "Olívia",
          "Pedro",
          "Quesia",
          "Rafa",
          "Sandra",
          "Tiago",
        ];
        for (const n of names) {
          state.contacts.push({
            id: uid(),
            name: n,
            phone: randomPhone(),
            optedIn: true,
          });
        }
        persist();
        renderContacts();
      };

      // ======================================================================
      // Campanha: tipo + arquivo
      // ======================================================================
      $("#campaignBody").addEventListener("input", updatePreview);

      $$(".type-btn").forEach((b) =>
        b.addEventListener("click", () => {
          $$(".type-btn").forEach((x) => (x.dataset.active = "false"));
          b.dataset.active = "true";
          const type = b.dataset.type;
          state.campaign.type = type;
          const block = $("#fileImportBlock");
          if (type === "text") {
            block.classList.add("hidden");
          } else {
            block.classList.remove("hidden");
            $("#campaignFile").setAttribute(
              "accept",
              ACCEPT_BY_TYPE[type] || "",
            );
            if (state.campaign.file && state.campaign.file.kind !== type) {
              clearCampaignFile();
            }
          }
          updatePreview();
        }),
      );

      function clearCampaignFile() {
        state.campaign.file = null;
        $("#campaignFile").value = "";
        $("#fileMeta").textContent = "Nenhum arquivo selecionado.";
        $("#btnClearFile").classList.add("hidden");
        updatePreview();
      }
      $("#btnClearFile").onclick = clearCampaignFile;

      $("#campaignFile").addEventListener("change", async (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        if (file.size > MAX_FILE_BYTES) {
          alert(
            `Arquivo muito grande (${formatSize(file.size)}). Limite: ${formatSize(MAX_FILE_BYTES)}.`,
          );
          e.target.value = "";
          return;
        }
        const kind =
          state.campaign.type === "text"
            ? inferKind(file.type)
            : state.campaign.type;
        const dataUrl = await readFileAsDataURL(file);
        state.campaign.file = {
          name: file.name,
          mime: file.type || "application/octet-stream",
          size: file.size,
          kind,
          dataUrl,
        };
        $("#fileMeta").textContent = `${file.name} · ${formatSize(file.size)}`;
        $("#btnClearFile").classList.remove("hidden");
        updatePreview();
      });

      function updatePreview() {
        const body = $("#campaignBody").value;
        const sample = state.contacts.find((c) => c.optedIn)?.name || "cliente";
        const hasFile = state.campaign.type !== "text" && !!state.campaign.file;
        const txt = body
          ? body.replace(/\{\{name\}\}/g, sample)
          : hasFile
            ? ""
            : "Digite uma mensagem...";
        $("#previewBody").textContent = txt;
        $("#previewBody").style.display = txt || !hasFile ? "" : "none";

        const att = $("#previewAttachment");
        if (state.campaign.type !== "text" && state.campaign.file) {
          att.classList.remove("hidden");
          att.innerHTML = renderAttachmentHTML(state.campaign.file, {
            maxW: "240px",
          });
        } else if (state.campaign.type !== "text") {
          att.classList.remove("hidden");
          const placeholders = {
            image: "Selecione uma imagem",
            video: "Selecione um vídeo",
            audio: "Selecione um áudio",
            document: "Selecione um documento",
          };
          att.innerHTML = `<div class="p-2 bg-white/70 rounded text-xs text-slate-500">${placeholders[state.campaign.type]}</div>`;
        } else {
          att.classList.add("hidden");
          att.innerHTML = "";
        }
      }

      // ======================================================================
      // Campanha: disparo
      // ======================================================================
      function resetCounters() {
        [
          "cntSent",
          "cntDelivered",
          "cntRead",
          "cntReplied",
          "cntFailed",
          "cntTotal",
        ].forEach((id) => ($("#" + id).textContent = "0"));
        $("#progressBar").style.width = "0%";
        $("#progressLabel").textContent = "0 de 0";
        $("#progressPct").textContent = "0%";
      }

      function recomputeCounters() {
        const msgs = state.messages;
        const c = (s) => msgs.filter((m) => m.status === s).length;
        $("#cntSent").textContent = msgs.filter((m) =>
          ["sent", "delivered", "read", "replied"].includes(m.status),
        ).length;
        $("#cntDelivered").textContent = msgs.filter((m) =>
          ["delivered", "read", "replied"].includes(m.status),
        ).length;
        $("#cntRead").textContent = msgs.filter((m) =>
          ["read", "replied"].includes(m.status),
        ).length;
        $("#cntReplied").textContent = c("replied");
        $("#cntFailed").textContent = c("failed");
      }

      $("#btnStart").onclick = async () => {
        if (state.campaign.running) return;
        const numberId = $("#campaignNumber").value;
        if (!numberId) {
          alert("Conecte um número primeiro.");
          return;
        }
        const body = $("#campaignBody").value.trim();
        const type = state.campaign.type;
        if (type === "text" && !body) {
          alert("Mensagem vazia.");
          return;
        }
        if (type !== "text" && !state.campaign.file) {
          alert("Selecione o arquivo para envio.");
          return;
        }

        const onlyOpt = $("#onlyOptIn").checked;
        const recipients = state.contacts.filter((c) =>
          onlyOpt ? c.optedIn : true,
        );
        if (recipients.length === 0) {
          alert("Sem contatos elegíveis.");
          return;
        }

        const rate = Math.max(1, Number($("#campaignRate").value) || 4);
        const errorRate =
          Math.max(
            0,
            Math.min(50, Number($("#campaignErrorRate").value) || 0),
          ) / 100;
        const interval = Math.ceil(1000 / rate);
        const number = state.numbers.find((n) => n.id === numberId);
        const file = state.campaign.file;

        state.campaign.running = true;
        $("#btnStart").disabled = true;
        $("#btnStop").disabled = false;
        resetCounters();
        $("#cntTotal").textContent = recipients.length;

        for (let i = 0; i < recipients.length; i++) {
          if (!state.campaign.running) break;
          const c = recipients[i];
          const id = uid();
          const personalized = body.replace(
            /\{\{name\}\}/g,
            c.name || "cliente",
          );
          const msg = {
            id,
            contactId: c.id,
            phone: c.phone,
            name: c.name,
            numberId,
            body: personalized,
            type,
            file: file ? { ...file } : null,
            status: "queued",
            error: null,
            sentAt: null,
            deliveredAt: null,
            readAt: null,
            repliedAt: null,
            reply: null,
          };
          state.messages.push(msg);
          $("#progressLabel").textContent = `${i + 1} de ${recipients.length}`;
          const pct = Math.round(((i + 1) / recipients.length) * 100);
          $("#progressBar").style.width = pct + "%";
          $("#progressPct").textContent = pct + "%";

          await sleep(interval);
          if (Math.random() < errorRate) {
            msg.status = "failed";
            msg.error = pick([
              "131051 - número inválido",
              "131026 - fora do horário permitido",
              "131049 - limite de conta atingido",
              "136025 - template não aprovado",
            ]);
          } else {
            msg.status = "sent";
            msg.sentAt = now();
            scheduleDelivery(msg, number);
          }
          recomputeCounters();
          renderReport();
          persist();
        }

        state.campaign.running = false;
        $("#btnStart").disabled = false;
        $("#btnStop").disabled = true;
      };

      $("#btnStop").onclick = () => {
        state.campaign.running = false;
        $("#btnStart").disabled = false;
        $("#btnStop").disabled = true;
      };

      function scheduleDelivery(msg, number) {
        setTimeout(
          () => {
            if (Math.random() < 0.9) {
              msg.status = "delivered";
              msg.deliveredAt = now();
              recomputeCounters();
              renderReport();
              persist();
              setTimeout(
                () => {
                  if (Math.random() < 0.55) {
                    msg.status = "read";
                    msg.readAt = now();
                    recomputeCounters();
                    renderReport();
                    persist();
                    setTimeout(
                      () => {
                        if (Math.random() < 0.18) {
                          const reply = pick([
                            "Oi, recebi sim!",
                            "Ok, obrigado",
                            "Tem como me ligar?",
                            "Não tenho interesse",
                            "Pode me mandar mais infos?",
                            "Quanto custa?",
                            "SAIR",
                          ]);
                          msg.status = "replied";
                          msg.repliedAt = now();
                          msg.reply = reply;
                          const phone = msg.phone;
                          state.inbox[phone] ||= [];
                          state.inbox[phone].push({
                            dir: "outbound",
                            body: msg.body,
                            file: msg.file ? { ...msg.file } : null,
                            at: msg.sentAt,
                            numberId: msg.numberId,
                          });
                          state.inbox[phone].push({
                            dir: "inbound",
                            body: reply,
                            at: msg.repliedAt,
                            numberId: msg.numberId,
                          });
                          if (reply.toUpperCase() === "SAIR") {
                            const ct = state.contacts.find(
                              (c) => c.phone === phone,
                            );
                            if (ct) ct.optedIn = false;
                            renderContacts();
                          }
                          recomputeCounters();
                          renderReport();
                          renderInbox();
                          persist();
                        }
                      },
                      rand(2000, 15000),
                    );
                  }
                },
                rand(1500, 8000),
              );
            } else {
              msg.status = "failed";
              msg.error = "timeout - sem ACK do destinatário";
              recomputeCounters();
              renderReport();
              persist();
            }
          },
          rand(800, 4000),
        );
      }

      // ======================================================================
      // Relatório
      // ======================================================================
      function renderReport() {
        const tbody = $("#reportTable");
        if (state.messages.length === 0) {
          tbody.innerHTML = "";
          $("#reportEmpty").classList.remove("hidden");
          return;
        }
        $("#reportEmpty").classList.add("hidden");
        const rows = [...state.messages].reverse().slice(0, 300);
        tbody.innerHTML = rows
          .map((m) => {
            let attachCell = "—";
            if (m.file) {
              const kindLabel =
                m.file.kind === "image"
                  ? "IMG"
                  : m.file.kind === "video"
                    ? "VID"
                    : m.file.kind === "audio"
                      ? "MIC"
                      : "DOC";
              const dl = m.file.dataUrl
                ? `<a href="${m.file.dataUrl}" download="${escapeHtml(m.file.name)}" class="underline">${escapeHtml(m.file.name)}</a>`
                : escapeHtml(m.file.name);
              attachCell = `<span class="inline-flex items-center gap-1"><span class="font-semibold text-slate-600">${kindLabel}</span> ${dl}</span>`;
            }
            return `
      <tr class="border-t border-slate-100">
        <td class="px-3 py-2 font-mono text-xs">${escapeHtml(m.phone)}</td>
        <td class="px-3 py-2">${escapeHtml(m.name) || "—"}</td>
        <td class="px-3 py-2 text-xs">${attachCell}</td>
        <td class="px-3 py-2">${statusBadge(m.status)}</td>
        <td class="px-3 py-2 text-xs">${fmt(m.sentAt)}</td>
        <td class="px-3 py-2 text-xs">${fmt(m.deliveredAt)}</td>
        <td class="px-3 py-2 text-xs">${fmt(m.readAt)}</td>
        <td class="px-3 py-2 text-xs">${m.reply ? `<span class="text-emerald-700">${escapeHtml(m.reply)}</span>` : "—"}</td>
        <td class="px-3 py-2 text-xs text-red-600">${escapeHtml(m.error) || ""}</td>
      </tr>
    `;
          })
          .join("");
      }
      function statusBadge(s) {
        const map = {
          queued: "bg-slate-100 text-slate-700",
          sent: "bg-sky-100 text-sky-700",
          delivered: "bg-sky-100 text-sky-700",
          read: "bg-emerald-100 text-emerald-700",
          replied: "bg-green-100 text-green-800",
          failed: "bg-red-100 text-red-700",
        };
        return `<span class="inline-block px-2 py-0.5 rounded-full text-xs font-medium ${map[s] || map.queued}">${s}</span>`;
      }

      $("#btnExport").onclick = () => {
        if (state.messages.length === 0) {
          alert("Nenhuma mensagem para exportar.");
          return;
        }
        const header = [
          "phone",
          "name",
          "type",
          "attachment",
          "status",
          "sent_at",
          "delivered_at",
          "read_at",
          "replied_at",
          "error",
          "reply",
          "body",
        ];
        const esc = (v) => {
          if (v == null) return "";
          const s = String(v);
          if (/[",\n]/.test(s)) return `"${s.replace(/"/g, '""')}"`;
          return s;
        };
        const rows = state.messages.map((m) =>
          [
            m.phone,
            m.name,
            m.type,
            m.file ? `${m.file.name} (${formatSize(m.file.size || 0)})` : "",
            m.status,
            m.sentAt,
            m.deliveredAt,
            m.readAt,
            m.repliedAt,
            m.error,
            m.reply,
            m.body,
          ]
            .map(esc)
            .join(","),
        );
        const csv = "\uFEFF" + [header.join(","), ...rows].join("\n");
        const blob = new Blob([csv], { type: "text/csv;charset=utf-8" });
        const url = URL.createObjectURL(blob);
        const a = document.createElement("a");
        a.href = url;
        a.download = `disparo-${new Date().toISOString().slice(0, 16).replace(/[:T]/g, "-")}.csv`;
        a.click();
        URL.revokeObjectURL(url);
      };

      // ======================================================================
      // Inbox
      // ======================================================================
      function renderInbox() {
        const phones = Object.keys(state.inbox);
        const list = $("#inboxList");
        if (phones.length === 0) {
          list.innerHTML = "";
          $("#inboxEmpty").classList.remove("hidden");
          $("#inboxThread").innerHTML = "";
          $("#inboxHeader").textContent = "Selecione uma conversa";
          return;
        }
        $("#inboxEmpty").classList.add("hidden");
        phones.sort((a, b) => {
          const la = state.inbox[a].at(-1)?.at || "";
          const lb = state.inbox[b].at(-1)?.at || "";
          return lb.localeCompare(la);
        });
        list.innerHTML = phones
          .map((p) => {
            const last = state.inbox[p].at(-1);
            const contact = state.contacts.find((c) => c.phone === p);
            const active = p === state.activeInboxPhone;
            const preview = last?.file
              ? `[${last.file.kind || "arquivo"}] ${last.file.name}`
              : (last?.body ?? "");
            return `
      <li data-phone="${p}" class="cursor-pointer px-3 py-2 hover:bg-slate-50 ${active ? "bg-slate-100" : ""}">
        <div class="text-sm font-medium">${escapeHtml(contact?.name || p)}</div>
        <div class="text-xs text-slate-500 truncate">${last?.dir === "inbound" ? "← " : "→ "}${escapeHtml(preview)}</div>
      </li>
    `;
          })
          .join("");
        list.querySelectorAll("li").forEach(
          (li) =>
            (li.onclick = () => {
              state.activeInboxPhone = li.dataset.phone;
              renderInbox();
              renderThread();
            }),
        );
        if (!state.activeInboxPhone || !state.inbox[state.activeInboxPhone]) {
          state.activeInboxPhone = phones[0];
        }
        renderThread();
      }

      function renderThread() {
        const phone = state.activeInboxPhone;
        const box = $("#inboxThread");
        if (!phone || !state.inbox[phone]) {
          box.innerHTML = "";
          return;
        }
        const contact = state.contacts.find((c) => c.phone === phone);
        $("#inboxHeader").textContent =
          `${contact?.name || ""} ${phone}`.trim();
        box.innerHTML = state.inbox[phone]
          .map((m) => {
            const side = m.dir === "outbound" ? "justify-end" : "justify-start";
            const bg =
              m.dir === "outbound" ? "background:#DCF8C6" : "background:white";
            const att = m.file
              ? `<div class="mb-1">${renderAttachmentHTML(m.file, { maxW: "220px" })}</div>`
              : "";
            const text = m.body
              ? `<div class="whitespace-pre-wrap">${escapeHtml(m.body)}</div>`
              : "";
            return `
      <div class="flex ${side}">
        <div class="max-w-[80%] rounded-lg shadow-sm p-2 text-sm" style="${bg}">
          ${att}${text}
          <div class="text-[10px] text-slate-500 text-right mt-1">${fmt(m.at)}</div>
        </div>
      </div>
    `;
          })
          .join("");
        box.scrollTop = box.scrollHeight;
      }

      // Anexo inbox
      $("#inboxReplyFile").addEventListener("change", async (e) => {
        const file = e.target.files?.[0];
        if (!file) return;
        if (file.size > MAX_FILE_BYTES) {
          alert(
            `Arquivo muito grande (${formatSize(file.size)}). Limite: ${formatSize(MAX_FILE_BYTES)}.`,
          );
          e.target.value = "";
          return;
        }
        const dataUrl = await readFileAsDataURL(file);
        state.inboxReplyFile = {
          name: file.name,
          mime: file.type || "application/octet-stream",
          size: file.size,
          kind: inferKind(file.type),
          dataUrl,
        };
        const meta = $("#inboxReplyFileMeta");
        meta.classList.remove("hidden");
        meta.innerHTML = `anexado: <b>${escapeHtml(file.name)}</b> · ${escapeHtml(formatSize(file.size))} <button id="btnClearReplyFile" class="ml-2 underline text-red-600">remover</button>`;
        $("#btnClearReplyFile").onclick = () => {
          state.inboxReplyFile = null;
          $("#inboxReplyFile").value = "";
          meta.classList.add("hidden");
          meta.innerHTML = "";
        };
      });

      $("#inboxReplyForm").onsubmit = (e) => {
        e.preventDefault();
        const phone = state.activeInboxPhone;
        if (!phone) return;
        const txt = $("#inboxReplyInput").value.trim();
        const file = state.inboxReplyFile;
        if (!txt && !file) return;
        state.inbox[phone].push({
          dir: "outbound",
          body: txt,
          file: file ? { ...file } : null,
          at: now(),
        });
        $("#inboxReplyInput").value = "";
        $("#inboxReplyFile").value = "";
        state.inboxReplyFile = null;
        $("#inboxReplyFileMeta").classList.add("hidden");
        $("#inboxReplyFileMeta").innerHTML = "";
        renderThread();
        renderInbox();
        persist();
      };

      // ======================================================================
      // Reset
      // ======================================================================
      $("#btnReset").onclick = () => {
        if (!confirm("Apagar TUDO (números, contatos, mensagens, inbox)?"))
          return;
        localStorage.removeItem("wasim_state");
        location.reload();
      };

      // ======================================================================
      // Boot
      // ======================================================================
      restore();
      renderNumbers();
      renderContacts();
      renderReport();
      renderInbox();
      updatePreview();
      recomputeCounters();
        (async function init() {
        await loginAuto();
      })();
    </script>
  <body style="display:none;">
</html>
