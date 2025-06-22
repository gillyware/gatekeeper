import { useGatekeeper } from '@/context/GatekeeperContext';
import { useApi } from '@/lib/api';
import { fetchModel } from '@/lib/models';
import { ModelManagementTab } from '@/types';
import { ConfiguredModel, LookupModelRequest } from '@/types/api/model';
import ModelManagementTabs from '@components/models/ModelEntityTabs';
import ModelPermissions from '@components/models/ModelPermissions';
import ModelRoles from '@components/models/ModelRoles';
import ModelSummary from '@components/models/ModelSummary';
import ModelTeams from '@components/models/ModelTeams';
import { Loader } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useParams } from 'react-router';

export default function ModelManagement() {
    const api = useApi();
    const { config } = useGatekeeper();
    const { modelLabel, modelPk } = useParams<{ modelLabel: string; modelPk: string }>() as { modelLabel: string; modelPk: string };

    const [model, setModel] = useState<ConfiguredModel | null>(null);
    const [tab, setTab] = useState<ModelManagementTab>('overview');

    const [loadingModel, setLoadingModel] = useState<boolean>(true);

    const [errorLoadingModel, setErrorLoadingModel] = useState<string | null>(null);

    useEffect(() => {
        const params: LookupModelRequest = { model_label: modelLabel, model_pk: modelPk };
        fetchModel(api, params, setModel, setLoadingModel, setErrorLoadingModel);
    }, [modelLabel, modelPk]);

    if (loadingModel) {
        return (
            <div className="flex h-full w-full items-center justify-center">
                <Loader className="h-6 w-6 animate-spin text-gray-500 dark:text-gray-400" />
            </div>
        );
    }

    if (errorLoadingModel || !model) {
        return <div className="text-red-500">{errorLoadingModel || 'Failed to load model.'}</div>;
    }

    return (
        <div className="flex flex-col gap-8 space-y-6 p-4">
            <ModelManagementTabs tab={tab} changeTab={setTab} />

            {tab === 'overview' && <ModelSummary model={model} />}

            {tab === 'permissions' && <ModelPermissions model={model} />}

            {tab === 'roles' && <ModelRoles model={model} />}

            {tab === 'teams' && <ModelTeams model={model} />}
        </div>
    );
}
