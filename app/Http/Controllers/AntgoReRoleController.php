<?php

namespace App\Http\Controllers;

use App\Http\Requests\Roles\RoleImportCsvRequest;
use App\Http\Requests\Storeantgo_re_roleRequest;
use App\Http\Requests\Updateantgo_re_roleRequest;
use App\Models\antgo_re_role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use SplFileObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AntgoReRoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $roles = antgo_re_role::query()
            ->orderBy('name')
            ->get()
            ->map(fn (antgo_re_role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'inSites' => $role->inSites,
                'inBuildings' => $role->inBuildings,
                'inProperties' => $role->inProperties,
                'inContracts' => $role->inContracts,
                'created_at' => $role->created_at?->toDateTimeString(),
                'updated_at' => $role->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('roles/index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Storeantgo_re_roleRequest $request): RedirectResponse
    {
        antgo_re_role::query()->create($this->normalizePayload($request->validated()));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role created.')]);

        return to_route('roles.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(antgo_re_role $role): Response
    {
        return Inertia::render('roles/edit', [
            'role' => [
                'id' => $role->id,
                'name' => $role->name,
                'description' => $role->description,
                'inSites' => $role->inSites,
                'inBuildings' => $role->inBuildings,
                'inProperties' => $role->inProperties,
                'inContracts' => $role->inContracts,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updateantgo_re_roleRequest $request, antgo_re_role $role): RedirectResponse
    {
        $role->update($this->normalizePayload($request->validated()));

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role updated.')]);

        return to_route('roles.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(antgo_re_role $role): RedirectResponse
    {
        $role->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Role deleted.')]);

        return to_route('roles.index');
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

            fputcsv($handle, ['name', 'description', 'inSites', 'inBuildings', 'inProperties', 'inContracts']);
            fputcsv($handle, ['Manager', 'Role for site and building scope', '1', '1', '0', '0']);
            fclose($handle);
        }, 'roles-template.csv', $headers);
    }

    /**
     * Import roles from a CSV file.
     */
    public function importCsv(RoleImportCsvRequest $request): RedirectResponse
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
                    $requiredHeaders = ['name', 'description'];

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

                if (antgo_re_role::query()->where('name', $payload['name'])->exists()) {
                    $skipped++;

                    continue;
                }

                antgo_re_role::query()->create($payload);
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

        return to_route('roles.index');
    }

    /**
     * @param  array{name: string, description: string, inSites?: bool, inBuildings?: bool, inProperties?: bool, inContracts?: bool}  $payload
     * @return array{name: string, description: string, inSites: bool, inBuildings: bool, inProperties: bool, inContracts: bool}
     */
    private function normalizePayload(array $payload): array
    {
        return [
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
     * @return array{name: string, description: string, inSites: bool, inBuildings: bool, inProperties: bool, inContracts: bool}
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
