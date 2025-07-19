import { useGatekeeper } from '@/context/GatekeeperContext';
import { manageModelText, type ModelSummaryText } from '@/lib/lang/en/model/manage';
import { getEntitySupportForModel } from '@/lib/models';
import { cn } from '@/lib/utils';
import { VerboseRole, type ConfiguredModel, type ModelEntitySupport, type VerbosePermission } from '@/types/api/model';
import { Card, CardContent } from '@components/ui/card';
import { Input } from '@components/ui/input';
import { Separator } from '@components/ui/separator';
import { Tooltip, TooltipContent, TooltipTrigger } from '@components/ui/tooltip';
import { Ban, CheckCircle, ChevronDown, ChevronRight, LayoutPanelTop } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';

interface ModelSummaryProps {
    model: ConfiguredModel;
}

interface SupportIndicatorProps {
    supported: boolean;
    tooltip: string;
}

interface ModelInformationProps {
    model: ConfiguredModel;
    entitySupport: ModelEntitySupport;
    language: ModelSummaryText;
}

interface EffectivePermissionsProps {
    effectivePermissions: VerbosePermission[];
    language: ModelSummaryText;
}

interface EffectiveRolesProps {
    effectiveRoles: VerboseRole[];
    language: ModelSummaryText;
}

interface PermissionItemProps {
    permission: VerbosePermission;
    open: boolean;
    onToggle: () => void;
    language: ModelSummaryText;
}

interface RoleItemProps {
    role: VerboseRole;
    open: boolean;
    onToggle: () => void;
    language: ModelSummaryText;
}

export default function ModelSummary({ model }: ModelSummaryProps) {
    const { config } = useGatekeeper();
    const entitySupport: ModelEntitySupport = useMemo(() => getEntitySupportForModel(config, model), [config, model]);
    const language: ModelSummaryText = useMemo(() => manageModelText.modelSummaryText, []);

    return (
        <div className="flex h-full w-full flex-col gap-6">
            <ModelInformation model={model} entitySupport={entitySupport} language={language} />

            <EffectivePermissions effectivePermissions={model.access_sources.permissions} language={language} />

            {entitySupport.role.supported && <EffectiveRoles effectiveRoles={model.access_sources.roles} language={language} />}
        </div>
    );
}

function ModelInformation({ model, entitySupport, language }: ModelInformationProps) {
    return (
        <div className="mb-0 grid grid-cols-1 gap-6 sm:grid-cols-2">
            <Card>
                <CardContent className="flex flex-1 flex-col gap-2">
                    <div className="flex flex-row items-center justify-between gap-4">
                        <span className="font-bold">{language.modelLabel}</span>
                        <span>{model.model_label}</span>
                    </div>

                    <Separator className="bg-sidebar-border my-1" />

                    {model.displayable.map((x) => (
                        <div key={x.column} className="flex flex-row items-center justify-between gap-4">
                            <span className="font-bold">{x.label}:</span>
                            <span className="truncate">{model.display[x.column] ?? ''}</span>
                        </div>
                    ))}
                </CardContent>
            </Card>

            <Card>
                <CardContent className="flex flex-1 flex-col gap-2">
                    <div className="flex flex-row items-center justify-start">
                        <span className="font-bold">{language.entitySupportLabel}</span>
                    </div>

                    <Separator className="bg-sidebar-border my-1" />

                    <div className="flex flex-row items-center justify-between gap-4">
                        <span className="font-bold">{language.entitySupportText.permission.label}</span>
                        <span>
                            <SupportIndicator supported={entitySupport.permission.supported} tooltip={entitySupport.permission.reason || ''} />
                        </span>
                    </div>

                    <div className="flex flex-row items-center justify-between gap-4">
                        <span className="font-bold">{language.entitySupportText.role.label}</span>
                        <span>
                            <SupportIndicator supported={entitySupport.role.supported} tooltip={entitySupport.role.reason || ''} />
                        </span>
                    </div>

                    <div className="flex flex-row items-center justify-between gap-4">
                        <span className="font-bold">{language.entitySupportText.team.label}</span>
                        <span>
                            <SupportIndicator supported={entitySupport.team.supported} tooltip={entitySupport.team.reason || ''} />
                        </span>
                    </div>
                </CardContent>
            </Card>
        </div>
    );
}

