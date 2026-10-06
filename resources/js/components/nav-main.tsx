import { Link } from '@inertiajs/react';
import { ChevronDown } from 'lucide-react';
import { useState } from 'react';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import {
    SidebarGroup,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/hooks/use-current-url';
import type { NavItem } from '@/types';

export function NavMain({ items }: { items: NavItem[] }) {
    const { isCurrentUrl } = useCurrentUrl();
    const [openMenus, setOpenMenus] = useState<Record<string, boolean>>({
        Admin: true,
    });

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>Platform</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => {
                    if (item.children && item.children.length > 0) {
                        const isOpen = openMenus[item.title] ?? true;

                        return (
                            <SidebarMenuItem key={item.title} className="flex flex-col">
                                <div className="flex items-center gap-1 rounded-md border border-transparent transition-colors hover:bg-muted/50">
                                    <SidebarMenuButton
                                        asChild
                                        isActive={item.children.some((child) => isCurrentUrl(child.href))}
                                        tooltip={{ children: item.title }}
                                        className="flex-1 rounded-r-none"
                                    >
                                        <Link href={item.href} prefetch>
                                            {item.icon && <item.icon />}
                                            <span>{item.title}</span>
                                        </Link>
                                    </SidebarMenuButton>

                                    <Collapsible
                                        open={isOpen}
                                        onOpenChange={(value) =>
                                            setOpenMenus((current) => ({
                                                ...current,
                                                [item.title]: Boolean(value),
                                            }))
                                        }
                                    >
                                        <CollapsibleTrigger asChild>
                                            <button
                                                type="button"
                                                aria-label={isOpen ? `Cerrar ${item.title}` : `Abrir ${item.title}`}
                                                aria-expanded={isOpen}
                                                className="flex h-8 w-8 items-center justify-center rounded-md rounded-l-none transition-colors hover:bg-muted"
                                            >
                                                <ChevronDown
                                                    className={`h-4 w-4 transition-transform duration-200 ${
                                                        isOpen ? 'rotate-180' : ''
                                                    }`}
                                                />
                                            </button>
                                        </CollapsibleTrigger>
                                    </Collapsible>
                                </div>

                                <Collapsible open={isOpen}>
                                    <CollapsibleContent className="overflow-hidden">
                                        <div className="mt-1 ml-6 space-y-1 border-l border-border/80 pl-2">
                                            {item.children.map((child) => (
                                                <SidebarMenuButton
                                                    key={child.title}
                                                    asChild
                                                    isActive={isCurrentUrl(child.href)}
                                                    tooltip={{ children: child.title }}
                                                    className="h-8"
                                                >
                                                    <Link href={child.href} prefetch>
                                                        {child.icon && <child.icon />}
                                                        <span>{child.title}</span>
                                                    </Link>
                                                </SidebarMenuButton>
                                            ))}
                                        </div>
                                    </CollapsibleContent>
                                </Collapsible>
                            </SidebarMenuItem>
                        );
                    }

                    return (
                        <SidebarMenuItem key={item.title}>
                            <SidebarMenuButton
                                asChild
                                isActive={isCurrentUrl(item.href)}
                                tooltip={{ children: item.title }}
                            >
                                <Link href={item.href} prefetch>
                                    {item.icon && <item.icon />}
                                    <span>{item.title}</span>
                                </Link>
                            </SidebarMenuButton>
                        </SidebarMenuItem>
                    );
                })}
            </SidebarMenu>
        </SidebarGroup>
    );
}
