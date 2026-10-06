import { Head, Link, useForm } from '@inertiajs/react';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as adminsIndex, update as adminsUpdate } from '@/routes/admins';

type AdminRole = {
    role: string;
    description: string;
};

type Props = {
    admin: AdminRole;
};

export default function AdminsEdit({ admin }: Props) {
    const form = useForm({
        role: admin.role,
        description: admin.description,
    });

    return (
        <>
            <Head title={`Edit ${admin.role}`} />

            <div className="space-y-6 p-4">
                <Heading title="Edit admin role" description="Update the selected role." />

                <form
                    onSubmit={(event) => {
                        event.preventDefault();
                        form.put(adminsUpdate(admin.role));
                    }}
                    className="space-y-4 rounded-xl border p-4"
                >
                    <div className="grid gap-2">
                        <Label htmlFor="role">Role</Label>
                        <Input
                            id="role"
                            name="role"
                            value={form.data.role}
                            onChange={(event) => form.setData('role', event.target.value)}
                            required
                        />
                        <InputError message={form.errors.role} />
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

                    <div className="flex gap-2">
                        <Button disabled={form.processing}>Save changes</Button>
                        <Button variant="secondary" asChild>
                            <Link href={adminsIndex()}>Cancel</Link>
                        </Button>
                    </div>
                </form>
            </div>
        </>
    );
}

AdminsEdit.layout = {
    breadcrumbs: [
        {
            title: 'Admin roles',
            href: adminsIndex(),
        },
        {
            title: 'Edit',
            href: '#',
        },
    ],
};
