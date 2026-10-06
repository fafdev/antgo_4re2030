<?php

namespace App\Http\Controllers;

use App\Http\Requests\Infrastructures\InfrastructureImportCsvRequest;
use App\Http\Requests\Storeantgo_re_infrastructureRequest;
use App\Http\Requests\Updateantgo_re_infrastructureRequest;
use App\Models\antgo_re_infrastructure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use SplFileObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AntgoReInfrastructureController extends Controller
{
    public function index(): Response
    {
        $infrastructures = antgo_re_infrastructure::query()
            ->orderBy('description')
            ->get()
            ->map(fn (antgo_re_infrastructure $infrastructure): array => [
                'id' => $infrastructure->id,
                'description' => $infrastructure->description,
                'inSites' => $infrastructure->inSites,
                'inBuildings' => $infrastructure->inBuildings,
                'inProperties' => $infrastructure->inProperties,
                'created_at' => $infrastructure->created_at?->toDateTimeString(),
                'updated_at' => $infrastructure->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('infrastructures/index', [
            'infrastructures' => $infrastructures,
        ]);
    }

    public function store(Storeantgo_re_infrastructureRequest $request): RedirectResponse
    {
        antgo_re_infrastructure::query()->create($this->normalizePayload($request->validated()));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Infrastructure created.')]);

        return to_route('infrastructures.index');
    }

    public function edit(antgo_re_infrastructure $infrastructure): Response
    {
        return Inertia::render('infrastructures/edit', [
            'infrastructure' => [
                'id' => $infrastructure->id,
                'description' => $infrastructure->description,
                'inSites' => $infrastructure->inSites,
                'inBuildings' => $infrastructure->inBuildings,
                'inProperties' => $infrastructure->inProperties,
            ],
        ]);
    }

    public function update(Updateantgo_re_infrastructureRequest $request, antgo_re_infrastructure $infrastructure): RedirectResponse
    {
        $infrastructure->update($this->normalizePayload($request->validated()));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Infrastructure updated.')]);

        return to_route('infrastructures.index');
    }

    public function destroy(antgo_re_infrastructure $infrastructure): RedirectResponse
    {
        $infrastructure->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Infrastructure deleted.')]);

        return to_route('infrastructures.index');
    }

    public function downloadTemplateCsv(): StreamedResponse
    {
        return response()->streamDownload(static function (): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                throw new RuntimeException('Unable to generate CSV template.');
            }

            fputcsv($handle, ['description', 'inSites', 'inBuildings', 'inProperties']);
            fputcsv($handle, ['Water network', '1', '1', '1']);
            fclose($handle);
        }, 'infrastructures-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importCsv(InfrastructureImportCsvRequest $request): RedirectResponse
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

                    if (! in_array('description', $headers, true)) {
                        throw ValidationException::withMessages([
                            'csv_file' => __("CSV header is missing ':header' column.", ['header' => 'description']),
                        ]);
                    }

                    continue;
                }

                $payload = $this->mapRowToPayload($headers, $row);
                $validator = Validator::make($payload, [
                    'description' => ['required', 'string', 'max:255'],
                    'inSites' => ['required', 'boolean'],
                    'inBuildings' => ['required', 'boolean'],
                    'inProperties' => ['required', 'boolean'],
                ]);

                if ($validator->fails()) {
                    throw ValidationException::withMessages([
                        'csv_file' => __('Invalid data at CSV row :row: :message', [
                            'row' => $lineNumber,
                            'message' => $validator->errors()->first(),
                        ]),
                    ]);
                }

                if (antgo_re_infrastructure::query()->where('description', $payload['description'])->exists()) {
                    $skipped++;

                    continue;
                }

                antgo_re_infrastructure::query()->create($payload);
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

        return to_route('infrastructures.index');
    }

    /**
     * @param  array{description: string, inSites?: bool, inBuildings?: bool, inProperties?: bool}  $payload
     * @return array{description: string, inSites: bool, inBuildings: bool, inProperties: bool}
     */
    private function normalizePayload(array $payload): array
    {
        return [
            'description' => trim((string) $payload['description']),
            'inSites' => (bool) ($payload['inSites'] ?? false),
            'inBuildings' => (bool) ($payload['inBuildings'] ?? false),
            'inProperties' => (bool) ($payload['inProperties'] ?? false),
        ];
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string>  $row
     * @return array{description: string, inSites: bool, inBuildings: bool, inProperties: bool}
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
            'description' => trim((string) ($mappedRow['description'] ?? '')),
            'inSites' => $this->toBoolean($mappedRow['insites'] ?? null),
            'inBuildings' => $this->toBoolean($mappedRow['inbuildings'] ?? null),
            'inProperties' => $this->toBoolean($mappedRow['inproperties'] ?? null),
        ];
    }

    private function toBoolean(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }

        return in_array(mb_strtolower(trim((string) $value)), ['1', 'true', 'yes', 'y', 'si', 'sí'], true);
    }
}
