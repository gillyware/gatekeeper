import { useGatekeeper } from '@/context/GatekeeperContext';
import { useIsMobile } from '@/hooks/use-mobile';
import { GatekeeperEntity } from '@/types';
import { GatekeeperError } from '@/types/api';
import { EntityModel } from '@/types/models';
import { Button } from '@components/ui/button';
import { Input } from '@components/ui/input';
import InputError from '@components/ui/input-error';
import { Tooltip, TooltipContent, TooltipTrigger } from '@components/ui/tooltip';
import { CheckCircle, LoaderCircle, PauseCircle } from 'lucide-react';
import { useState } from 'react';

interface ModelAccessListProps {
    title: string;
    enabledOnModel: boolean;
    enabledInApp: boolean;
    items: EntityModel[];
    entity: GatekeeperEntity;
    onRevoke: (entity: GatekeeperEntity, entityName: string, onError: (e: GatekeeperError) => void) => void;
    onAssign: (entity: GatekeeperEntity, value: string, onError: (e: GatekeeperError) => void) => void;
}

export default function ModelAccessList({ title, enabledOnModel, enabledInApp, items, entity, onRevoke, onAssign }: ModelAccessListProps) {
    const { user } = useGatekeeper();
    const [error, setError] = useState<GatekeeperError | null>(null);
    const [showInput, setShowInput] = useState<boolean>(false);
    const [inputValue, setInputValue] = useState<string>('');
    const [processing, setProcessing] = useState<boolean>(false);
    const isMobile = useIsMobile();

    const handleAssign = async () => {
        setProcessing(true);
        setError(null);
        onAssign(entity, inputValue, (err) => {
            setError(err);
            setProcessing(false);
        });
        setProcessing(false);
        setInputValue('');
        setShowInput(false);
    };

    return (
        <div>
            <h2 className="mb-2 text-lg font-semibold">{title}</h2>
            <div className="flex flex-col gap-2">
                {items.length === 0 ? (
                    <div className="text-muted-foreground text-sm">No {title.toLowerCase()} assigned.</div>
                ) : (
                    items.map((item) => (
                        <div key={item.id} className="flex items-center justify-between rounded border px-3 py-2">
                            <div className="flex items-center gap-2">
                                {item.is_active ? (
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <CheckCircle className="h-4 w-4 text-green-600 dark:text-green-400" />
                                        </TooltipTrigger>
                                        <TooltipContent side="right" align="center" hidden={isMobile}>
                                            Active
                                        </TooltipContent>
                                    </Tooltip>
                                ) : (
                                    <Tooltip>
                                        <TooltipTrigger asChild>
                                            <PauseCircle className="h-4 w-4 text-yellow-600 dark:text-yellow-400" />
                                        </TooltipTrigger>
                                        <TooltipContent side="right" align="center" hidden={isMobile}>
                                            Inactive
                                        </TooltipContent>
                                    </Tooltip>
                                )}
                                <span>{item.name}</span>
                            </div>
                            {user.permissions.can_manage && (
                                <Button
                                    variant="ghost"
                                    size="sm"
                                    onClick={() => {
                                        setError(null);
                                        onRevoke(entity, item.name, setError);
                                    }}
                                >
                                    Revoke
                                </Button>
                            )}
                        </div>
                    ))
                )}

                {enabledOnModel &&
                    enabledInApp &&
                    user.permissions.can_manage &&
                    (showInput ? (
                        <div className="flex gap-2">
                            <Input
                                placeholder={`Enter ${entity} name`}
                                value={inputValue}
                                onChange={(e) => setInputValue(e.target.value)}
                                className="w-full py-2"
                            />
                            <Button className="py-2" onClick={handleAssign} disabled={processing || !inputValue.trim()}>
                                {processing ? <LoaderCircle className="h-4 w-4 animate-spin" /> : 'Assign'}
                            </Button>
                            <Button className="py-2" variant="ghost" onClick={() => setShowInput(false)} disabled={processing}>
                                Cancel
                            </Button>
                        </div>
                    ) : (
                        <Button className="py-2" variant="outline" size="sm" onClick={() => setShowInput(true)}>
                            + Assign {title.slice(0, -1)}
                        </Button>
                    ))}

                {enabledOnModel && !enabledInApp && (
                    <div className="text-sm text-red-500">The {title.toLowerCase()} feature is not enabled in the application configuration.</div>
                )}

                {!enabledOnModel && enabledInApp && <div className="text-sm text-red-500">The model does not support {title.toLowerCase()}.</div>}

                {!enabledOnModel && !enabledInApp && (
                    <div className="text-sm text-red-500">
                        The {title.toLowerCase()} feature is not enabled in the application configuration and the model does not support it.
                    </div>
                )}

                {error && <InputError message={error.message} className="mt-2" />}
            </div>
        </div>
    );
}
