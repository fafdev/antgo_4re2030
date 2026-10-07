import { Form, Head, Link, useForm } from '@inertiajs/react';
import { useMemo } from 'react';
import AntgoReContactController from '@/actions/App/Http/Controllers/AntgoReContactController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { detectSpanishTaxIdType, normalizeSpanishTaxId } from '@/lib/spanish-tax-id';
import { edit as contactsEdit, index as contactsIndex } from '@/routes/contacts';

type Contact = {
    id: string;
    code: string;
    type: 'Persona' | 'Empresa';
    taxId: string;
    formatedName: string;
    name: string | null;
    middleName: string | null;
    lastName: string | null;
    companyName: string | null;
    gender: 'Hombre' | 'Mujer' | 'Other' | null;
    birthDate: string | null;
    email: string | null;
    phone: string | null;
    addresses?: Address[];
    created_at?: string | null;
    updated_at?: string | null;
};

type Address = {
    id?: number;
    street: string;
    number: string;
    building: string;
    floor: string;
    door: string;
    city: string;
    postal_code: string;
    country: string;
    is_primary: boolean;
};

type Props = {
    contacts: Contact[];
};

export default function ContactsIndex({ contacts }: Props) {
    const createForm = useForm({
        taxId: '',
        formatedName: '',
        name: '',
        middleName: '',
        lastName: '',
        companyName: '',
        gender: '',
        birthDate: '',
        email: '',
        phone: '',
        addresses: [
            {
                street: '',
                number: '',
                building: '',
                floor: '',
                door: '',
                city: '',
                postal_code: '',
                country: '',
                is_primary: true,
            },
        ] as Address[],
        search: '',
        typeFilter: 'all',
    });

    const detectedType = detectSpanishTaxIdType(createForm.data.taxId);
    const isEmpresa = detectedType === 'Empresa';
    const isPersona = detectedType === 'Persona';
    const showCompanyFields = !isPersona;
    const showPersonFields = !isEmpresa;

    const filteredContacts = useMemo(() => {
        const normalizedSearch = createForm.data.search.trim().toLowerCase();

        return contacts.filter((contact) => {
            const matchesType = createForm.data.typeFilter === 'all' || contact.type === createForm.data.typeFilter;

            if (normalizedSearch === '') {
                return matchesType;
            }

            const matchesSearch = [
                contact.code,
                contact.type,
                contact.taxId,
                contact.formatedName,
                contact.name ?? '',
                contact.middleName ?? '',
                contact.lastName ?? '',
                contact.companyName ?? '',
                contact.email ?? '',
                contact.phone ?? '',
            ].some((value) => value.toLowerCase().includes(normalizedSearch));

            return matchesType && matchesSearch;
        });
    }, [contacts, createForm.data.search, createForm.data.typeFilter]);

    const updateTaxId = (rawTaxId: string) => {
        const taxId = normalizeSpanishTaxId(rawTaxId);
        const taxIdType = detectSpanishTaxIdType(taxId);

        createForm.setData('taxId', taxId);

        if (taxIdType === 'Empresa') {
            createForm.setData('name', '');
            createForm.setData('middleName', '');
            createForm.setData('lastName', '');
            createForm.setData('gender', '');
            createForm.setData('birthDate', '');
            createForm.setData('formatedName', createForm.data.companyName.trim());

            return;
        }

        if (taxIdType === 'Persona') {
            createForm.setData('companyName', '');
            createForm.setData(
                'formatedName',
                [createForm.data.name, createForm.data.middleName, createForm.data.lastName]
                    .filter((value) => value.trim() !== '')
                    .join(' ')
                    .trim(),
            );
        }
    };

    const updatePersonNameParts = (nextValues: { name?: string; middleName?: string; lastName?: string }) => {
        createForm.setData(
            'formatedName',
            [nextValues.name ?? createForm.data.name, nextValues.middleName ?? createForm.data.middleName, nextValues.lastName ?? createForm.data.lastName]
                .filter((value) => value.trim() !== '')
                .join(' ')
                .trim(),
        );
    };

    const updateCompanyName = (companyName: string) => {
        createForm.setData('companyName', companyName);
        createForm.setData('formatedName', companyName.trim());
    };

    const hasAddressValues = (address: Address) =>
        [address.street, address.number, address.building, address.floor, address.door, address.city, address.postal_code, address.country]
            .some((value) => value.trim() !== '');

    const addAddress = () => {
        createForm.setData('addresses', [
            ...createForm.data.addresses,
            {
                street: '',
                number: '',
                building: '',
                floor: '',
                door: '',
                city: '',
                postal_code: '',
                country: '',
                is_primary: createForm.data.addresses.length === 0,
            },
        ]);
    };

    const removeAddress = (index: number) => {
        const nextAddresses = createForm.data.addresses.filter((_, currentIndex) => currentIndex !== index);

        if (nextAddresses.length > 0 && !nextAddresses.some((address) => address.is_primary)) {
            nextAddresses[0] = { ...nextAddresses[0], is_primary: true };
        }

        createForm.setData('addresses', nextAddresses);
    };

    const setAddressField = (index: number, field: keyof Address, value: string) => {
        createForm.setData(
            'addresses',
            createForm.data.addresses.map((address, currentIndex) =>
                currentIndex === index ? { ...address, [field]: value } : address,
            ),
        );
    };

    const setPrimaryAddress = (index: number) => {
        createForm.setData(
            'addresses',
            createForm.data.addresses.map((address, currentIndex) => ({
                ...address,
                is_primary: currentIndex === index,
            })),
        );
    };

    return (
        <>
            <Head title="Contacts" />

            <div className="space-y-8 p-4">
                <Heading
                    title="Contacts management"
                    description="Create and manage contacts, including CSV imports and local search."
                />

                <p className="mt-2 text-sm font-medium text-foreground">
                    Showing {filteredContacts.length} of {contacts.length} rows
                </p>

                <section className="grid gap-6 lg:grid-cols-2">
                    <form
                        onSubmit={(event) => {
                            event.preventDefault();
                            createForm.transform((data) => ({
                                ...data,
                                addresses: data.addresses.filter((address) =>
                                    hasAddressValues(address),
                                ),
                            }));
                            createForm.post(AntgoReContactController.store(), {
                                onSuccess: () => {
                                    createForm.reset();
                                    createForm.setData('addresses', [
                                        {
                                            street: '',
                                            number: '',
                                            building: '',
                                            floor: '',
                                            door: '',
                                            city: '',
                                            postal_code: '',
                                            country: '',
                                            is_primary: true,
                                        },
                                    ]);
                                },
                            });
                        }}
                        className="space-y-4 rounded-xl border p-4"
                    >
                        <h2 className="text-base font-semibold">Create contact</h2>

                        <p className="text-sm text-muted-foreground">
                            Código se asigna automáticamente.
                        </p>

                        <div className="grid gap-2">
                            <Label htmlFor="taxId">CIF / NIF / NIE</Label>
                            <Input
                                id="taxId"
                                name="taxId"
                                placeholder="12345678Z / X1234567L / A58818501"
                                value={createForm.data.taxId}
                                onChange={(event) => updateTaxId(event.target.value)}
                                required
                            />
                            <InputError message={createForm.errors.taxId} />
                        </div>

                        {showCompanyFields && (
                            <div className="grid gap-2">
                                <Label htmlFor="companyName">Company name</Label>
                                <Input
                                    id="companyName"
                                    name="companyName"
                                    value={createForm.data.companyName}
                                    onChange={(event) => updateCompanyName(event.target.value)}
                                    required
                                />
                                <InputError message={createForm.errors.companyName} />
                            </div>
                        )}

                        {showPersonFields && (
                            <>
                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input
                                        id="name"
                                        name="name"
                                        value={createForm.data.name}
                                        onChange={(event) => {
                                            createForm.setData('name', event.target.value);
                                            updatePersonNameParts({ name: event.target.value });
                                        }}
                                        required={isPersona}
                                    />
                                    <InputError message={createForm.errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="middleName">Middle name</Label>
                                    <Input
                                        id="middleName"
                                        name="middleName"
                                        value={createForm.data.middleName}
                                        onChange={(event) => {
                                            createForm.setData('middleName', event.target.value);
                                            updatePersonNameParts({ middleName: event.target.value });
                                        }}
                                    />
                                    <InputError message={createForm.errors.middleName} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="lastName">Last name</Label>
                                    <Input
                                        id="lastName"
                                        name="lastName"
                                        value={createForm.data.lastName}
                                        onChange={(event) => {
                                            createForm.setData('lastName', event.target.value);
                                            updatePersonNameParts({ lastName: event.target.value });
                                        }}
                                        required={isPersona}
                                    />
                                    <InputError message={createForm.errors.lastName} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="gender">Gender</Label>
                                    <select
                                        id="gender"
                                        name="gender"
                                        className="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                        value={createForm.data.gender}
                                        onChange={(event) => createForm.setData('gender', event.target.value)}
                                    >
                                        <option value="">No gender</option>
                                        <option value="Hombre">Hombre</option>
                                        <option value="Mujer">Mujer</option>
                                        <option value="Other">Other</option>
                                    </select>
                                    <InputError message={createForm.errors.gender} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="birthDate">Birth date</Label>
                                    <Input
                                        id="birthDate"
                                        name="birthDate"
                                        type="date"
                                        value={createForm.data.birthDate}
                                        onChange={(event) => createForm.setData('birthDate', event.target.value)}
                                    />
                                    <InputError message={createForm.errors.birthDate} />
                                </div>
                            </>
                        )}

                        <div className="grid gap-2">
                            <Label>Formatted name</Label>
                            <p className="min-h-10 rounded-md border border-input bg-muted/30 px-3 py-2 text-sm">
                                {createForm.data.formatedName || '-'}
                            </p>
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email">Email</Label>
                            <Input
                                id="email"
                                name="email"
                                type="email"
                                value={createForm.data.email}
                                onChange={(event) => createForm.setData('email', event.target.value)}
                            />
                            <InputError message={createForm.errors.email} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="phone">Phone</Label>
                            <Input
                                id="phone"
                                name="phone"
                                value={createForm.data.phone}
                                onChange={(event) => createForm.setData('phone', event.target.value)}
                            />
                            <InputError message={createForm.errors.phone} />
                        </div>

                        <div className="space-y-3 rounded-lg border p-3">
                            <div className="flex items-center justify-between">
                                <h3 className="text-sm font-semibold">Addresses</h3>
                                <Button type="button" variant="secondary" size="sm" onClick={addAddress}>
                                    Add address
                                </Button>
                            </div>

                            {createForm.data.addresses.map((address, index) => (
                                <div key={index} className="space-y-3 rounded-md border p-3">
                                    <div className="grid gap-2 md:grid-cols-2">
                                        <div className="grid gap-1">
                                            <Label>Street</Label>
                                            <Input
                                                value={address.street}
                                                onChange={(event) => setAddressField(index, 'street', event.target.value)}
                                            />
                                        </div>
                                        <div className="grid gap-1">
                                            <Label>City</Label>
                                            <Input
                                                value={address.city}
                                                onChange={(event) => setAddressField(index, 'city', event.target.value)}
                                            />
                                        </div>
                                        <div className="grid gap-1">
                                            <Label>Number</Label>
                                            <Input
                                                value={address.number}
                                                onChange={(event) => setAddressField(index, 'number', event.target.value)}
                                            />
                                        </div>
                                        <div className="grid gap-1">
                                            <Label>Building</Label>
                                            <Input
                                                value={address.building}
                                                onChange={(event) => setAddressField(index, 'building', event.target.value)}
                                            />
                                        </div>
                                        <div className="grid gap-1">
                                            <Label>Floor</Label>
                                            <Input
                                                value={address.floor}
                                                onChange={(event) => setAddressField(index, 'floor', event.target.value)}
                                            />
                                        </div>
                                        <div className="grid gap-1">
                                            <Label>Door</Label>
                                            <Input
                                                value={address.door}
                                                onChange={(event) => setAddressField(index, 'door', event.target.value)}
                                            />
                                        </div>
                                        <div className="grid gap-1">
                                            <Label>Postal code</Label>
                                            <Input
                                                value={address.postal_code}
                                                onChange={(event) => setAddressField(index, 'postal_code', event.target.value)}
                                            />
                                        </div>
                                        <div className="grid gap-1">
                                            <Label>Country</Label>
                                            <Input
                                                value={address.country}
                                                onChange={(event) => setAddressField(index, 'country', event.target.value)}
                                            />
                                        </div>
                                        <label className="flex items-center gap-2 text-sm md:col-span-2">
                                            <input
                                                type="radio"
                                                name="primary-address-create"
                                                checked={address.is_primary}
                                                onChange={() => setPrimaryAddress(index)}
                                                className="h-4 w-4"
                                            />
                                            <span>Primary address</span>
                                        </label>
                                    </div>

                                    {createForm.data.addresses.length > 1 && (
                                        <Button
                                            type="button"
                                            variant="outline"
                                            size="sm"
                                            onClick={() => removeAddress(index)}
                                        >
                                            Remove address
                                        </Button>
                                    )}
                                </div>
                            ))}

                            <InputError message={createForm.errors.addresses} />
                        </div>

                        <Button disabled={createForm.processing}>Create contact</Button>
                    </form>

                    <Form
                        {...AntgoReContactController.importCsv.form()}
                        className="space-y-4 rounded-xl border p-4"
                        resetOnSuccess
                    >
                        {({ errors, processing }) => (
                            <>
                                <h2 className="text-base font-semibold">Import CSV</h2>
                                <p className="text-sm text-muted-foreground">
                                    Required columns: taxId (CIF/NIF/NIE). Optional: name,
                                    middleName, lastName, companyName, gender, birthDate, email,
                                    phone.
                                </p>
                                <a
                                    href={AntgoReContactController.downloadTemplateCsv.url()}
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

                                <Button disabled={processing}>Import contacts</Button>
                            </>
                        )}
                    </Form>
                </section>

                <section className="space-y-3 rounded-xl border p-4">
                    <div className="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <h2 className="text-base font-semibold">Contacts</h2>
                        <div className="grid gap-3 md:grid-cols-2">
                            <div className="grid gap-1">
                                <Label htmlFor="search-contacts">Search</Label>
                                <Input
                                    id="search-contacts"
                                    placeholder="Search by code, CIF/NIF/NIE, name, company..."
                                    value={createForm.data.search}
                                    onChange={(event) => createForm.setData('search', event.target.value)}
                                />
                            </div>
                            <div className="grid gap-1">
                                <Label htmlFor="type-contacts">Type</Label>
                                <select
                                    id="type-contacts"
                                    className="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    value={createForm.data.typeFilter}
                                    onChange={(event) => createForm.setData('typeFilter', event.target.value)}
                                >
                                    <option value="all">All types</option>
                                    <option value="Persona">Persona</option>
                                    <option value="Empresa">Empresa</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[1200px] text-left text-sm">
                            <thead>
                                <tr className="border-b">
                                    <th className="px-2 py-3 font-medium">Code</th>
                                    <th className="px-2 py-3 font-medium">Type</th>
                                    <th className="px-2 py-3 font-medium">CIF / NIF / NIE</th>
                                    <th className="px-2 py-3 font-medium">Formatted name</th>
                                    <th className="px-2 py-3 font-medium">Email</th>
                                    <th className="px-2 py-3 font-medium">Phone</th>
                                    <th className="px-2 py-3 font-medium">Updated</th>
                                    <th className="px-2 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredContacts.length === 0 ? (
                                    <tr>
                                        <td colSpan={8} className="px-2 py-6 text-center text-muted-foreground">
                                            No rows match the current filters.
                                        </td>
                                    </tr>
                                ) : (
                                    filteredContacts.map((contact) => (
                                        <tr key={contact.id} className="border-b">
                                            <td className="px-2 py-3">{contact.code}</td>
                                            <td className="px-2 py-3">{contact.type}</td>
                                            <td className="px-2 py-3">{contact.taxId}</td>
                                            <td className="px-2 py-3">{contact.formatedName}</td>
                                            <td className="px-2 py-3">{contact.email ?? '-'}</td>
                                            <td className="px-2 py-3">{contact.phone ?? '-'}</td>
                                            <td className="px-2 py-3">
                                                {contact.updated_at ?? contact.created_at ?? '-'}
                                            </td>
                                            <td className="px-2 py-3">
                                                <div className="flex gap-2">
                                                    <Button size="sm" asChild>
                                                        <Link href={contactsEdit(contact.id)}>Edit</Link>
                                                    </Button>
                                                    <Link
                                                        href={AntgoReContactController.destroy(contact.id)}
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

ContactsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Contacts',
            href: contactsIndex(),
        },
    ],
};
