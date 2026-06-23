<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Throwable;

class ContactController extends Controller
{
    private function userId(Request $request): ?int
    {
        return optional($request->user())->id ?? auth()->id();
    }

    public function create()
    {
        return view('contacts.create');
    }

    public function index(Request $request)
    {
        $userId = $this->userId($request);

        if (!$userId) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $perPage = (int) $request->get('per_page', 50);

        if ($perPage < 10) {
            $perPage = 10;
        }

        if ($perPage > 200) {
            $perPage = 200;
        }

        $q = Contact::where('user_id', $userId)->with('phones');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                    ->orWhere('nome', 'like', "%{$search}%")
                    ->orWhere('cpf', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('telefone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('cidade', 'like', "%{$search}%");
            });
        }

        if ($request->filled('cidade')) {
            $q->where('cidade', 'like', '%' . trim($request->cidade) . '%');
        }

        if ($request->filled('estado')) {
            $estado = strtoupper(trim($request->estado));

            $q->where(function ($w) use ($estado) {
                $w->where('estado', $estado)
                    ->orWhere('uf', $estado);
            });
        }

        if ($request->filled('tag')) {
            $q->where('tag', 'like', '%' . trim($request->tag) . '%');
        }

        if ($request->filled('opt_in')) {
            $q->where('opt_in', (int) $request->opt_in);
        }

        if ($request->filled('ativo')) {
            $q->where('ativo', (int) $request->ativo);
        }

        if ($request->filled('has_phone')) {
            if ((int) $request->has_phone === 1) {
                $q->where(function ($w) {
                    $w->whereNotNull('phone')
                        ->where('phone', '<>', '')
                        ->orWhere(function ($w2) {
                            $w2->whereNotNull('telefone')
                                ->where('telefone', '<>', '');
                        });
                });
            }

            if ((int) $request->has_phone === 0) {
                $q->where(function ($w) {
                    $w->where(function ($w2) {
                        $w2->whereNull('phone')
                            ->orWhere('phone', '');
                    })->where(function ($w3) {
                        $w3->whereNull('telefone')
                            ->orWhere('telefone', '');
                    });
                });
            }
        }

