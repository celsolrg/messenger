#!/bin/bash
set -e

APP_DIR="${1:-/var/www/messenger/whatsapp-system}"
cd "$APP_DIR"

echo "==> Ajustando módulo de contatos em: $APP_DIR"

php artisan make:model Contact -m || true
php artisan make:controller ContactController || true
php artisan make:controller ContactImportController || true

MIGRATION_FILE=$(ls -1 database/migrations/*create_contacts_table.php | tail -n 1)
cat > "$MIGRATION_FILE" <<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('external_id')->nullable()->index();
            $table->string('cpf', 14)->nullable()->unique();
            $table->string('nome')->index();
            $table->string('ddd', 3)->nullable();
            $table->string('telefone', 20)->nullable()->index();
            $table->string('tipo_telefone', 30)->nullable();
            $table->string('sexo', 1)->nullable();
            $table->string('bairro')->nullable();
            $table->string('cidade')->nullable()->index();
            $table->string('uf', 2)->nullable()->index();
            $table->string('cep', 12)->nullable();
            $table->string('logradouro_titulo')->nullable();
            $table->string('logradouro_nome')->nullable();
            $table->string('logradouro_numero')->nullable();
            $table->date('data_nascimento')->nullable();
            $table->string('nome_mae')->nullable();
            $table->string('estado_civil')->nullable();
            $table->string('cbo')->nullable();
            $table->decimal('renda', 12, 2)->nullable();
            $table->unsignedInteger('faixa_renda_id')->nullable();
            $table->string('titulo_eleitor')->nullable();
            $table->dateTime('data_inclusao')->nullable();
            $table->boolean('ativo')->default(true)->index();
            $table->timestamps();

            $table->unique(['ddd', 'telefone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
PHP

cat > app/Models/Contact.php <<'PHP'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id', 'cpf', 'nome', 'ddd', 'telefone', 'tipo_telefone', 'sexo',
        'bairro', 'cidade', 'uf', 'cep', 'logradouro_titulo', 'logradouro_nome',
        'logradouro_numero', 'data_nascimento', 'nome_mae', 'estado_civil', 'cbo',
        'renda', 'faixa_renda_id', 'titulo_eleitor', 'data_inclusao', 'ativo',
    ];

    protected $casts = [
        'data_nascimento' => 'date',
        'data_inclusao' => 'datetime',
        'renda' => 'decimal:2',
        'ativo' => 'boolean',
    ];
}
PHP

cat > app/Http/Controllers/ContactController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $q = Contact::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $q->where(function ($w) use ($search) {
                $w->where('nome', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%")
                  ->orWhere('telefone', 'like', "%{$search}%")
                  ->orWhere('cidade', 'like', "%{$search}%");
            });
        }

        $contacts = $q->latest()->paginate(30)->withQueryString();
        return view('contacts.index', compact('contacts'));
    }

    public function create()
    {
        return view('contacts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'ddd' => ['nullable', 'string', 'max:3'],
            'telefone' => ['nullable', 'string', 'max:20'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', 'string', 'max:2'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cep' => ['nullable', 'string', 'max:12'],
        ]);

        Contact::create($data);
        return redirect()->route('contacts.index')->with('success', 'Contato cadastrado com sucesso.');
    }
}
PHP

cat > app/Http/Controllers/ContactImportController.php <<'PHP'
<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ContactImportController extends Controller
{
    public function form()
    {
        return view('contacts.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'arquivo' => ['required', 'file', 'mimes:xlsx,xls,csv,txt'],
        ]);

        $path = $request->file('arquivo')->getRealPath();
        $spreadsheet = IOFactory::load($path);
        $rows = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);
        $header = array_shift($rows);

        $map = [];
        foreach ($header as $col => $name) {
            $map[strtoupper(trim((string) $name))] = $col;
        }

        $created = 0;
        $updated = 0;
        $ignored = 0;

        foreach ($rows as $row) {
            $nome = $this->val($row, $map, 'NOME');
            $cpf = $this->digits($this->val($row, $map, 'CPF'));
            $ddd = $this->digits($this->val($row, $map, 'DDD'));
            $telefone = $this->digits($this->val($row, $map, 'TELEFONE'));

            if (!$nome || (!$cpf && !$telefone)) {
                $ignored++;
                continue;
            }

            $payload = [
                'external_id' => $this->val($row, $map, 'CONTATOS_ID'),
                'cpf' => $cpf ?: null,
                'nome' => $nome,
                'ddd' => $ddd ?: null,
                'telefone' => $telefone ?: null,
                'tipo_telefone' => $this->val($row, $map, 'TIPO_TELEFONE'),
                'sexo' => $this->val($row, $map, 'SEXO'),
                'bairro' => $this->nullIf($this->val($row, $map, 'BAIRRO')),
                'cidade' => $this->nullIf($this->val($row, $map, 'CIDADE')),
                'uf' => $this->nullIf($this->val($row, $map, 'UF')),
                'cep' => $this->digits($this->val($row, $map, 'CEP')) ?: null,
                'logradouro_titulo' => $this->nullIf($this->val($row, $map, 'LOGR_TITULO')),
                'logradouro_nome' => $this->nullIf($this->val($row, $map, 'LOGR_NOME')),
                'logradouro_numero' => $this->nullIf($this->val($row, $map, 'LOGR_NUMERO')),
                'data_nascimento' => $this->date($this->val($row, $map, 'NASC')),
                'nome_mae' => $this->nullIf($this->val($row, $map, 'NOME_MAE')),
                'estado_civil' => $this->nullIf($this->val($row, $map, 'ESTCIV')),
                'cbo' => $this->nullIf($this->val($row, $map, 'CBO')),
                'renda' => $this->money($this->val($row, $map, 'RENDA')),
                'faixa_renda_id' => $this->intOrNull($this->val($row, $map, 'FAIXA_RENDA_ID')),
                'titulo_eleitor' => $this->nullIf($this->val($row, $map, 'TITULO_ELEITOR')),
                'data_inclusao' => $this->datetime($this->val($row, $map, 'DT_INCLUSAO')),
                'ativo' => true,
            ];

            $key = $cpf ? ['cpf' => $cpf] : ['ddd' => $ddd, 'telefone' => $telefone];
            $contact = Contact::updateOrCreate($key, $payload);
            $contact->wasRecentlyCreated ? $created++ : $updated++;
        }

        return back()->with('success', "Importação concluída. Criados: {$created}. Atualizados: {$updated}. Ignorados: {$ignored}.");
    }

    private function val(array $row, array $map, string $key): ?string
    {
        $value = $map[$key] ?? null;
        return $value ? trim((string) ($row[$value] ?? '')) : null;
    }

    private function nullIf(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' || strtoupper($value) === 'NULL' ? null : $value;
    }

    private function digits(?string $value): string
    {
        return preg_replace('/\D+/', '', (string) $this->nullIf($value));
    }

    private function date(?string $value): ?string
    {
        $value = $this->nullIf($value);
        return $value ? Carbon::parse($value)->format('Y-m-d') : null;
    }

    private function datetime(?string $value): ?string
    {
        $value = $this->nullIf($value);
        return $value ? Carbon::parse($value)->format('Y-m-d H:i:s') : null;
    }

    private function money(?string $value): ?float
    {
        $value = $this->nullIf($value);
        return $value === null ? null : (float) str_replace(',', '.', $value);
    }

    private function intOrNull(?string $value): ?int
    {
        $value = $this->nullIf($value);
        return $value === null ? null : (int) $value;
    }
}
PHP

mkdir -p resources/views/contacts
cat > resources/views/contacts/index.blade.php <<'BLADE'
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
BLADE

cat > resources/views/contacts/create.blade.php <<'BLADE'
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
BLADE

cat > resources/views/contacts/import.blade.php <<'BLADE'
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
BLADE

if ! grep -q "ContactController" routes/web.php; then
cat >> routes/web.php <<'PHP'

use App\Http\Controllers\ContactController;
use App\Http\Controllers\ContactImportController;

Route::middleware(['auth'])->group(function () {
    Route::get('/contatos', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contatos/novo', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contatos', [ContactController::class, 'store'])->name('contacts.store');
    Route::get('/contatos/importar', [ContactImportController::class, 'form'])->name('contacts.import.form');
    Route::post('/contatos/importar', [ContactImportController::class, 'import'])->name('contacts.import');
});
PHP
fi

composer require phpoffice/phpspreadsheet
php artisan migrate
php artisan optimize:clear

echo "==> Finalizado. Acesse: /contatos"
