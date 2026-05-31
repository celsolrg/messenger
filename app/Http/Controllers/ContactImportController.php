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
