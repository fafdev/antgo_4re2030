import { Link } from '@inertiajs/react';
import { BookOpen, CalendarDays, Factory, FolderGit2, LayoutGrid, Layers, Package, Ruler, ShieldCheck, SlidersHorizontal, Users, Wrench } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as adminsIndex } from '@/routes/admins';
import { index as characteristicsIndex } from '@/routes/characteristics';
import { index as contactsIndex } from '@/routes/contacts';
import { index as datesIndex } from '@/routes/dates';
import { index as equipmentsIndex } from '@/routes/equipments';
import { index as infrastructuresIndex } from '@/routes/infrastructures';
import { index as measuresIndex } from '@/routes/measures';
import { index as rolesIndex } from '@/routes/roles';
import { index as usersIndex } from '@/routes/users';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Admin',
        href: adminsIndex(),
        icon: Wrench,
        children: [
            {
                title: 'Users',
                href: usersIndex(),
                icon: Users,
            },
            {
                title: 'Admin roles',
                href: adminsIndex(),
                icon: ShieldCheck,
            },
        ],
    },
    {
        title: 'Auxiliar',
        href: rolesIndex(),
        icon: Layers,
        children: [
            {
                title: 'Roles',
                href: rolesIndex(),
                icon: ShieldCheck,
            },
            {
                title: 'Dates',
                href: datesIndex(),
                icon: CalendarDays,
            },
            {
                title: 'Characteristics',
                href: characteristicsIndex(),
                icon: SlidersHorizontal,
            },
            {
                title: 'Contacts',
                href: contactsIndex(),
                icon: Users,
            },
            {
                title: 'Equipments',
                href: equipmentsIndex(),
                icon: Package,
            },
            {
                title: 'Infrastructures',
                href: infrastructuresIndex(),
                icon: Factory,
            },
            {
                title: 'Measures',
                href: measuresIndex(),
                icon: Ruler,
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={dashboard()} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
