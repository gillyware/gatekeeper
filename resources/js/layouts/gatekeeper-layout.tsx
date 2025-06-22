import { type BreadcrumbItem } from '@/types';
import { AppContent } from '@components/app/app-content';
import { AppShell } from '@components/app/app-shell';
import { AppSidebar } from '@components/app/app-sidebar';
import { AppSidebarHeader } from '@components/app/app-sidebar-header';
import { type PropsWithChildren } from 'react';

export default function GatekeeperLayout({ children, breadcrumbs = [] }: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[] }>) {
    return (
        <AppShell variant="sidebar">
            <AppSidebar />
            <AppContent variant="sidebar">
                <AppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
