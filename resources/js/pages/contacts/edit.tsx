import { Head, Link, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { detectSpanishTaxIdType, normalizeSpanishTaxId } from '@/lib/spanish-tax-id';
import { index as contactsIndex, update as contactsUpdate } from '@/routes/contacts';

type Contact = {
    id: string;
    code: string;
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
    mobilePhone: string | null;
    addresses: Address[];
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
    contact: Contact;
};

export default function ContactsEdit({ contact }: Props) {
    const form = useForm({
        taxId: contact.taxId,
        formatedName: contact.formatedName,
        name: contact.name ?? '',
        middleName: contact.middleName ?? '',
        lastName: contact.lastName ?? '',
        companyName: contact.companyName ?? '',
        gender: contact.gender ?? '',
        birthDate: contact.birthDate ?? '',
        email: contact.email ?? '',
        phone: contact.phone ?? '',
        mobilePhone: contact.mobilePhone ?? '',
        addresses: contact.addresses.length > 0
            ? contact.addresses
            : [
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
            ],
    });

    const detectedType = detectSpanishTaxIdType(form.data.taxId);
    const isEmpresa = detectedType === 'Empresa';
    const isPersona = detectedType === 'Persona';
    const showCompanyFields = !isPersona;
    const showPersonFields = !isEmpresa;

    const updateTaxId = (rawTaxId: string) => {
        const taxId = normalizeSpanishTaxId(rawTaxId);
        const taxIdType = detectSpanishTaxIdType(taxId);

        form.setData('taxId', taxId);

        if (taxIdType === 'Empresa') {
            form.setData('name', '');
            form.setData('middleName', '');
            form.setData('lastName', '');
            form.setData('gender', '');
            form.setData('birthDate', '');
            form.setData('formatedName', form.data.companyName.trim());

            return;
        }

        if (taxIdType === 'Persona') {
            form.setData('companyName', '');
            form.setData(
                'formatedName',
                [form.data.name, form.data.middleName, form.data.lastName]
                    .filter((value) => value.trim() !== '')
                    .join(' ')
                    .trim(),
            );
        }
    };

    const updatePersonNameParts = (nextValues: { name?: string; middleName?: string; lastName?: string }) => {
        form.setData(
            'formatedName',
            [nextValues.name ?? form.data.name, nextValues.middleName ?? form.data.middleName, nextValues.lastName ?? form.data.lastName]
                .filter((value) => value.trim() !== '')
                .join(' ')
                .trim(),
        );
    };

    const updateCompanyName = (companyName: string) => {
        form.setData('companyName', companyName);
        form.setData('formatedName', companyName.trim());
    };

    const hasAddressValues = (address: Address) =>
        [address.street, address.number, address.building, address.floor, address.door, address.city, address.postal_code, address.country]
            .some((value) => value.trim() !== '');

    const addAddress = () => {
        form.setData('addresses', [
            ...form.data.addresses,
            {
                street: '',
                number: '',
                building: '',
                floor: '',
                door: '',
                city: '',
                postal_code: '',
                country: '',
                is_primary: form.data.addresses.length === 0,
            },
        ]);
    };

    const removeAddress = (index: number) => {
        const nextAddresses = form.data.addresses.filter((_, currentIndex) => currentIndex !== index);

        if (nextAddresses.length > 0 && !nextAddresses.some((address) => address.is_primary)) {
            nextAddresses[0] = { ...nextAddresses[0], is_primary: true };
        }

        form.setData('addresses', nextAddresses);
    };

    const setAddressField = (index: number, field: keyof Address, value: string) => {
        form.setData(
            'addresses',
            form.data.addresses.map((address, currentIndex) =>
                currentIndex === index ? { ...address, [field]: value } : address,
            ),
        );
    };

    const setPrimaryAddress = (index: number) => {
        form.setData(
            'addresses',
            form.data.addresses.map((address, currentIndex) => ({
                ...address,
                is_primary: currentIndex === index,
            })),
        );
    };

    return (
        <>
            <Head title={`Edit ${contact.code}`} />

            <div className="space-y-6 p-4">
                <Heading
                    title="Edit contact"
                    description="Update contact data and recalculate type and formatted name from CIF/NIF/NIE."
                />

                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.transform((data) => ({
                            ...data,
                            addresses: data.addresses.filter((address) =>
                                hasAddressValues(address),
                            ),
                        }));
                        form.put(contactsUpdate(contact.id).url);
                    }}
                    className="space-y-4 rounded-xl border p-4"
                >
                    <div className="grid gap-2">
                        <Label>Code</Label>
                        <p className="min-h-10 rounded-md border border-input bg-muted/30 px-3 py-2 text-sm">
                            {contact.code}
                        </p>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="taxId">CIF / NIF / NIE</Label>
                        <Input
                            id="taxId"
                            name="taxId"
                            value={form.data.taxId}
                            onChange={(event) => updateTaxId(event.target.value)}
                            required
                        />
                        <InputError message={form.errors.taxId} />
                    </div>

                    {showCompanyFields && (
                        <div className="grid gap-2">
                            <Label htmlFor="companyName">Company name</Label>
                            <Input
                                id="companyName"
                                name="companyName"
                                value={form.data.companyName}
                                onChange={(event) => updateCompanyName(event.target.value)}
                                required
                            />
                            <InputError message={form.errors.companyName} />
                        </div>
                    )}

                    {showPersonFields && (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    value={form.data.name}
                                    onChange={(event) => {
                                        form.setData('name', event.target.value);
                                        updatePersonNameParts({ name: event.target.value });
                                    }}
                                    required={isPersona}
                                />
                                <InputError message={form.errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="middleName">Middle name</Label>
                                <Input
                                    id="middleName"
                                    name="middleName"
                                    value={form.data.middleName}
                                    onChange={(event) => {
                                        form.setData('middleName', event.target.value);
                                        updatePersonNameParts({ middleName: event.target.value });
                                    }}
                                />
                                <InputError message={form.errors.middleName} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="lastName">Last name</Label>
                                <Input
                                    id="lastName"
                                    name="lastName"
                                    value={form.data.lastName}
                                    onChange={(event) => {
                                        form.setData('lastName', event.target.value);
                                        updatePersonNameParts({ lastName: event.target.value });
                                    }}
                                    required={isPersona}
                                />
                                <InputError message={form.errors.lastName} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="gender">Gender</Label>
                                <select
                                    id="gender"
                                    name="gender"
                                    className="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    value={form.data.gender}
                                    onChange={(event) => form.setData('gender', event.target.value)}
                                >
                                    <option value="">No gender</option>
                                    <option value="Hombre">Hombre</option>
                                    <option value="Mujer">Mujer</option>
                                    <option value="Other">Other</option>
                                </select>
                                <InputError message={form.errors.gender} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="birthDate">Birth date</Label>
                                <Input
                                    id="birthDate"
                                    name="birthDate"
                                    type="date"
                                    value={form.data.birthDate}
                                    onChange={(event) => form.setData('birthDate', event.target.value)}
                                />
                                <InputError message={form.errors.birthDate} />
                            </div>
                        </>
                    )}

                    <div className="grid gap-2">
                        <Label>Formatted name</Label>
                        <p className="min-h-10 rounded-md border border-input bg-muted/30 px-3 py-2 text-sm">
                            {form.data.formatedName || '-'}
                        </p>
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">Email</Label>
                        <Input
                            id="email"
                            name="email"
                            type="email"
                            value={form.data.email}
                            onChange={(event) => form.setData('email', event.target.value)}
                        />
                        <InputError message={form.errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="phone">Phone</Label>
                        <Input
                            id="phone"
                            name="phone"
                            value={form.data.phone}
                            onChange={(event) => form.setData('phone', event.target.value)}
                        />
                        <InputError message={form.errors.phone} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="mobilePhone">Mobile Phone</Label>
                        <Input
                            id="mobilePhone"
                            name="mobilePhone"
                            value={form.data.mobilePhone}
                            onChange={(event) => form.setData('mobilePhone', event.target.value)}
                        />
                        <InputError message={form.errors.mobilePhone} />
                    </div>

                    <div className="space-y-3 rounded-lg border p-3">
                        <div className="flex items-center justify-between">
                            <h3 className="text-sm font-semibold">Addresses</h3>
                            <Button type="button" variant="secondary" size="sm" onClick={addAddress}>
                                Add address
                            </Button>
                        </div>

                        {form.data.addresses.map((address, index) => (
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
                                            name="primary-address-edit"
                                            checked={address.is_primary}
                                            onChange={() => setPrimaryAddress(index)}
                                            className="h-4 w-4"
                                        />
                                        <span>Primary address</span>
                                    </label>
                                </div>

                                {form.data.addresses.length > 1 && (
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

                        <InputError message={form.errors.addresses} />
                    </div>

                    <div className="flex gap-2">
                        <Button disabled={form.processing}>Save changes</Button>
                        <Button variant="secondary" asChild>
                            <Link href={contactsIndex()}>Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

ContactsEdit.layout = {
    breadcrumbs: [
        {
            title: 'Contacts',
            href: contactsIndex(),
        },
        {
            title: 'Edit',
            href: '#',
        },
    ],
};
