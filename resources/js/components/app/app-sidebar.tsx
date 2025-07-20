import { type NavItem } from '@/types';
import AppLogo from '@components/app/app-logo';
import { NavFooter } from '@components/app/app-nav-footer';
import { NavMain } from '@components/app/app-nav-main';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@components/ui/sidebar';
import { BookOpen, FileClock, Folder, KeyRound, Shapes, ShieldCheck, Users } from 'lucide-react';
import { Link } from 'react-router-dom';

const mainNavItems: NavItem[] = [
    { title: 'Permissions', href: '/permissions', icon: KeyRound },
    { title: 'Roles', href: '/roles', icon: ShieldCheck },
    { title: 'Teams', href: '/teams', icon: Users },
    { title: 'Models', href: '/models', icon: Shapes },
    { title: 'Audit', href: '/audit', icon: FileClock },
];

const footerNavItems: NavItem[] = [
    { title: 'Repository', href: 'https://github.com/gillyware/gatekeeper', icon: Folder },
    { title: 'Documentation', href: 'https://github.com/gillyware/gatekeeper/blob/main/README.md#official-documentation', icon: BookOpen },
];

export function AppSidebar() {
    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link to="/">
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
            </SidebarFooter>
        </Sidebar>
    );
}
