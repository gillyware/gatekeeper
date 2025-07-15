import ModelManagement from '@/components/model/ModelManagement';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import ModelLayout from '@/layouts/model-layout';

export default function ManageModel() {
    return (
        <GatekeeperLayout>
            <ModelLayout>
                <ModelManagement />
            </ModelLayout>
        </GatekeeperLayout>
    );
}
