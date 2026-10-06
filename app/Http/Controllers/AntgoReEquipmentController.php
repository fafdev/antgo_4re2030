<?php

namespace App\Http\Controllers;

use App\Http\Requests\Equipments\EquipmentImportCsvRequest;
use App\Http\Requests\Storeantgo_re_equipmentRequest;
use App\Http\Requests\Updateantgo_re_equipmentRequest;
use App\Models\antgo_re_equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use SplFileObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AntgoReEquipmentController extends Controller
{
    public function index(): Response
    {
        $equipments = antgo_re_equipment::query()
            ->orderBy('description')
            ->get()
            ->map(fn (antgo_re_equipment $equipment): array => [
                'id' => $equipment->id,
                'description' => $equipment->description,
                'inSites' => $equipment->inSites,
                'inBuildings' => $equipment->inBuildings,
                'inProperties' => $equipment->inProperties,
                'created_at' => $equipment->created_at?->toDateTimeString(),
                'updated_at' => $equipment->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('equipments/index', [
            'equipments' => $equipments,
        ]);
    }

    public function store(Storeantgo_re_equipmentRequest $request): RedirectResponse
    {
        antgo_re_equipment::query()->create($this->normalizePayload($request->validated()));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Equipment created.')]);

        return to_route('equipments.index');
    }

    public function edit(antgo_re_equipment $equipment): Response
    {
        return Inertia::render('equipments/edit', [
            'equipment' => [
                'id' => $equipment->id,
                'description' => $equipment->description,
                'inSites' => $equipment->inSites,
                'inBuildings' => $equipment->inBuildings,
                'inProperties' => $equipment->inProperties,
            ],
        ]);
    }

    public function update(Updateantgo_re_equipmentRequest $request, antgo_re_equipment $equipment): RedirectResponse
    {
        $equipment->update($this->normalizePayload($request->validated()));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Equipment updated.')]);

        return to_route('equipments.index');
    }

    public function destroy(antgo_re_equipment $equipment): RedirectResponse
    {
        $equipment->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Equipment deleted.')]);

        return to_route('equipments.index');
    }

    public function downloadTemplateCsv(): StreamedResponse
    {
        return response()->streamDownload(static function (): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                throw new RuntimeException('Unable to generate CSV template.');
            }

            fputcsv($handle, ['description', 'inSites', 'inBuildings', 'inProperties']);
            fputcsv($handle, ['HVAC system', '0', '1', '1']);
            fclose($handle);
        }, 'equipments-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importCsv(EquipmentImportCsvRequest $request): RedirectResponse
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

                if (antgo_re_equipment::query()->where('description', $payload['description'])->exists()) {
                    $skipped++;

                    continue;
                }

                antgo_re_equipment::query()->create($payload);
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

        return to_route('equipments.index');
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
