import { Form, Head, Link } from '@inertiajs/react';
import AntgoReRoleController from '@/actions/App/Http/Controllers/AntgoReRoleController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit as rolesEdit, index as rolesIndex } from '@/routes/roles';
import { ShieldCheck } from "lucide-react";

type Role = {
    id: number;
    name: string;
    description: string;
    inSites: boolean;
    inBuildings: boolean;
    inProperties: boolean;
    inContracts: boolean;
    created_at?: string | null;
    updated_at?: string | null;
};

type Props = {
    roles: Role[];
};

const scopeLabels = [
    { key: 'inSites', label: 'Sites' },
    { key: 'inBuildings', label: 'Buildings' },
    { key: 'inProperties', label: 'Properties' },
    { key: 'inContracts', label: 'Contracts' },
] as const;

export default function RolesIndex({ roles }: Props) {
    return (
        <>
            <Head title="Roles" />

            <div className="space-y-8 p-4">
                <div className="flex items-center gap-3">
                    <ShieldCheck className="h-14 w-14 shrink-0" aria-hidden="true" />
                    <Heading
                        title="Roles management"
                        description="Create and manage auxiliary roles, including CSV imports."
                    />
                </div>

                <p className="mt-2 text-sm font-medium text-foreground">
                    Total rows: {roles.length}
                </p>

                <section className="grid gap-6 lg:grid-cols-2">
                    <Form
                        {...AntgoReRoleController.store.form()}
                        className="space-y-4 rounded-xl border p-4"
                        resetOnSuccess
                    >
                        {({ errors, processing }) => (
                            <>
                                <h2 className="text-base font-semibold">Create role</h2>

                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input id="name" name="name" required />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="description">Description</Label>
                                    <Input id="description" name="description" required />
                                    <InputError message={errors.description} />
                                </div>

                                <div className="grid gap-3 sm:grid-cols-2">
                                    {scopeLabels.map((scope) => (
                                        <label
                                            key={scope.key}
                                            className="flex items-center gap-2 text-sm"
                                        >
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

                                <Button disabled={processing}>Create role</Button>
                            </>
                        )}
                    </Form>

                    <Form
                        {...AntgoReRoleController.importCsv.form()}
                        className="space-y-4 rounded-xl border p-4"
                        resetOnSuccess
                    >
                        {({ errors, processing }) => (
                            <>
                                <h2 className="text-base font-semibold">Import CSV</h2>
                                <p className="text-sm text-muted-foreground">
                                    Required columns: name, description. Optional: inSites,
                                    inBuildings, inProperties, inContracts.
                                </p>
                                <a
                                    href={AntgoReRoleController.downloadTemplateCsv.url()}
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

                                <Button disabled={processing}>Import roles</Button>
                            </>
                        )}
                    </Form>
                </section>

                <section className="space-y-3 rounded-xl border p-4">
                    <h2 className="text-base font-semibold">Roles</h2>

                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[900px] text-left text-sm">
                            <thead>
                                <tr className="border-b">
                                    <th className="px-2 py-3 font-medium">Name</th>
                                    <th className="px-2 py-3 font-medium">Description</th>
                                    <th className="px-2 py-3 font-medium">Sites</th>
                                    <th className="px-2 py-3 font-medium">Buildings</th>
                                    <th className="px-2 py-3 font-medium">Properties</th>
                                    <th className="px-2 py-3 font-medium">Contracts</th>
                                    <th className="px-2 py-3 font-medium">Updated</th>
                                    <th className="px-2 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {roles.map((role) => (
                                    <tr key={role.id} className="border-b">
                                        <td className="px-2 py-3">{role.name}</td>
                                        <td className="px-2 py-3">{role.description}</td>
                                        <td className="px-2 py-3">{role.inSites ? 'Yes' : 'No'}</td>
                                        <td className="px-2 py-3">{role.inBuildings ? 'Yes' : 'No'}</td>
                                        <td className="px-2 py-3">{role.inProperties ? 'Yes' : 'No'}</td>
                                        <td className="px-2 py-3">{role.inContracts ? 'Yes' : 'No'}</td>
                                        <td className="px-2 py-3">
                                            {role.updated_at ?? role.created_at ?? '-'}
                                        </td>
                                        <td className="px-2 py-3">
                                            <div className="flex gap-2">
                                                <Button size="sm" asChild>
                                                    <Link href={rolesEdit(role.id)}>
                                                        Edit
                                                    </Link>
                                                </Button>
                                                <Link
                                                    href={AntgoReRoleController.destroy(
                                                        role.id,
                                                    )}
                                                    method="delete"
                                                    as="button"
                                                    className="inline-flex h-9 items-center rounded-md border px-3 text-sm hover:bg-muted"
                                                >
                                                    Delete
                                                </Link>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </>
    );
}

RolesIndex.layout = {
    breadcrumbs: [
        {
            title: 'Roles',
            href: rolesIndex(),
        },
    ],
};
