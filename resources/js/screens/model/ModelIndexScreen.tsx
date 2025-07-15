import ModelsTable from '@/components/model/ModelsTable';
import GatekeeperLayout from '@/layouts/gatekeeper-layout';
import ModelLayout from '@/layouts/model-layout';

export default function ModelIndexScreen() {
    return (
        <GatekeeperLayout>
            <ModelLayout>
                <div className="space-y-6">
                    <ModelsTable />
                </div>
            </ModelLayout>
        </GatekeeperLayout>
    );
}
