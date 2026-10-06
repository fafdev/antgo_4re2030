import { Form, Head, Link } from '@inertiajs/react';
import UserManagementController from '@/actions/App/Http/Controllers/UserManagementController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index as usersIndex } from '@/routes/users';

type AdminRole = {
    role: string;
    description: string;
};

type ManagedUser = {
    id: number;
    name: string;
    email: string;
    admin_role: string | null;
};

type Props = {
    user: ManagedUser;
    adminRoles: AdminRole[];
};

export default function UsersEdit({ user, adminRoles }: Props) {
    return (
        <>
            <Head title="Edit user" />

            <div className="space-y-6 p-4">
                <Heading
                    title="Edit user"
                    description="Update user data and optional admin role."
                />

                <Form
                    {...UserManagementController.update.form(user.id)}
                    className="space-y-4 rounded-xl border p-4"
                >
                    {({ errors, processing }) => (
                        <>
                            <div className="grid gap-2">
                                <Label htmlFor="name">Name</Label>
                                <Input
                                    id="name"
                                    name="name"
                                    defaultValue={user.name}
                                    required
                                />
                                <InputError message={errors.name} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    name="email"
                                    type="email"
                                    defaultValue={user.email}
                                    required
                                />
                                <InputError message={errors.email} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="password">
                                    Password (optional)
                                </Label>
                                <Input
                                    id="password"
                                    name="password"
                                    type="password"
                                />
                                <InputError message={errors.password} />
                            </div>

                            <div className="grid gap-2">
                                <Label htmlFor="admin_role">Admin role</Label>
                                <select
                                    id="admin_role"
                                    name="admin_role"
                                    className="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                    defaultValue={user.admin_role ?? ''}
                                >
                                    <option value="">No role</option>
                                    {adminRoles.map((role) => (
                                        <option
                                            key={role.role}
                                            value={role.role}
                                        >
                                            {role.role} - {role.description}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors.admin_role} />
                            </div>

                            <div className="flex items-center gap-2">
                                <Button disabled={processing}>
                                    Save changes
                                </Button>
                                <Button variant="ghost" asChild>
                                    <Link href={usersIndex()}>Cancel</Link>
                                </Button>
                            </div>
                        </>
                    )}
                </Form>
            </div>
        </>
    );
}

UsersEdit.layout = {
    breadcrumbs: [
        {
            title: 'User management',
            href: usersIndex(),
        },
        {
            title: 'Edit user',
            href: usersIndex(),
        },
    ],
};
