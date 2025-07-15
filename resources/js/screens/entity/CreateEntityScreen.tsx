import EntityForm from '@/components/entity/EntityForm';
import { useGatekeeper } from '@/context/GatekeeperContext';
import EntityLayout from '@/layouts/entity-layout';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import { createEntityText } from '@/lib/lang/en/entity/create';
import { type GatekeeperEntity } from '@/types';
import HeadingSmall from '@components/ui/heading-small';
import { useMemo } from 'react';

interface CreateEntityScreenProps {
    entity: GatekeeperEntity;
}

export default function CreateEntityScreen<E extends GatekeeperEntity>({ entity }: CreateEntityScreenProps) {
    const { user } = useGatekeeper();
    const language = useMemo(() => createEntityText[entity], [entity]);

    return (
        <GatekeeperLayout>
            <EntityLayout entity={entity}>
                {user.permissions.can_manage && (
                    <div className="space-y-6">
                        <HeadingSmall title={language.title} description={language.description} />
                        <EntityForm<E> formType="create" entity={entity} />
                    </div>
                )}
            </EntityLayout>
        </GatekeeperLayout>
    );
}
