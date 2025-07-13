import { useApi } from '@/lib/api';
import { persistEntity } from '@/lib/entities';
import { type EntityFormText, manageEntityText } from '@/lib/lang/en/entity/manage';
import { type GatekeeperEntity, type GatekeeperEntityModelMap } from '@/types';
import { Button } from '@components/ui/button';
import { Card, CardContent } from '@components/ui/card';
import { Input } from '@components/ui/input';
import InputError from '@components/ui/input-error';
import { Label } from '@components/ui/label';
import { Transition } from '@headlessui/react';
import { type FormEvent, useState } from 'react';

export type CreateEntityFormType = 'create';
export type UpdateEntityFormType = 'update';
export type EntityFormType = CreateEntityFormType | UpdateEntityFormType;

interface EntityFormProps<E extends GatekeeperEntity> {
    formType: EntityFormType;
    entity: GatekeeperEntity;
    entityModel?: GatekeeperEntityModelMap[E];
    updateEntity?: (newEntity: GatekeeperEntityModelMap[E]) => void;
}

export default function EntityForm<E extends GatekeeperEntity>({ formType, entity, entityModel, updateEntity }: EntityFormProps<E>) {
    const api = useApi();
    const [entityName, setEntityName] = useState<string>(entityModel?.name || '');
    const [processing, setProcessing] = useState<boolean>(false);
    const [recentlySuccessful, setRecentlySuccessful] = useState<boolean>(false);
    const [error, setError] = useState<string | null>(null);
    const language: EntityFormText = manageEntityText[entity].entityFormText[formType];

    const submitPersistence = async (e: FormEvent<HTMLFormElement>) => {
        e.preventDefault();

        const onSuccess = (newEntityModel: GatekeeperEntityModelMap[E]) => {
            if (updateEntity) {
                updateEntity(newEntityModel);
            }

            setRecentlySuccessful(true);
            setTimeout(() => setRecentlySuccessful(false), 2000);
        };

        persistEntity(api, entity, formType, Number(entityModel?.id), entityName, onSuccess, setProcessing, setError);
    };

    return (
        <Card>
            <CardContent>
                <form onSubmit={submitPersistence}>
                    <div className="mb-2">
                        <Label htmlFor="name">{language.inputLabel}</Label>
                        <Input
                            id="name"
                            name="name"
                            type="text"
                            value={entityName}
                            onChange={(e) => {
                                setEntityName(e.target.value);
                                setError(null);
                            }}
                        />
                    </div>

                    {error && <InputError className="mb-2" message={error || undefined} />}

                    <div className="flex items-center justify-end gap-4 pt-2">
                        <Transition
                            show={recentlySuccessful}
                            enter="transition ease-in-out"
                            enterFrom="opacity-0"
                            leave="transition ease-in-out"
                            leaveTo="opacity-0"
                        >
                            <p className="text-sm text-neutral-600">{language.successMessage}</p>
                        </Transition>

                        <Button type="submit" disabled={processing}>
                            {language.submitButton}
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>
    );
}
