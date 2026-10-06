import { Form, Head, Link } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import AntgoReEquipmentController from '@/actions/App/Http/Controllers/AntgoReEquipmentController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit as equipmentsEdit, index as equipmentsIndex } from '@/routes/equipments';

type Equipment = {
    id: number;
    description: string;
    inSites: boolean;
    inBuildings: boolean;
    inProperties: boolean;
    created_at?: string | null;
    updated_at?: string | null;
};

type Props = {
    equipments: Equipment[];
};

type ScopeFilter = 'all' | 'sites' | 'buildings' | 'properties';

const scopeLabels = [
    { key: 'inSites', label: 'Sites' },
    { key: 'inBuildings', label: 'Buildings' },
    { key: 'inProperties', label: 'Properties' },
] as const;

export default function EquipmentsIndex({ equipments }: Props) {
    const [searchTerm, setSearchTerm] = useState('');
    const [scopeFilter, setScopeFilter] = useState<ScopeFilter>('all');

    const filteredEquipments = useMemo(() => {
        const normalizedSearchTerm = searchTerm.trim().toLowerCase();

        return equipments.filter((equipment) => {
            const matchesSearch = normalizedSearchTerm === ''
                || equipment.description.toLowerCase().includes(normalizedSearchTerm);

            const matchesScope = (() => {
                if (scopeFilter === 'sites') {
                    return equipment.inSites;
                }

                if (scopeFilter === 'buildings') {
                    return equipment.inBuildings;
                }

                if (scopeFilter === 'properties') {
                    return equipment.inProperties;
                }

                return true;
            })();

            return matchesSearch && matchesScope;
        });
    }, [equipments, searchTerm, scopeFilter]);

    return (
        <>
            <Head title="Equipments" />

            <div className="space-y-8 p-4">
                <Heading
                    title="Equipments management"
                    description="Create and manage equipments, including CSV imports."
                />

                <p className="mt-2 text-sm font-medium text-foreground">
                    Showing {filteredEquipments.length} of {equipments.length} rows
                </p>

                <section className="grid gap-6 lg:grid-cols-2">
                    <Form
                        {...AntgoReEquipmentController.store.form()}
                        className="space-y-4 rounded-xl border p-4"
                        resetOnSuccess
                    >
                        {({ errors, processing }) => (
                            <>
                                <h2 className="text-base font-semibold">Create equipment</h2>
                                <div className="grid gap-2">
                                    <Label htmlFor="description">Description</Label>
                                    <Input id="description" name="description" required />
                                    <InputError message={errors.description} />
                                </div>
                                <div className="grid gap-3 sm:grid-cols-2">
                                    {scopeLabels.map((scope) => (
                                        <label key={scope.key} className="flex items-center gap-2 text-sm">
                                            <input
                                                type="checkbox"
                                                name={scope.key}
                                                value="1"
                                                className="h-4 w-4 rounded border"
                                            />
                                            <span>{scope.label}</span>
                                        </label>
                                    ))}
                                </div>
                                <Button disabled={processing}>Create equipment</Button>
                            </>
                        )}
                    </Form>

                    <Form
                        {...AntgoReEquipmentController.importCsv.form()}
                        className="space-y-4 rounded-xl border p-4"
                        resetOnSuccess
                    >
                        {({ errors, processing }) => (
                            <>
                                <h2 className="text-base font-semibold">Import CSV</h2>
                                <p className="text-sm text-muted-foreground">
                                    Required column: description. Optional: inSites, inBuildings,
                                    inProperties.
                                </p>
                                <a
                                    href={AntgoReEquipmentController.downloadTemplateCsv.url()}
                                    className="inline-flex h-9 items-center rounded-md border px-3 text-sm hover:bg-muted"
                                >
                                    Download template
                                </a>
                                <div className="grid gap-2">
                                    <Label htmlFor="csv_file">CSV file</Label>
                                    <Input
                                        id="csv_file"
                                        name="csv_file"
                                        type="file"
                                        accept=".csv,text/csv,text/plain"
                                        required
                                    />
                                    <InputError message={errors.csv_file} />
                                </div>
                                <Button disabled={processing}>Import equipments</Button>
                            </>
                        )}
                    </Form>
                </section>

                <section className="space-y-3 rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <h2 className="text-base font-semibold">Equipments</h2>
                        <div className="grid gap-3 md:grid-cols-2">
                            <div className="grid gap-1">
                                <Label htmlFor="search-equipments">Search</Label>
                                <Input
                                    id="search-equipments"
                                    placeholder="Search by description..."
                                    value={searchTerm}
                                    onChange={(event) => setSearchTerm(event.target.value)}
                                />
                            </div>
                            <div className="grid gap-1">
                                <Label htmlFor="scope-equipments">Scope</Label>
                                <select
                                    id="scope-equipments"
                                    className="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    value={scopeFilter}
                                    onChange={(event) => setScopeFilter(event.target.value as ScopeFilter)}
                                >
                                    <option value="all">All scopes</option>
                                    <option value="sites">Sites only</option>
                                    <option value="buildings">Buildings only</option>
                                    <option value="properties">Properties only</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[850px] text-left text-sm">
                            <thead>
                                <tr className="border-b">
                                    <th className="px-2 py-3 font-medium">Description</th>
                                    <th className="px-2 py-3 font-medium">Sites</th>
                                    <th className="px-2 py-3 font-medium">Buildings</th>
                                    <th className="px-2 py-3 font-medium">Properties</th>
                                    <th className="px-2 py-3 font-medium">Updated</th>
                                    <th className="px-2 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredEquipments.length === 0 ? (
                                    <tr>
                                        <td colSpan={6} className="px-2 py-6 text-center text-muted-foreground">
                                            No rows match the current filters.
                                        </td>
                                    </tr>
                                ) : (
                                    filteredEquipments.map((equipment) => (
                                        <tr key={equipment.id} className="border-b">
                                            <td className="px-2 py-3">{equipment.description}</td>
                                            <td className="px-2 py-3">{equipment.inSites ? 'Yes' : 'No'}</td>
                                            <td className="px-2 py-3">{equipment.inBuildings ? 'Yes' : 'No'}</td>
                                            <td className="px-2 py-3">{equipment.inProperties ? 'Yes' : 'No'}</td>
                                            <td className="px-2 py-3">
                                                {equipment.updated_at ?? equipment.created_at ?? '-'}
                                            </td>
                                            <td className="px-2 py-3">
                                                <div className="flex gap-2">
                                                    <Button size="sm" asChild>
                                                        <Link href={equipmentsEdit(equipment.id)}>Edit</Link>
                                                    </Button>
                                                    <Link
                                                        href={AntgoReEquipmentController.destroy(equipment.id)}
                                                        method="delete"
                                                        as="button"
                                                        className="inline-flex h-9 items-center rounded-md border px-3 text-sm hover:bg-muted"
                                                    >
                                                        Delete
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </>
    );
}

EquipmentsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Equipments',
            href: equipmentsIndex(),
        },
    ],
};
