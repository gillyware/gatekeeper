import EntityFeatureDisabledAlert from '@/components/entity/EntityFeatureDisabledAlert';
import { useGatekeeper } from '@/context/GatekeeperContext';
import { entitiesLayoutText, EntityLayoutText } from '@/lib/lang/en/entity/layout';
import { cn } from '@/lib/utils';
import { type GatekeeperEntity, type NavItem } from '@/types';
import { Button } from '@components/ui/button';
import Heading from '@components/ui/heading';
import { Separator } from '@components/ui/separator';
import { FolderCog, ListChecks, PlusCircle } from 'lucide-react';
import { useMemo, type PropsWithChildren } from 'react';
import { useLocation } from 'react-router';
import { Link } from 'react-router-dom';

interface EntityLayoutProps extends PropsWithChildren {
    entity: GatekeeperEntity;
}

export default function EntityLayout({ entity, children }: EntityLayoutProps) {
    if (typeof window === 'undefined') {
        return null;
    }

    const { user } = useGatekeeper();
    const location = useLocation();
    const currentPath = location.pathname;
    const language: EntityLayoutText = useMemo(() => entitiesLayoutText[entity], [entity]);

    const navItems: NavItem[] = [
        {
            title: language.navIndex,
            href: `/${entity}s`,
            icon: ListChecks,
        },
        user.permissions.can_manage && {
            title: language.navCreate,
            href: `/${entity}s/create`,
            icon: PlusCircle,
        },
        currentPath.includes('manage') && {
            title: language.navManage,
            href: currentPath,
            icon: FolderCog,
        },
    ].filter((x) => Boolean(x)) as NavItem[];

    return (
        <div className="px-4 py-6">
            <Heading title={language.title} description={language.description} />

            <EntityFeatureDisabledAlert entity={entity} language={language} />

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
                    <section className="max-w-xl space-y-12">{children}</section>
                </div>
            </div>
        </div>
    );
}
