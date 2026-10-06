import { Head, Link, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as datesIndex, update as datesUpdate } from '@/routes/dates';

type DateType = {
    code: string;
    name: string;
    description: string;
    inSites: boolean;
    inBuildings: boolean;
    inProperties: boolean;
    inContracts: boolean;
};

type Props = {
    date: DateType;
};

export default function DatesEdit({ date }: Props) {
    const form = useForm({
        code: date.code,
        name: date.name,
        description: date.description,
        inSites: date.inSites,
        inBuildings: date.inBuildings,
        inProperties: date.inProperties,
        inContracts: date.inContracts,
    });

    const checkboxClassName = 'h-4 w-4 rounded border';

    return (
        <>
            <Head title={`Edit ${date.code}`} />

            <div className="space-y-6 p-4">
                <Heading title="Edit date" description="Update date data and scopes." />

                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.put(datesUpdate(date.code));
                    }}
                    className="space-y-4 rounded-xl border p-4"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="code">Code</Label>
                        <Input
                            id="code"
                            name="code"
                            value={form.data.code}
                            onChange={(event) => form.setData('code', event.target.value)}
                            required
                        />
                        <InputError message={form.errors.code} />
                    </div>

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
                            <Link href={datesIndex()}>Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

DatesEdit.layout = {
    breadcrumbs: [
        {
            title: 'Dates',
            href: datesIndex(),
        },
        {
            title: 'Edit',
            href: '#',
        },
    ],
};
