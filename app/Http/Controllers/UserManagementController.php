<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\UserImportCsvRequest;
use App\Http\Requests\Users\UserStoreRequest;
use App\Http\Requests\Users\UserUpdateRequest;
use App\Models\antgo_re_admin;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use SplFileObject;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(): Response
    {
        $users = User::query()
            ->with('adminRole:role,description')
            ->orderBy('name')
            ->paginate(15)
            ->through(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'admin_role' => $user->admin_role,
                'admin_role_description' => $user->adminRole?->description,
                'created_at' => $user->created_at?->toDateTimeString(),
            ]);

        $adminRoles = antgo_re_admin::query()
            ->orderBy('role')
            ->get(['role', 'description']);

        return Inertia::render('users/index', [
            'users' => $users,
            'adminRoles' => $adminRoles,
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(UserStoreRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['admin_role'] = $data['admin_role'] ?: null;

        User::create($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User created successfully.')]);

        return to_route('users.index');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): Response
    {
        $adminRoles = antgo_re_admin::query()
            ->orderBy('role')
            ->get(['role', 'description']);

        return Inertia::render('users/edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'admin_role' => $user->admin_role,
            ],
            'adminRoles' => $adminRoles,
        ]);
    }

    /**
     * Update the specified user.
     */
    public function update(UserUpdateRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        $data['admin_role'] = $data['admin_role'] ?: null;

        if (($data['password'] ?? null) === null || $data['password'] === '') {
            unset($data['password']);
        }

        $user->update($data);

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User updated successfully.')]);

        return to_route('users.index');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $user->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('User deleted successfully.')]);

        return to_route('users.index');
    }

    /**
     * Import users from a CSV file.
     */
    public function importCsv(UserImportCsvRequest $request): RedirectResponse
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

                    $requiredHeaders = ['name', 'email', 'password'];

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

                $emailValidator = Validator::make($payload, [
                    'email' => ['required', 'email', 'max:255'],
                ]);

                if ($emailValidator->fails()) {
                    throw ValidationException::withMessages([
                        'csv_file' => __('Invalid data at CSV row :row: :message', [
                            'row' => $lineNumber,
                            'message' => $emailValidator->errors()->first(),
                        ]),
                    ]);
                }

                if (User::query()->where('email', $payload['email'])->exists()) {
                    $skipped++;

                    continue;
                }

                $validator = Validator::make($payload, [
                    'name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
                    'password' => ['required', Password::default()],
                    'admin_role' => ['nullable', 'string', Rule::exists('antgo_re_admins', 'role')],
                ]);

                if ($validator->fails()) {
                    throw ValidationException::withMessages([
                        'csv_file' => __('Invalid data at CSV row :row: :message', [
                            'row' => $lineNumber,
                            'message' => $validator->errors()->first(),
                        ]),
                    ]);
                }

                User::query()->create($payload);
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

        return to_route('users.index');
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, string>  $row
     * @return array{name: string, email: string, password: string, admin_role: ?string}
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

        $adminRole = trim((string) ($mappedRow['admin_role'] ?? ''));

        return [
            'name' => trim((string) ($mappedRow['name'] ?? '')),
            'email' => mb_strtolower(trim((string) ($mappedRow['email'] ?? ''))),
            'password' => trim((string) ($mappedRow['password'] ?? '')),
            'admin_role' => $adminRole !== '' ? $adminRole : null,
        ];
    }
}
