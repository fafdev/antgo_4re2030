<?php

namespace App\Http\Controllers;

use App\Http\Requests\Measures\MeasureImportCsvRequest;
use App\Http\Requests\Storeantgo_re_measureRequest;
use App\Http\Requests\Updateantgo_re_measureRequest;
use App\Models\antgo_re_measure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use SplFileObject;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AntgoReMeasureController extends Controller
{
    public function index(): Response
    {
        $measures = antgo_re_measure::query()
            ->orderBy('description')
            ->get()
            ->map(fn (antgo_re_measure $measure): array => [
                'id' => $measure->id,
                'description' => $measure->description,
                'created_at' => $measure->created_at?->toDateTimeString(),
                'updated_at' => $measure->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('measures/index', [
            'measures' => $measures,
        ]);
    }

    public function store(Storeantgo_re_measureRequest $request): RedirectResponse
    {
        antgo_re_measure::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Measure created.')]);

        return to_route('measures.index');
    }

    public function edit(antgo_re_measure $measure): Response
    {
        return Inertia::render('measures/edit', [
            'measure' => [
                'id' => $measure->id,
                'description' => $measure->description,
            ],
        ]);
    }

    public function update(Updateantgo_re_measureRequest $request, antgo_re_measure $measure): RedirectResponse
    {
        $measure->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Measure updated.')]);

        return to_route('measures.index');
    }

    public function destroy(antgo_re_measure $measure): RedirectResponse
    {
        $measure->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Measure deleted.')]);

        return to_route('measures.index');
    }

    public function downloadTemplateCsv(): StreamedResponse
    {
        return response()->streamDownload(static function (): void {
            $handle = fopen('php://output', 'wb');

            if ($handle === false) {
                throw new RuntimeException('Unable to generate CSV template.');
            }

            fputcsv($handle, ['description']);
            fputcsv($handle, ['Square meter']);
            fclose($handle);
        }, 'measures-template.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function importCsv(MeasureImportCsvRequest $request): RedirectResponse
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
                ]);

                if ($validator->fails()) {
                    throw ValidationException::withMessages([
                        'csv_file' => __('Invalid data at CSV row :row: :message', [
                            'row' => $lineNumber,
                            'message' => $validator->errors()->first(),
                        ]),
                    ]);
                }

                if (antgo_re_measure::query()->where('description', $payload['description'])->exists()) {
                    $skipped++;

                    continue;
                }

                antgo_re_measure::query()->create($payload);
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

        return to_route('measures.index');
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string>  $row
     * @return array{description: string}
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
        ];
    }
}
