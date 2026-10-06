import { Form, Head, Link } from '@inertiajs/react';
import AntgoReAdminController from '@/actions/App/Http/Controllers/AntgoReAdminController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as adminsIndex, edit as adminsEdit } from '@/routes/admins';
import { ShieldCheck } from "lucide-react";

type AdminRole = {
    role: string;
    description: string;
    created_at?: string | null;
    updated_at?: string | null;
};

type Props = {
    admins: AdminRole[];
};

export default function AdminsIndex({ admins }: Props) {
    return (
        <>
            <Head title="Admin roles" />

            <div className="space-y-8 p-4">
                <div className="flex items-center gap-3">
                    <ShieldCheck className="h-14 w-14 shrink-0" aria-hidden="true" />
                    <Heading
                        title="Admin roles management"
                        description="Create and manage admin roles for the system."
                    />
                </div>

                <p className="mt-2 text-sm font-medium text-foreground">
                    Total rows: {admins.length}
                </p>

                <section className="grid gap-6 lg:grid-cols-2">
                    <Form
                        {...AntgoReAdminController.store.form()}
                        className="space-y-4 rounded-xl border p-4"
                        resetOnSuccess
                    >
                        {({ errors, processing }) => (
                            <>
                                <h2 className="text-base font-semibold">
                                    Create role
                                </h2>

                                <div className="grid gap-2">
                                    <Label htmlFor="role">Role</Label>
                                    <Input
                                        id="role"
                                        name="role"
                                        placeholder="super_admin"
                                        required
                                    />
                                    <InputError message={errors.role} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="description">
                                        Description
                                    </Label>
                                    <Input
                                        id="description"
                                        name="description"
                                        placeholder="Super administrator"
                                        required
                                    />
                                    <InputError message={errors.description} />
                                </div>

                                <Button disabled={processing}>Create role</Button>
                            </>
                        )}
                    </Form>

                    <div className="rounded-xl border p-4">
                        <h2 className="text-base font-semibold">Info</h2>
                        <p className="mt-2 text-sm text-muted-foreground">
                            These roles are referenced by the <code>admin_role</code>{' '}
                            field in users.
                        </p>
                    </div>
                </section>

                <section className="space-y-3 rounded-xl border p-4">
                    <h2 className="text-base font-semibold">Roles</h2>

                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[600px] text-left text-sm">
                            <thead>
                                <tr className="border-b">
                                    <th className="px-2 py-3 font-medium">Role</th>
                                    <th className="px-2 py-3 font-medium">Description</th>
                                    <th className="px-2 py-3 font-medium">Updated</th>
                                    <th className="px-2 py-3 font-medium">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {admins.map((admin) => (
                                    <tr key={admin.role} className="border-b">
                                        <td className="px-2 py-3">{admin.role}</td>
                                        <td className="px-2 py-3">{admin.description}</td>
                                        <td className="px-2 py-3">
                                            {admin.updated_at ?? admin.created_at ?? '-'}
                                        </td>
                                        <td className="px-2 py-3">
                                            <div className="flex gap-2">
                                                <Button size="sm" asChild>
                                                    <Link href={adminsEdit(admin.role)}>
                                                        Edit
                                                    </Link>
                                                </Button>
                                                <Link
                                                    href={AntgoReAdminController.destroy(
                                                        admin.role,
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

AdminsIndex.layout = {
    breadcrumbs: [
        {
            title: 'Admin roles',
            href: adminsIndex(),
        },
    ],
};
