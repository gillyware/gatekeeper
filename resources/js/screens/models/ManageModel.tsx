import ModelManagement from '@/components/models/ModelManagement';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import ModelsLayout from '@/layouts/models-layout';

export default function ManageModel() {
    return (
        <GatekeeperLayout>
            <ModelsLayout>
                <ModelManagement />
            </ModelsLayout>
        </GatekeeperLayout>
    );
}
