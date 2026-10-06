import { Head, Link, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as infrastructuresIndex, update as infrastructuresUpdate } from '@/routes/infrastructures';

type Infrastructure = {
    id: number;
    description: string;
    inSites: boolean;
    inBuildings: boolean;
    inProperties: boolean;
};

type Props = {
    infrastructure: Infrastructure;
};

export default function InfrastructuresEdit({ infrastructure }: Props) {
    const form = useForm({
        description: infrastructure.description,
        inSites: infrastructure.inSites,
        inBuildings: infrastructure.inBuildings,
        inProperties: infrastructure.inProperties,
    });

    return (
        <>
            <Head title="Edit infrastructure" />
            <div className="space-y-6 p-4">
                <Heading title="Edit infrastructure" description="Update infrastructure data and scopes." />
                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.put(infrastructuresUpdate(infrastructure.id));
                    }}
                    className="space-y-4 rounded-xl border p-4"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="description">Description</Label>
                        <Input
                            id="description"
                            name="description"
                            value={form.data.description}
                            onChange={(event) => form.setData('description', event.target.value)}
                            required
                        />
                        <InputError message={form.errors.description} />
                    </div>

                    <div className="grid gap-3 sm:grid-cols-2">
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                className="h-4 w-4 rounded border"
                                checked={form.data.inSites}
                                onChange={(event) => form.setData('inSites', event.target.checked)}
                            />
                            <span>Sites</span>
                        </label>
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                className="h-4 w-4 rounded border"
                                checked={form.data.inBuildings}
                                onChange={(event) => form.setData('inBuildings', event.target.checked)}
                            />
                            <span>Buildings</span>
                        </label>
                        <label className="flex items-center gap-2 text-sm">
                            <input
                                type="checkbox"
                                className="h-4 w-4 rounded border"
                                checked={form.data.inProperties}
                                onChange={(event) => form.setData('inProperties', event.target.checked)}
                            />
                            <span>Properties</span>
                        </label>
                    </div>

                    <div className="flex gap-2">
                        <Button disabled={form.processing}>Save changes</Button>
                        <Button variant="secondary" asChild>
                            <Link href={infrastructuresIndex()}>Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

InfrastructuresEdit.layout = {
    breadcrumbs: [
        {
            title: 'Infrastructures',
            href: infrastructuresIndex(),
        },
        {
            title: 'Edit',
            href: '#',
        },
    ],
};
