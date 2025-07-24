import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { modelLayoutText, ModelLayoutText } from '@/lib/lang/en/model/layout';
import { cn } from '@/lib/utils';
import { type NavItem } from '@/types';
import Heading from '@components/ui/heading';
import { FolderCog, ListChecks } from 'lucide-react';
import { type PropsWithChildren } from 'react';
import { useLocation } from 'react-router';
import { Link } from 'react-router-dom';

export default function ModelLayout({ children }: PropsWithChildren) {
    const location = useLocation();

    if (typeof window === 'undefined') {
        return null;
    }

    const currentPath = location.pathname;
    const language: ModelLayoutText = modelLayoutText;

    const navItems: NavItem[] = [
        {
            title: language.navIndex,
            href: '/models',
            icon: ListChecks,
        },
        currentPath.match(/^\/models\/[^/]+\/[^/]+$/) && {
            title: language.navManage,
            href: currentPath,
            icon: FolderCog,
        },
    ].filter((x) => Boolean(x)) as NavItem[];

    return (
        <div className="px-4 py-6">
            <Heading title={language.title} description={language.description} />

            <div className="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav className="flex flex-col space-y-1 space-x-0">
                        {navItems.map((item, index) => (
                            <Button
                                key={`${item.href}-${index}`}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn('w-full justify-start', {
                                    'bg-muted': currentPath === item.href,
                                })}
                            >
                                <Link to={item.href}>
                                    {item.icon && <item.icon />}
                                    <span>{item.title}</span>
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-6 md:hidden" />

                <div className="flex-1 md:max-w-2xl">
                    <section className="max-w-2xl space-y-12">{children}</section>
                </div>
            </div>
        </div>
    );
}
