import { useApi } from '@/lib/api';
import { revokeEntityDefaultGrant } from '@/lib/entities';
import { manageEntityText, type RevokeEntityDefaultGrantText } from '@/lib/lang/en/entity/manage';
import { type GatekeeperEntity, type GatekeeperEntityModelMap } from '@/types';
import { Button } from '@components/ui/button';
import { Dialog, DialogClose, DialogContent, DialogDescription, DialogFooter, DialogTitle, DialogTrigger } from '@components/ui/dialog';
import { Input } from '@components/ui/input';
import InputError from '@components/ui/input-error';
import { Label } from '@components/ui/label';
import { type FormEvent, useMemo, useState } from 'react';

interface RevokeEntityDefaultGrantProps<E extends GatekeeperEntity> {
    entity: GatekeeperEntity;
    entityModel: GatekeeperEntityModelMap[E];
    updateEntity: (newEntity: GatekeeperEntityModelMap[E]) => void;
}

export default function RevokeEntityDefaultGrant<E extends GatekeeperEntity>({
    entity,
    entityModel,
    updateEntity,
}: RevokeEntityDefaultGrantProps<E>) {
    const api = useApi();
    const [entityName, setEntityName] = useState<string>('');
    const [processing, setProcessing] = useState<boolean>(false);
    const [error, setError] = useState<string | null>(null);
    const language: RevokeEntityDefaultGrantText = useMemo(() => manageEntityText[entity].revokeEntityDefaultGrantText, [entity]);

    const submitRevokeEntityDefaultGrant = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        if (entityName !== entityModel.name) {
            return setError(language.mismatchError);
        }

        revokeEntityDefaultGrant(api, entity, entityModel.id, updateEntity, setProcessing, setError);
    };

    const closeDeactivationModal = () => {
        setEntityName('');
        setError(null);
        setProcessing(false);
    };

    return (
        <div className="space-y-6">
            <div className="space-y-4 rounded-lg border border-red-100 bg-red-50 p-4 dark:border-red-200/10 dark:bg-red-700/10">
                <div className="relative space-y-0.5 text-red-600 dark:text-red-100">
                    <p className="font-medium">{language.title}</p>
                    <p className="text-sm">{language.description}</p>
                </div>

                <Dialog>
                    <DialogTrigger asChild>
                        <Button variant="destructive">{language.confirmButton}</Button>
                    </DialogTrigger>
                    <DialogContent>
                        <DialogTitle>{language.confirmTitle}</DialogTitle>
                        <DialogDescription>{language.confirmDescription(entityModel.name)}</DialogDescription>
                        <form className="space-y-6" onSubmit={submitRevokeEntityDefaultGrant}>
                            <div className="grid gap-2">
                                <Label htmlFor="name" className="sr-only">
                                    {language.inputLabel}
                                </Label>

                                <Input
                                    id="name"
                                    type="text"
                                    name="name"
                                    value={entityName}
                                    onChange={(e) => {
                                        setEntityName(e.target.value);
                                        setError(null);
                                    }}
                                />

                                <InputError message={error || undefined} />
                            </div>

                            <DialogFooter className="gap-2">
                                <DialogClose asChild>
                                    <Button variant="secondary" onClick={closeDeactivationModal}>
                                        {language.cancelButton}
                                    </Button>
                                </DialogClose>

                                <Button variant="destructive" disabled={processing} asChild>
                                    <button type="submit">{language.confirmButton}</button>
                                </Button>
                            </DialogFooter>
                        </form>
                    </DialogContent>
                </Dialog>
            </div>
        </div>
    );
}
