<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storeantgo_re_adminRequest;
use App\Http\Requests\Updateantgo_re_adminRequest;
use App\Models\antgo_re_admin;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AntgoReAdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $admins = antgo_re_admin::query()
            ->orderBy('role')
            ->get()
            ->map(fn (antgo_re_admin $admin) => [
                'role' => $admin->role,
                'description' => $admin->description,
                'created_at' => $admin->created_at?->toDateTimeString(),
                'updated_at' => $admin->updated_at?->toDateTimeString(),
            ]);

        return Inertia::render('admins/index', [
            'admins' => $admins,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Storeantgo_re_adminRequest $request): RedirectResponse
    {
        antgo_re_admin::query()->create($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Admin role created.')]);

        return to_route('admins.index');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(antgo_re_admin $admin): Response
    {
        return Inertia::render('admins/edit', [
            'admin' => [
                'role' => $admin->role,
                'description' => $admin->description,
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updateantgo_re_adminRequest $request, antgo_re_admin $admin): RedirectResponse
    {
        $admin->update($request->validated());

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Admin role updated.')]);

        return to_route('admins.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(antgo_re_admin $admin): RedirectResponse
    {
        $admin->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Admin role deleted.')]);

        return to_route('admins.index');
    }
}
