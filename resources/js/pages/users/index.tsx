import { Form, Head, Link } from '@inertiajs/react';
import UserManagementController from '@/actions/App/Http/Controllers/UserManagementController';
import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import users, { index as usersIndex, edit as usersEdit } from '@/routes/users';
import { Users } from "lucide-react";

type AdminRole = {
    role: string;
    description: string;
};

type ManagedUser = {
    id: number;
    name: string;
    email: string;
    admin_role: string | null;
    admin_role_description: string | null;
    created_at: string | null;
};

type UsersPaginator = {
    data: ManagedUser[];
};

type Props = {
    users: UsersPaginator;
    adminRoles: AdminRole[];
};

export default function UsersIndex({ users, adminRoles }: Props) {
    return (
        <>
            <div className="space-y-8 p-4">
                <div className="flex items-center gap-3">
                    <Users className="h-14 w-14 shrink-0" aria-hidden="true" />
                    <Heading
                        title="User management"
                        description="Create users, assign admin roles and import users from CSV."
                    />
                </div>

                <p className="mt-2 text-sm font-medium text-foreground">
                    Total rows: {users.data.length}
                </p>


                <section className="grid gap-6 lg:grid-cols-2">
                    <Form
                        {...UserManagementController.store.form()}
                        className="space-y-4 rounded-xl border p-4"
                        resetOnSuccess
                    >
                        {({ errors, processing }) => (
                            <>
                                <h2 className="text-base font-semibold">
                                    Create user
                                </h2>

                                <div className="grid gap-2">
                                    <Label htmlFor="name">Name</Label>
                                    <Input id="name" name="name" required />
                                    <InputError message={errors.name} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="email">Email</Label>
                                    <Input
                                        id="email"
                                        type="email"
                                        name="email"
                                        required
                                    />
                                    <InputError message={errors.email} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="password">Password</Label>
                                    <Input
                                        id="password"
                                        type="password"
                                        name="password"
                                        required
                                    />
                                    <InputError message={errors.password} />
                                </div>

                                <div className="grid gap-2">
                                    <Label htmlFor="admin_role">
                                        Admin role
                                    </Label>
                                    <select
                                        id="admin_role"
                                        name="admin_role"
                                        className="h-10 rounded-md border border-input bg-background px-3 py-2 text-sm"
                                        defaultValue=""
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

                                <Button disabled={processing}>Create user</Button>
                            </>
                        )}
                    </Form>

                    <Form
                        {...UserManagementController.importCsv.form()}
                        className="space-y-4 rounded-xl border p-4"
                        resetOnSuccess
                    >
                        {({ errors, processing }) => (
                            <>
                                <h2 className="text-base font-semibold">
                                    Import CSV
                                </h2>
                                <p className="text-sm text-muted-foreground">
                                    Required columns: name, email, password.
                                    Optional column: admin_role.
                                </p>

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

                                <Button disabled={processing}>Import users</Button>
                            </>
                        )}
                    </Form>
                </section>

                <section className="space-y-3 rounded-xl border p-4">
                    <h2 className="text-base font-semibold">Users</h2>

                    <div className="overflow-x-auto">
                        <table className="w-full min-w-[700px] text-left text-sm">
                            <thead>
                                <tr className="border-b">
                                    <th className="px-2 py-3 font-medium">
                                        Name
                                    </th>
                                    <th className="px-2 py-3 font-medium">
                                        Email
                                    </th>
                                    <th className="px-2 py-3 font-medium">
                                        Admin role
                                    </th>
                                    <th className="px-2 py-3 font-medium">
                                        Created
                                    </th>
                                    <th className="px-2 py-3 font-medium">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {users.data.map((user) => (
                                    <tr key={user.id} className="border-b">
                                        <td className="px-2 py-3">
                                            {user.name}
                                        </td>
                                        <td className="px-2 py-3">
                                            {user.email}
                                        </td>
                                        <td className="px-2 py-3">
                                            {user.admin_role ?? '-'}
                                        </td>
                                        <td className="px-2 py-3">
                                            {user.created_at ?? '-'}
                                        </td>
                                        <td className="px-2 py-3">
                                            <div className="flex gap-2">
                                                <Button size="sm" asChild>
                                                    <Link
                                                        href={usersEdit(user.id)}
                                                    >
                                                        Edit
                                                    </Link>
                                                </Button>
                                                <Link
                                                    href={UserManagementController.destroy(
                                                        user.id,
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
            </div >
        </>
    );
}

UsersIndex.layout = {
    breadcrumbs: [
        {
            title: 'User management',
            href: usersIndex(),
        },
    ],
};
