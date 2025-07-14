import EntitiesTable from '@/components/entity/EntitiesTable';
import EntityLayout from '@/layouts/entity-layout';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import { entityIndexText, type EntityIndexText } from '@/lib/lang/en/entity';
import { type GatekeeperEntity } from '@/types';
import HeadingSmall from '@components/ui/heading-small';
import { useMemo } from 'react';

interface EntityIndexScreenProps {
    entity: GatekeeperEntity;
}

export default function EntityIndexScreen<E extends GatekeeperEntity>({ entity }: EntityIndexScreenProps) {
    const language: EntityIndexText = useMemo(() => entityIndexText[entity], [entity]);

    return (
        <GatekeeperLayout>
            <EntityLayout entity={entity}>
                <div className="space-y-6">
                    <HeadingSmall title={language.title} description={language.description} />
                    <EntitiesTable<E> entity={entity} />
                </div>
            </EntityLayout>
        </GatekeeperLayout>
    );
}