function SupportIndicator({ supported, tooltip }: SupportIndicatorProps) {
    return supported ? (
        <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
    ) : (
        <Tooltip>
            <TooltipTrigger asChild>
                <Ban className="h-4 w-4 text-red-600 dark:text-red-400" />
            </TooltipTrigger>
            <TooltipContent>{tooltip}</TooltipContent>
        </Tooltip>
    );
}

function EffectivePermissions({ effectivePermissions, language }: EffectivePermissionsProps) {
    const [searchTerm, setSearchTerm] = useState<string>('');
    const filteredPermissions = useMemo(
        () => effectivePermissions.filter((p) => p.name.toLowerCase().includes(searchTerm.toLowerCase())),
        [effectivePermissions, searchTerm],
    );
    const [openStates, setOpenStates] = useState<Record<string, boolean>>(() => Object.fromEntries(filteredPermissions.map((p) => [p.name, false])));
    const allOpen = useMemo(() => Object.values(openStates).every(Boolean), [openStates]);

    useEffect(() => {
        setOpenStates(Object.fromEntries(filteredPermissions.map((p) => [p.name, false])));
    }, [filteredPermissions]);

    const toggleAll = () => {
        const next = Object.fromEntries(filteredPermissions.map((p) => [p.name, !allOpen]));
        setOpenStates(next);
    };

    const toggleOne = (name: string) => {
        setOpenStates((prev) => ({ ...prev, [name]: !prev[name] }));
    };

    return (
        <Card className="col-span-full">
            <CardContent className="flex flex-col gap-4">
                <div className="mb-0 flex items-center justify-between">
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <span className="cursor-default font-bold">{language.effectivePermissionsText.title}</span>
                        </TooltipTrigger>
                        <TooltipContent side="right">{language.effectivePermissionsText.titleTooltip}</TooltipContent>
                    </Tooltip>

                    {effectivePermissions.length > 0 && (
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <button
                                    onClick={toggleAll}
                                    className="text-sidebar-foreground hover:bg-sidebar-accent flex cursor-pointer items-center gap-1 rounded-md p-2 text-xs"
                                >
                                    <LayoutPanelTop className="h-4 w-4" />
                                </button>
                            </TooltipTrigger>
                            <TooltipContent side="left">{language.effectivePermissionsText.toggleAllTooltip(allOpen)}</TooltipContent>
                        </Tooltip>
                    )}
                </div>

                {effectivePermissions.length > 0 ? (
                    <>
                        <Input
                            type="text"
                            name="permission-search"
                            placeholder={language.effectivePermissionsText.searchPlaceholder}
                            className="w-full"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />

                        <div className="mb-0 flex flex-col justify-between gap-2 sm:flex-row sm:flex-wrap">
                            {filteredPermissions.map((permission) => (
                                <PermissionItem
                                    key={permission.name}
                                    permission={permission}
                                    open={openStates[permission.name]}
                                    onToggle={() => toggleOne(permission.name)}
                                    language={language}
                                />
                            ))}
                        </div>
                    </>
                ) : (
                    <span>{language.effectivePermissionsText.empty}</span>
                )}
            </CardContent>
        </Card>
    );
}

