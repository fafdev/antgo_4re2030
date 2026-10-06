import { Head, Link, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as rolesIndex, update as rolesUpdate } from '@/routes/roles';

type Role = {
    id: number;
    name: string;
    description: string;
    inSites: boolean;
    inBuildings: boolean;
    inProperties: boolean;
    inContracts: boolean;
};

type Props = {
    role: Role;
};

export default function RolesEdit({ role }: Props) {
    const form = useForm({
        name: role.name,
        description: role.description,
        inSites: role.inSites,
        inBuildings: role.inBuildings,
        inProperties: role.inProperties,
        inContracts: role.inContracts,
    });

    const checkboxClassName = 'h-4 w-4 rounded border';

    return (
        <>
            <Head title={`Edit ${role.name}`} />

            <div className="space-y-6 p-4">
                <Heading title="Edit role" description="Update role data and scopes." />

                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.put(rolesUpdate(role.id));
                    }}
                    className="space-y-4 rounded-xl border p-4"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="name">Name</Label>
                        <Input
                            id="name"
                            name="name"
                            value={form.data.name}
                            onChange={(event) => form.setData('name', event.target.value)}
                            required
                        />
                        <InputError message={form.errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="description">Description</Label>
                        <Input
                            id="description"
                            name="description"
                            value={form.data.description}
                            onChange={(event) =>
                                form.setData('description', event.target.value)
                            }
                            required
                        />
                        <InputError message={form.errors.description} />
                    </div>

                    <div className="grid gap-3 sm:grid-cols-2">
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                className={checkboxClassName}
                                checked={form.data.inSites}
                                onChange={(event) =>
                                    form.setData('inSites', event.target.checked)
                                }
                            />
                            <span>Sites</span>
                        </label>

                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                className={checkboxClassName}
                                checked={form.data.inBuildings}
                                onChange={(event) =>
                                    form.setData('inBuildings', event.target.checked)
                                }
                            />
                            <span>Buildings</span>
                        </label>

                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                className={checkboxClassName}
                                checked={form.data.inProperties}
                                onChange={(event) =>
                                    form.setData('inProperties', event.target.checked)
                                }
                            />
                            <span>Properties</span>
                        </label>

                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                className={checkboxClassName}
                                checked={form.data.inContracts}
                                onChange={(event) =>
                                    form.setData('inContracts', event.target.checked)
                                }
                            />
                            <span>Contracts</span>
                        </label>
                    </div>

                    <div className="flex gap-2">
                        <Button disabled={form.processing}>Save changes</Button>
                        <Button variant="secondary" asChild>
                            <Link href={rolesIndex()}>Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

RolesEdit.layout = {
    breadcrumbs: [
        {
            title: 'Roles',
            href: rolesIndex(),
        },
        {
            title: 'Edit',
            href: '#',
        },
    ],
};