        return $q->latest()->paginate($perPage);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'external_id' => ['nullable', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:14'],
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:255'],
            'ddd' => ['nullable', 'string', 'max:3'],
            'tipo_telefone' => ['nullable', 'string', 'max:30'],
            'sexo' => ['nullable', 'string', 'max:1'],
            'email' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'bairro' => ['nullable', 'string', 'max:255'],
            'cidade' => ['nullable', 'string', 'max:255'],
            'uf' => ['nullable', 'string', 'max:2'],
            'cep' => ['nullable', 'string', 'max:12'],
            'data_nascimento' => ['nullable', 'date'],
            'nome_mae' => ['nullable', 'string', 'max:255'],
            'renda' => ['nullable', 'numeric'],
            'titulo_eleitor' => ['nullable', 'string', 'max:255'],
            'data_inclusao' => ['nullable', 'date'],
            'opt_in' => ['nullable', 'boolean'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $userId = $this->userId($request);

        if (!$userId) {
            return response()->json(['message' => 'Não autenticado.'], 401);
        }

        $data['user_id'] = $userId;
        $data['opt_in'] = $data['opt_in'] ?? true;
        $data['ativo'] = $data['ativo'] ?? true;

        return Contact::updateOrCreate(
            [
                'phone' => $data['phone'],
                'user_id' => $userId,
            ],
            $data
        );
    }

public function importExcel(Request $request)
{
    $request->validate([
        'file' => ['required', 'file', 'mimes:xlsx,xls,csv,txt'],
    ]);

    @ini_set('memory_limit', '1024M');
    @set_time_limit(0);

    $file = $request->file('file')->getRealPath();

    $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
    $reader->setReadDataOnly(true);

    $spreadsheetInfo = $reader->listWorksheetInfo($file);
    $totalRows = $spreadsheetInfo[0]['totalRows'];

    $chunkSize = 500;
    $imported = 0;

    for ($startRow = 2; $startRow <= $totalRows; $startRow += $chunkSize) {
        $chunkFilter = new \App\Imports\ChunkReadFilter($startRow, $chunkSize);
        $reader->setReadFilter($chunkFilter);

        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getActiveSheet();

        foreach ($sheet->getRowIterator($startRow, min($startRow + $chunkSize - 1, $totalRows)) as $row) {
            $rowIndex = $row->getRowIndex();

            $nome = trim((string) $sheet->getCell("A{$rowIndex}")->getValue());
            $telefone = preg_replace('/\D/', '', (string) $sheet->getCell("B{$rowIndex}")->getValue());
            $email = trim((string) $sheet->getCell("C{$rowIndex}")->getValue());
            $cpf = preg_replace('/\D/', '', (string) $sheet->getCell("D{$rowIndex}")->getValue());
            $cidade = trim((string) $sheet->getCell("E{$rowIndex}")->getValue());
            $estado = trim((string) $sheet->getCell("F{$rowIndex}")->getValue());
            $tag = trim((string) $sheet->getCell("G{$rowIndex}")->getValue());

            if (!$nome && !$telefone) {
                continue;
            }

            \App\Models\Contact::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'telefone' => $telefone,
                ],
                [
                    'nome' => $nome ?: null,
                    'telefone' => $telefone ?: null,
                    'email' => $email ?: null,
                    'cpf' => $cpf ?: null,
                    'cidade' => $cidade ?: null,
                    'estado' => $estado ?: null,
                    'tag' => $tag ?: null,
                    'opt_in' => true,
                    'ativo' => true,
                ]
            );

            $imported++;
                }

                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet, $sheet);
                gc_collect_cycles();
            }

            return response()->json([
                'message' => "Importação concluída. {$imported} contatos processados.",
            ]);
        }

        public function update(Request $request, $id)
        {
            $userId = $this->userId($request);

            if (!$userId) {
                return response()->json(['message' => 'Não autenticado.'], 401);
            }

            $contact = Contact::where('user_id', $userId)
                ->where('id', $id)
                ->first();

            if (!$contact) {
                return response()->json(['message' => 'Contato não encontrado.'], 404);
                    }

            $data = $request->validate([
                'name' => ['nullable', 'string', 'max:255'],
                'cpf' => ['nullable', 'string', 'max:14'],
                'email' => ['nullable', 'email', 'max:255'],
                'phone' => ['nullable', 'string', 'max:30'],
                'ddd' => ['nullable', 'string', 'max:5'],
                'tipo_telefone' => ['nullable', 'string', 'max:50'],
                'cidade' => ['nullable', 'string', 'max:255'],
                'uf' => ['nullable', 'string', 'max:2'],
                'estado' => ['nullable', 'string', 'max:2'],
                'bairro' => ['nullable', 'string', 'max:255'],
                'address' => ['nullable', 'string', 'max:255'],
                'cep' => ['nullable', 'string', 'max:12'],
                'tag' => ['nullable', 'string', 'max:255'],
                'opt_in' => ['nullable', 'boolean'],
                'ativo' => ['nullable', 'boolean'],
            ]);

            $cpf = isset($data['cpf']) ? preg_replace('/\D/', '', $data['cpf']) : null;
            $ddd = isset($data['ddd']) ? preg_replace('/\D/', '', $data['ddd']) : null;
            $phone = isset($data['phone']) ? preg_replace('/\D/', '', $data['phone']) : null;

            if ($cpf) {
                $exists = Contact::where('user_id', $userId)
                    ->where('cpf', $cpf)
                    ->where('id', '!=', $contact->id)
                    ->exists();

                if ($exists) {
                    return response()->json([
                        'message' => 'Já existe outro contato com este CPF.'
                    ], 422);
                        }
                    }

            $uf = $data['uf'] ?? $data['estado'] ?? $contact->uf;

            $contact->update([
                'name' => $data['name'] ?? $contact->name,
                'cpf' => $cpf ?: null,
                'email' => $data['email'] ?? null,
                'ddd' => $ddd,
                'phone' => $phone ?: $contact->phone,
                'tipo_telefone' => $data['tipo_telefone'] ?? null,
                'cidade' => $data['cidade'] ?? null,
                'uf' => $uf,
                'estado' => $data['estado'] ?? $uf,
                'bairro' => $data['bairro'] ?? null,
                'address' => $data['address'] ?? null,
                'cep' => $data['cep'] ?? null,
                'tag' => $data['tag'] ?? null,
                'opt_in' => $data['opt_in'] ?? $contact->opt_in,
                'ativo' => $data['ativo'] ?? $contact->ativo,
            ]);

            if ($phone) {
                $contact->phones()->updateOrCreate(
                    [
                        'ddd' => $ddd,
                        'telefone' => $phone,
                    ],
                    [
                        'tipo_telefone' => $data['tipo_telefone'] ?? null,
                        'whatsapp' => true,
                        'principal' => true,
                    ]
                );

                $contact->phones()
                    ->where('telefone', '!=', $phone)
                    ->update(['principal' => false]);
            }

            return response()->json([
                'message' => 'Contato atualizado com sucesso.',
                'contact' => $contact->load('phones'),
            ]);
        }


        public function destroy(Request $request, $id)
        {
            $userId = optional($request->user())->id ?? auth()->id();

            if (!$userId) {
                return response()->json(['message' => 'Não autenticado.'], 401);
            }

            $contact = Contact::where('user_id', $userId)->findOrFail($id);

            $contact->delete();

            return response()->json([
                'message' => 'Contato removido com sucesso.',
            ]);
        }

    private function cell(array $row, array $map, string $key): ?string
    {
        if (!isset($map[$key])) {
            return null;
        }

        $value = $row[$map[$key]] ?? null;

        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function normalizeHeader($value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);
        $value = mb_strtolower($value, 'UTF-8');

        $value = str_replace(
            ['á','à','ã','â','ä','é','è','ê','ë','í','ì','î','ï','ó','ò','õ','ô','ö','ú','ù','û','ü','ç'],
            ['a','a','a','a','a','e','e','e','e','i','i','i','i','o','o','o','o','o','u','u','u','u','c'],
            $value
        );

        $value = preg_replace('/[^a-z0-9]+/', '_', $value);
        $value = trim($value, '_');

        return $value ?: null;
    }

    private function onlyDigits(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $value);

        return $digits ?: null;
    }

    private function formatPhone(?string $ddd, string $telefone): string
    {
        $telefone = $this->onlyDigits($telefone);

        if (!$telefone) {
            return '';
        }

        if (str_starts_with($telefone, '55')) {
            return $telefone;
        }

        if ($ddd && !str_starts_with($telefone, $ddd)) {
            return '55' . $ddd . $telefone;
        }

        return '55' . $telefone;
    }

    private function parseMoney(?string $value): ?float
    {
        if (!$value) {
            return null;
        }

        $value = str_replace(['R$', ' '], '', $value);
        $value = str_replace('.', '', $value);
        $value = str_replace(',', '.', $value);

        return is_numeric($value) ? (float) $value : null;
    }

    private function parseDate(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)
                )->format('Y-m-d');
            }

            return Carbon::parse($value)->format('Y-m-d');
        } catch (Throwable $e) {
            return null;
        }
    }

    private function parseDateTime(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(
                    \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value)
                )->format('Y-m-d H:i:s');
            }

            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (Throwable $e) {
            return null;
        }
    }
}