import { Form, Head, Link } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import AntgoReMeasureController from '@/actions/App/Http/Controllers/AntgoReMeasureController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { edit as measuresEdit, index as measuresIndex } from '@/routes/measures';

type Measure = {
    id: number;
    description: string;
    created_at?: string | null;
    updated_at?: string | null;
};

type Props = {
    measures: Measure[];
};

export default function MeasuresIndex({ measures }: Props) {
    const [searchTerm, setSearchTerm] = useState('');

    const filteredMeasures = useMemo(() => {
        const normalizedSearchTerm = searchTerm.trim().toLowerCase();

        return measures.filter((measure) => {
            if (normalizedSearchTerm === '') {
                return true;
            }

            return measure.description.toLowerCase().includes(normalizedSearchTerm);
        });
    }, [measures, searchTerm]);

    return (
        <>
            <Head title="Measures" />

            <div className="space-y-8 p-4">
                <Heading
                    title="Measures management"
                    description="Create and manage measures, including CSV imports."
                />

                <p className="mt-2 text-sm font-medium text-foreground">
                    Showing {filteredMeasures.length} of {measures.length} rows
                </p>

                <section className="grid gap-6 lg:grid-cols-2">
                    <Form
                        {...AntgoReMeasureController.store.form()}
                        className="space-y-4 rounded-xl border p-4"
                        resetOnSuccess
                    >
                        {({ errors, processing }) => (
                            <>
                                <h2 className="text-base font-semibold">Create measure</h2>
                                <div className="grid gap-2">
                                    <Label htmlFor="description">Description</Label>
                                    <Input id="description" name="description" required />
                                    <InputError message={errors.description} />
                                </div>
                                <Button disabled={processing}>Create measure</Button>
                            </>
                        )}
                    </Form>

                    <Form
                        {...AntgoReMeasureController.importCsv.form()}
                        className="space-y-4 rounded-xl border p-4"
                        resetOnSuccess
                    >
                        {({ errors, processing }) => (
                            <>
                                <h2 className="text-base font-semibold">Import CSV</h2>
                                <p className="text-sm text-muted-foreground">
                                    Required column: description.
                                </p>
                                <a
                                    href={AntgoReMeasureController.downloadTemplateCsv.url()}
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
                                <Button disabled={processing}>Import measures</Button>
                            </>
                        )}
                    </Form>
                </section>

                <section className="space-y-3 rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <h2 className="text-base font-semibold">Measures</h2>
                        <div className="grid gap-1">
                            <Label htmlFor="search-measures">Search</Label>
                            <Input
                                id="search-measures"
                                placeholder="Search by description..."
                                value={searchTerm}
                                onChange={(event) => setSearchTerm(event.target.value)}
                            />
                        </div>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[700px] text-left text-sm">
                            <thead>
                                <tr className="border-b">
                                    <th className="px-2 py-3 font-medium">Description</th>
                                    <th className="px-2 py-3 font-medium">Updated</th>
                                    <th className="px-2 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredMeasures.length === 0 ? (
                                    <tr>
                                        <td colSpan={3} className="px-2 py-6 text-center text-muted-foreground">
                                            No rows match the current filters.
                                        </td>
                                    </tr>
                                ) : (
                                    filteredMeasures.map((measure) => (
                                        <tr key={measure.id} className="border-b">
                                            <td className="px-2 py-3">{measure.description}</td>
                                            <td className="px-2 py-3">
                                                {measure.updated_at ?? measure.created_at ?? '-'}
                                            </td>
                                            <td className="px-2 py-3">
                                                <div className="flex gap-2">
                                                    <Button size="sm" asChild>
                                                        <Link href={measuresEdit(measure.id)}>Edit</Link>
                                                    </Button>
                                                    <Link
                                                        href={AntgoReMeasureController.destroy(measure.id)}
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

MeasuresIndex.layout = {
    breadcrumbs: [
        {
            title: 'Measures',
            href: measuresIndex(),
        },
    ],
};
