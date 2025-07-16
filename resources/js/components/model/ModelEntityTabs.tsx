import { useGatekeeper } from '@/context/GatekeeperContext';
import { manageModelText, type ModelManagementTabsText } from '@/lib/lang/en/model/manage';
import { getEntitySupportForModel } from '@/lib/models';
import { cn } from '@/lib/utils';
import { type ModelManagementTab } from '@/types';
import { type ConfiguredModel, type ModelEntitySupport } from '@/types/api/model';
import { KeyRound, LucideIcon, ShieldCheck, Square, Users } from 'lucide-react';
import { useMemo } from 'react';

interface ModelManagementTabsProps {
    tab: ModelManagementTab;
    changeTab: (tab: ModelManagementTab) => void;
    model: ConfiguredModel;
}

interface Tab {
    value: ModelManagementTab;
    icon: LucideIcon;
    label: string;
}

export default function ModelManagementTabs({ tab, changeTab, model }: ModelManagementTabsProps) {
    const { config } = useGatekeeper();
    const entitySupport: ModelEntitySupport = useMemo(() => getEntitySupportForModel(config, model), [config, model]);

    const showPermissiosnTab = entitySupport.permission.supported || model.direct_permissions.length > 0;
    const showRolesTab = entitySupport.role.supported || model.direct_roles.length > 0;
    const showTeamsTab = entitySupport.team.supported || model.direct_teams.length > 0;
    const language: ModelManagementTabsText = useMemo(() => manageModelText.modelManagementTabsText, []);

    const tabs: Tab[] = [
        { value: 'overview', icon: Square, label: language.navOverview },
        showPermissiosnTab && { value: 'permissions', icon: KeyRound, label: language.navPermission },
        showRolesTab && { value: 'roles', icon: ShieldCheck, label: language.navRoles },
        showTeamsTab && { value: 'teams', icon: Users, label: language.navTeams },
    ].filter((x) => Boolean(x)) as Tab[];

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
