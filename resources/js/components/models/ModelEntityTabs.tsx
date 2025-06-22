import { cn } from '@/lib/utils';
import { ModelManagementTab } from '@/types';
import { KeyRound, LucideIcon, ShieldCheck, Square, Users } from 'lucide-react';

interface ModelManagementTabsProps {
    tab: ModelManagementTab;
    changeTab: (tab: ModelManagementTab) => void;
}

export default function ModelManagementTabs({ tab, changeTab }: ModelManagementTabsProps) {
    const tabs: { value: ModelManagementTab; icon: LucideIcon; label: string }[] = [
        { value: 'overview', icon: Square, label: 'Overview' },
        { value: 'permissions', icon: KeyRound, label: 'Permissions' },
        { value: 'roles', icon: ShieldCheck, label: 'Roles' },
        { value: 'teams', icon: Users, label: 'Teams' },
    ];

    return (
        <div className="mb-0 inline-flex w-full justify-between gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800">
            {tabs.map(({ value, icon: Icon, label }) => (
                <button
                    key={value}
                    onClick={() => {
                        if (tab === value) return;
                        changeTab(value);
                    }}
                    className={cn(
                        'flex grow cursor-pointer items-center justify-center rounded-md px-3.5 py-1.5 transition-colors',
                        tab === value
                            ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                            : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
                    )}
                >
                    <Icon className="-ml-1 h-4 w-4" />
                    <span className={'ml-1.5 hidden text-sm sm:inline-flex'}>{label}</span>
                </button>
            ))}
        </div>
    );
}
