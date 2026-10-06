import { Form, Head, Link } from "@inertiajs/react";
import AntgoReDateController from "@/actions/App/Http/Controllers/AntgoReDateController";
import Heading from "@/components/heading";
import InputError from "@/components/input-error";
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Label } from "@/components/ui/label";
import { edit as datesEdit, index as datesIndex } from "@/routes/dates";
import { Calendar } from "lucide-react";

type DateType = {
    code: string;
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
    dates: DateType[];
};

const scopeLabels = [
    { key: "inSites", label: "Sites" },
    { key: "inBuildings", label: "Buildings" },
    { key: "inProperties", label: "Properties" },
    { key: "inContracts", label: "Contracts" },
] as const;

export default function DatesIndex({ dates }: Props) {
    return (
        <>
            <Head title="Dates" />

            <div className="space-y-8 p-4">
                <div className="flex items-center gap-3">
                    <Calendar
                        className="h-14 w-14 shrink-0"
                        aria-hidden="true"
                    />
                    <Heading
                        title="Dates management"
                        description="Create and manage date records, including CSV imports."
                    />
                </div>

                <p className="mt-2 text-sm font-medium text-foreground">
                    Total rows: {dates.length}
                </p>

                <section className="grid gap-6 lg:grid-cols-2">
                    <Form
                        {...AntgoReDateController.store.form()}
                        className="space-y-4 rounded-xl border p-4"
                        resetOnSuccess
                    >
                        {({ errors, processing }) => (
                            <>
                                <h2 className="text-base font-semibold">
                                    Create date
                                </h2>

                                <div className="grid gap-2">
                                    <Label htmlFor="code">Code</Label>
                                    <Input
                                        id="code"
                                        name="code"
                                        placeholder="MY_DATE_CODE"
                                        required
                                    />
                                    <InputError message={errors.code} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input id="name" name="name" required />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="description">
                                        Description
                                    </Label>
                                    <Input
                                        id="description"
                                        name="description"
                                        required
                                    />
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

                                <Button disabled={processing}>
                                    Create date
                                </Button>
                            </>
                        )}
                    </Form>

                    <Form
                        {...AntgoReDateController.importCsv.form()}
                        className="space-y-4 rounded-xl border p-4"
                        resetOnSuccess
                    >
                        {({ errors, processing }) => (
                            <>
                                <h2 className="text-base font-semibold">
                                    Import CSV
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    Required columns: code, name, description.
                                    Optional: inSites, inBuildings,
                                    inProperties, inContracts.
                                </p>
                                <a
                                    href={AntgoReDateController.downloadTemplateCsv.url()}
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

                                <Button disabled={processing}>
                                    Import dates
                                </Button>
                            </>
                        )}
                    </Form>
                </section>

                <section className="space-y-3 rounded-xl border p-4">
                    <h2 className="text-base font-semibold">Dates</h2>

                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[980px] text-left text-sm">
                            <thead>
                                <tr className="border-b">
                                    <th className="px-2 py-3 font-medium">
                                        Code
                                    </th>
                                    <th className="px-2 py-3 font-medium">
                                        Name
                                    </th>
                                    <th className="px-2 py-3 font-medium">
                                        Description
                                    </th>
                                    <th className="px-2 py-3 font-medium">
                                        Sites
                                    </th>
                                    <th className="px-2 py-3 font-medium">
                                        Buildings
                                    </th>
                                    <th className="px-2 py-3 font-medium">
                                        Properties
                                    </th>
                                    <th className="px-2 py-3 font-medium">
                                        Contracts
                                    </th>
                                    <th className="px-2 py-3 font-medium">
                                        Updated
                                    </th>
                                    <th className="px-2 py-3 font-medium">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {dates.map((dateRow) => (
                                    <tr key={dateRow.code} className="border-b">
                                        <td className="px-2 py-3">
                                            {dateRow.code}
                                        </td>
                                        <td className="px-2 py-3">
                                            {dateRow.name}
                                        </td>
                                        <td className="px-2 py-3">
                                            {dateRow.description}
                                        </td>
                                        <td className="px-2 py-3">
                                            {dateRow.inSites ? "Yes" : "No"}
                                        </td>
                                        <td className="px-2 py-3">
                                            {dateRow.inBuildings ? "Yes" : "No"}
                                        </td>
                                        <td className="px-2 py-3">
                                            {dateRow.inProperties
                                                ? "Yes"
                                                : "No"}
                                        </td>
                                        <td className="px-2 py-3">
                                            {dateRow.inContracts ? "Yes" : "No"}
                                        </td>
                                        <td className="px-2 py-3">
                                            {dateRow.updated_at ??
                                                dateRow.created_at ??
                                                "-"}
                                        </td>
                                        <td className="px-2 py-3">
                                            <div className="flex gap-2">
                                                <Button size="sm" asChild>
                                                    <Link
                                                        href={datesEdit(
                                                            dateRow.code,
                                                        )}
                                                    >
                                                        Edit
                                                    </Link>
                                                </Button>
                                                <Link
                                                    href={AntgoReDateController.destroy(
                                                        dateRow.code,
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

DatesIndex.layout = {
    breadcrumbs: [
        {
            title: "Dates",
            href: datesIndex(),
        },
    ],
};
