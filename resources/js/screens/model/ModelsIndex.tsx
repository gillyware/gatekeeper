import ModelsSearch from '@/components/model/ModelsSearch';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import ModelLayout from '@/layouts/model-layout';

export default function ModelsIndex() {
    return (
        <GatekeeperLayout>
            <ModelLayout>
                <ModelsSearch />
            </ModelLayout>
        </GatekeeperLayout>
    );
}