function EffectiveRoles({ effectiveRoles, language }: EffectiveRolesProps) {
    const [searchTerm, setSearchTerm] = useState<string>('');
    const filteredRoles = useMemo(
        () => effectiveRoles.filter((r) => r.name.toLowerCase().includes(searchTerm.toLowerCase())),
        [effectiveRoles, searchTerm],
    );
    const [openStates, setOpenStates] = useState<Record<string, boolean>>(() => Object.fromEntries(filteredRoles.map((r) => [r.name, false])));
    const allOpen = useMemo(() => Object.values(openStates).every(Boolean), [openStates]);

    useEffect(() => {
        setOpenStates(Object.fromEntries(filteredRoles.map((r) => [r.name, false])));
    }, [filteredRoles]);

    const toggleAll = () => {
        const next = Object.fromEntries(filteredRoles.map((r) => [r.name, !allOpen]));
        setOpenStates(next);
    };

    const toggleOne = (name: string) => {
        setOpenStates((prev) => ({ ...prev, [name]: !prev[name] }));
    };

    return (
        <Card className="col-span-full">
            <CardContent className="flex flex-col gap-4">
                <div className="mb-0 flex items-center justify-between">
                    <Tooltip>
                        <TooltipTrigger asChild>
                            <span className="cursor-default font-bold">{language.effectiveRolesText.title}</span>
                        </TooltipTrigger>
                        <TooltipContent side="right">{language.effectiveRolesText.titleTooltip}</TooltipContent>
                    </Tooltip>

                    {effectiveRoles.length > 0 && (
                        <Tooltip>
                            <TooltipTrigger asChild>
                                <button
                                    onClick={toggleAll}
                                    className="text-sidebar-foreground hover:bg-sidebar-accent flex cursor-pointer items-center gap-1 rounded-md p-2 text-xs"
                                >
                                    <LayoutPanelTop className="h-4 w-4" />
                                </button>
                            </TooltipTrigger>
                            <TooltipContent side="left">{language.effectiveRolesText.toggleAllTooltip(allOpen)}</TooltipContent>
                        </Tooltip>
                    )}
                </div>

                {effectiveRoles.length > 0 ? (
                    <>
                        <Input
                            type="text"
                            name="role-search"
                            placeholder={language.effectiveRolesText.searchPlaceholder}
                            className="w-full"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />

                        <div className="mb-0 flex flex-col justify-between gap-2 sm:flex-row sm:flex-wrap">
                            {filteredRoles.map((role) => (
                                <RoleItem
                                    key={role.name}
                                    role={role}
                                    open={openStates[role.name]}
                                    onToggle={() => toggleOne(role.name)}
                                    language={language}
                                />
                            ))}
                        </div>
                    </>
                ) : (
                    <span>{language.effectiveRolesText.empty}</span>
                )}
            </CardContent>
        </Card>
    );
}

function PermissionItem({ permission, open, onToggle, language }: PermissionItemProps) {
    return (
        <div className={cn('w-full rounded-md sm:w-[calc(50%-0.5rem)]', open ? 'border' : '')}>
            <button
                onClick={onToggle}
                className={cn(
                    'bg-muted hover:bg-accent sm:text-md flex w-full cursor-pointer items-center justify-between rounded-t-md px-4 py-2 text-left text-sm',
                    !open ? 'border' : '',
                )}
            >
                <span className="font-medium">{permission.name}</span>
                {open ? <ChevronDown className="h-4 w-4" /> : <ChevronRight className="h-4 w-4" />}
            </button>

            {open && (
                <div className="bg-background text-muted-foreground rounded-md px-6 py-2 text-sm">
                    <ul className="space-y-1">
                        {permission.sources.map((source, idx) => (
                            <li className="border-l-2 pl-4" key={idx}>
                                {language.effectivePermissionsText.sourceLabel(source)}
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}

function RoleItem({ role, open, onToggle, language }: RoleItemProps) {
    return (
        <div className={cn('w-full rounded-md sm:w-[calc(50%-0.5rem)]', open ? 'border' : '')}>
            <button
                onClick={onToggle}
                className={cn(
                    'bg-muted hover:bg-accent sm:text-md flex w-full cursor-pointer items-center justify-between rounded-t-md px-4 py-2 text-left text-sm',
                    !open ? 'border' : '',
                )}
            >
                <span className="font-medium">{role.name}</span>
                {open ? <ChevronDown className="h-4 w-4" /> : <ChevronRight className="h-4 w-4" />}
            </button>

            {open && (
                <div className="bg-background text-muted-foreground rounded-md px-6 py-2 text-sm">
                    <ul className="space-y-1">
                        {role.sources.map((source, idx) => (
                            <li className="border-l-2 pl-4" key={idx}>
                                {language.effectiveRolesText.sourceLabel(source)}
                            </li>
                        ))}
                    </ul>
                </div>
            )}
        </div>
    );
}
