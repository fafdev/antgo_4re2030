import { Head, Link, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as measuresIndex, update as measuresUpdate } from '@/routes/measures';

type Measure = {
    id: number;
    description: string;
};

type Props = {
    measure: Measure;
};

export default function MeasuresEdit({ measure }: Props) {
    const form = useForm({
        description: measure.description,
    });

    return (
        <>
            <Head title="Edit measure" />
            <div className="space-y-6 p-4">
                <Heading title="Edit measure" description="Update the selected measure." />
                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.put(measuresUpdate(measure.id));
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

                    <div className="flex gap-2">
                        <Button disabled={form.processing}>Save changes</Button>
                        <Button variant="secondary" asChild>
                            <Link href={measuresIndex()}>Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

MeasuresEdit.layout = {
    breadcrumbs: [
        {
            title: 'Measures',
            href: measuresIndex(),
        },
        {
            title: 'Edit',
            href: '#',
        },
    ],
};
