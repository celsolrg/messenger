<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Throwable;

class ContactController extends Controller
{
    public function index(Request $request)
    {
        $q = Contact::where('user_id', auth()->id());

        if ($request->filled('search')) {
            $search = $request->search;

            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('cpf', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('cidade', 'like', "%{$search}%");
            });
        }

        return $q->latest()->get();
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

        $data['user_id'] = auth()->id();
        $data['opt_in'] = $data['opt_in'] ?? true;
        $data['ativo'] = $data['ativo'] ?? true;

        return Contact::updateOrCreate(
            [
                'phone' => $data['phone'],
                'user_id' => auth()->id(),
            ],
            $data
        );
    }

    public function importExcel(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getRealPath());
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (count($rows) < 2) {
            return response()->json([
                'message' => 'Planilha vazia ou sem dados.',
            ], 400);
        }

        $header = array_shift($rows);

        $map = [];
        foreach ($header as $col => $name) {
            $key = $this->normalizeHeader($name);
            if ($key) {
                $map[$key] = $col;
            }
        }

        $created = 0;
        $updated = 0;
        $ignored = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            try {
                $externalId = $this->cell($row, $map, 'contatos_id');
                $cpf = $this->onlyDigits($this->cell($row, $map, 'cpf'));
                $name = $this->cell($row, $map, 'nome');
                $ddd = $this->onlyDigits($this->cell($row, $map, 'ddd'));
                $telefone = $this->onlyDigits($this->cell($row, $map, 'telefone'));

                if (!$telefone) {
                    $ignored++;
                    continue;
                }

                $phone = $this->formatPhone($ddd, $telefone);

                $payload = [
                    'external_id' => $externalId,
                    'cpf' => $cpf ?: null,
                    'name' => $name ?: null,
                    'phone' => $phone,
                    'ddd' => $ddd ?: null,
                    'tipo_telefone' => $this->cell($row, $map, 'tipo_telefone'),
                    'sexo' => $this->cell($row, $map, 'sexo'),
                    'bairro' => $this->cell($row, $map, 'bairro'),
                    'cidade' => $this->cell($row, $map, 'cidade'),
                    'uf' => $this->cell($row, $map, 'uf'),
                    'cep' => $this->onlyDigits($this->cell($row, $map, 'cep')),
                    'data_nascimento' => $this->parseDate($this->cell($row, $map, 'nasc')),
                    'nome_mae' => $this->cell($row, $map, 'nome_mae'),
                    'renda' => $this->parseMoney($this->cell($row, $map, 'renda')),
                    'titulo_eleitor' => $this->cell($row, $map, 'titulo_eleitor'),
                    'data_inclusao' => $this->parseDateTime($this->cell($row, $map, 'dt_inclusao')),
                    'opt_in' => true,
                    'ativo' => true,
                    'user_id' => auth()->id(),
                ];

                $contact = Contact::where('phone', $phone)
                    ->where('user_id', auth()->id())
                    ->first();

                if ($contact) {
                    $contact->update($payload);
                    $updated++;
                } else {
                    Contact::create($payload);
                    $created++;
                }

            } catch (Throwable $e) {
                $errors[] = [
                    'linha' => $index + 2,
                    'erro' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Importação concluída.',
            'created' => $created,
            'updated' => $updated,
            'ignored' => $ignored,
            'errors' => $errors,
        ]);
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::where('user_id', auth()->id())->findOrFail($id);

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'opt_in' => ['nullable', 'boolean'],
            'ativo' => ['nullable', 'boolean'],
        ]);

        $contact->update($data);

        return $contact;
    }

    public function destroy($id)
    {
        $contact = Contact::where('user_id', auth()->id())->findOrFail($id);
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
