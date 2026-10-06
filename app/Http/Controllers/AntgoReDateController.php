<?php

namespace App\Http\Controllers;

use App\Http\Requests\Dates\DateImportCsvRequest;
use App\Http\Requests\Storeantgo_re_dateRequest;
use App\Http\Requests\Updateantgo_re_dateRequest;
use App\Models\antgo_re_date;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use SplFileObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AntgoReDateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $dates = antgo_re_date::query()
            ->orderBy('code')
            ->get()
            ->map(fn (antgo_re_date $date): array => [
                'code' => $date->code,
                'name' => $date->name,
                'description' => $date->description,
                'inSites' => $date->inSites,
                'inBuildings' => $date->inBuildings,
                'inProperties' => $date->inProperties,
                'inContracts' => $date->inContracts,
                'created_at' => $date->created_at?->toDateTimeString(),
                'updated_at' => $date->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('dates/index', [
            'dates' => $dates,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Storeantgo_re_dateRequest $request): RedirectResponse
    {
        antgo_re_date::query()->create($this->normalizePayload($request->validated()));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Date created.')]);

        return to_route('dates.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(antgo_re_date $date): Response
    {
        return Inertia::render('dates/edit', [
            'date' => [
                'code' => $date->code,
                'name' => $date->name,
                'description' => $date->description,
                'inSites' => $date->inSites,
                'inBuildings' => $date->inBuildings,
                'inProperties' => $date->inProperties,
                'inContracts' => $date->inContracts,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updateantgo_re_dateRequest $request, antgo_re_date $date): RedirectResponse
    {
        $payload = $this->normalizePayload($request->validated());

        if ($payload['code'] !== $date->code) {
            antgo_re_date::query()
                ->where('code', $date->code)
                ->update($payload);
        } else {
            $date->update($payload);
        }

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Date updated.')]);

        return to_route('dates.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(antgo_re_date $date): RedirectResponse
    {
        $date->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Date deleted.')]);

        return to_route('dates.index');
    }

    public function downloadTemplateCsv(): StreamedResponse
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ];

        return response()->streamDownload(static function (): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                throw new RuntimeException('Unable to generate CSV template.');
            }

            fputcsv($handle, ['code', 'name', 'description', 'inSites', 'inBuildings', 'inProperties', 'inContracts']);
            fputcsv($handle, ['REVIEW_DATE', 'Review date', 'Review milestone date', '1', '0', '1', '0']);
            fclose($handle);
        }, 'dates-template.csv', $headers);
    }

    /**
     * Import dates from a CSV file.
     */
    public function importCsv(DateImportCsvRequest $request): RedirectResponse
    {
        $file = new SplFileObject($request->file('csv_file')->getPathname());
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);

        $headers = null;
        $created = 0;
        $skipped = 0;
        $lineNumber = 0;

        DB::transaction(function () use ($file, &$headers, &$created, &$skipped, &$lineNumber): void {
            foreach ($file as $row) {
                $lineNumber++;

                if ($row === false || $row === [null]) {
                    continue;
                }

                $row = array_map(static fn ($value): string => trim((string) $value), $row);

                if ($headers === null) {
                    $headers = array_map(static fn (string $value): string => mb_strtolower($value), $row);
                    $requiredHeaders = ['code', 'name', 'description'];

                    foreach ($requiredHeaders as $header) {
                        if (! in_array($header, $headers, true)) {
                            throw ValidationException::withMessages([
                                'csv_file' => __("CSV header is missing ':header' column.", ['header' => $header]),
                            ]);
                        }
                    }

                    continue;
                }

                $payload = $this->mapRowToPayload($headers, $row);

                $validator = Validator::make($payload, [
                    'code' => ['required', 'string', 'max:100', 'regex:/^[a-zA-Z0-9_\-]+$/'],
                    'name' => ['required', 'string', 'max:255'],
                    'description' => ['required', 'string', 'max:255'],
                    'inSites' => ['required', 'boolean'],
                    'inBuildings' => ['required', 'boolean'],
                    'inProperties' => ['required', 'boolean'],
                    'inContracts' => ['required', 'boolean'],
                ]);

                if ($validator->fails()) {
                    throw ValidationException::withMessages([
                        'csv_file' => __('Invalid data at CSV row :row: :message', [
                            'row' => $lineNumber,
                            'message' => $validator->errors()->first(),
                        ]),
                    ]);
                }

                if (antgo_re_date::query()->where('code', $payload['code'])->exists()) {
                    $skipped++;

                    continue;
                }

                antgo_re_date::query()->create($payload);
                $created++;
            }

            if ($headers === null) {
                throw ValidationException::withMessages([
                    'csv_file' => __('The CSV file is empty.'),
                ]);
            }
        });

        Inertia::flash('toast', [
            'type' => 'success',
            'message' => __('Import completed. Created: :created. Skipped: :skipped.', [
                'created' => $created,
                'skipped' => $skipped,
            ]),
        ]);

        return to_route('dates.index');
    }

    /**
     * @param  array{code: string, name: string, description: string, inSites?: bool, inBuildings?: bool, inProperties?: bool, inContracts?: bool}  $payload
     * @return array{code: string, name: string, description: string, inSites: bool, inBuildings: bool, inProperties: bool, inContracts: bool}
     */
    private function normalizePayload(array $payload): array
    {
        return [
            'code' => trim((string) $payload['code']),
            'name' => trim((string) $payload['name']),
            'description' => trim((string) $payload['description']),
            'inSites' => (bool) ($payload['inSites'] ?? false),
            'inBuildings' => (bool) ($payload['inBuildings'] ?? false),
            'inProperties' => (bool) ($payload['inProperties'] ?? false),
            'inContracts' => (bool) ($payload['inContracts'] ?? false),
        ];
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string>  $row
     * @return array{code: string, name: string, description: string, inSites: bool, inBuildings: bool, inProperties: bool, inContracts: bool}
     */
    private function mapRowToPayload(array $headers, array $row): array
    {
        $values = array_pad($row, count($headers), null);
        $values = array_slice($values, 0, count($headers));
        $mappedRow = array_combine($headers, $values);

        if ($mappedRow === false) {
            throw ValidationException::withMessages([
                'csv_file' => __('Unable to parse CSV row.'),
            ]);
        }

        return [
            'code' => trim((string) ($mappedRow['code'] ?? '')),
            'name' => trim((string) ($mappedRow['name'] ?? '')),
            'description' => trim((string) ($mappedRow['description'] ?? '')),
            'inSites' => $this->toBoolean($mappedRow['insites'] ?? null),
            'inBuildings' => $this->toBoolean($mappedRow['inbuildings'] ?? null),
            'inProperties' => $this->toBoolean($mappedRow['inproperties'] ?? null),
            'inContracts' => $this->toBoolean($mappedRow['incontracts'] ?? null),
        ];
    }

    private function toBoolean(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        $normalized = mb_strtolower(trim((string) $value));

        return in_array($normalized, ['1', 'true', 'yes', 'y', 'si', 'sí'], true);
    }
}
