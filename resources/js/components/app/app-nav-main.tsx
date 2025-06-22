import { type NavItem } from '@/types';
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@components/ui/sidebar';
import { useLocation } from 'react-router';
import { Link } from 'react-router-dom';

export function NavMain({ items = [] }: { items: NavItem[] }) {
    const location = useLocation();

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarGroupLabel>Platform</SidebarGroupLabel>
            <SidebarMenu>
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton
                            asChild
                            isActive={item.href.split('/')[1] === location.pathname.split('/')[1]}
                            tooltip={{ children: item.title }}
                        >
                            <Link to={item.href}>
                                {item.icon && <item.icon />}
                                <span>{item.title}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
