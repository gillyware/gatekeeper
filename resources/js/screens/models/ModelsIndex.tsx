import ModelsSearch from '@/components/models/ModelsSearch';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import ModelsLayout from '@/layouts/models-layout';

export default function ModelsIndex() {
    return (
        <GatekeeperLayout>
            <ModelsLayout>
                <ModelsSearch />
            </ModelsLayout>
        </GatekeeperLayout>
    );
}
